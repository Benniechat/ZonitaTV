<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(Request $request): View
    {
        return view('auth.login', [
            'initialEmail' => $request->query('email'),
        ]);
    }

    /**
     * Handle standard email/username & password login.
     */
    public function login(Request $request): RedirectResponse
    {
        // Sanitize login identifier
        $request->merge([
            'email' => trim((string) $request->input('email')),
        ]);

        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Please enter your email address or username.',
            'password.required' => 'Please enter your password.',
        ]);

        $remember = $request->boolean('remember');

        try {
            $input = strtolower(trim($credentials['email']));

            // Case-insensitive lookup by email OR by username/name
            $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$input])
                ->orWhereRaw('LOWER(TRIM(name)) = ?', [$input])
                ->first();

            if (! $user) {
                return back()->withErrors([
                    'email' => 'No account found with this email address or username. Please check your spelling or register a new account.',
                ])->onlyInput('email');
            }

            if (! Hash::check($credentials['password'], $user->password)) {
                return back()->withErrors([
                    'email' => 'The password you entered is incorrect. Please check your password and try again.',
                ])->onlyInput('email');
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            Log::info("User successfully authenticated: ID {$user->id}, Email {$user->email}");

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, '.$user->name.'!');
        } catch (\Throwable $e) {
            Log::error('Authentication login error: '.$e->getMessage(), [
                'email' => $credentials['email'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Authentication service error: '.$e->getMessage(),
            ])->onlyInput('email');
        }
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm(Request $request): View
    {
        return view('auth.register', [
            'initialEmail' => $request->query('email'),
        ]);
    }

    /**
     * Handle user account registration.
     */
    public function register(Request $request): RedirectResponse
    {
        // Sanitize name and email upfront
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Please enter your full name.',
            'name.min' => 'Your name must be at least 2 characters long.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please provide a valid email address (e.g. name@example.com).',
            'password.required' => 'Please choose a password.',
            'password.min' => 'Your password must be at least 6 characters long.',
            'password.confirmed' => 'The password confirmation does not match. Please re-enter both passwords carefully.',
        ]);

        try {
            $email = strtolower(trim($validated['email']));

            // Case-insensitive duplicate check
            if (User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->exists()) {
                return back()->withErrors([
                    'email' => 'An account with this email address already exists. Please sign in instead.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            // Persist new user account
            $user = User::create([
                'name' => trim($validated['name']),
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);

            if (! $user || ! $user->id) {
                throw new \RuntimeException('Failed to save account to database.');
            }

            Log::info("User account created and saved to database: ID {$user->id}, Email {$user->email}");

            // Auto-login registered user and regenerate session
            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', "Welcome to Zonita TV, {$user->name}! Your account has been registered and activated successfully.");
        } catch (\Throwable $e) {
            Log::error('Authentication registration error: '.$e->getMessage(), [
                'email' => $validated['email'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Registration error: '.$e->getMessage(),
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been signed out successfully.');
    }
}
