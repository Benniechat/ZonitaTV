<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
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

            // Case-insensitive lookup: match email or name/username
            if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
                $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$input])->first();
            } else {
                $user = User::whereRaw('LOWER(TRIM(name)) = ?', [$input])
                    ->orWhereRaw('LOWER(TRIM(email)) = ?', [$input])
                    ->first();
            }

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

            // Ensure associated profile/preferences exist idempotently
            $user->getOrCreatePreferences();

            Auth::login($user, $remember);
            $request->session()->regenerate();

            Log::info("User successfully authenticated: ID {$user->id}, Email {$user->email}");

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, '.$user->name.'!');
        } catch (QueryException $e) {
            Log::error('Database error during user login lookup', [
                'operation' => 'login_lookup',
                'sqlstate' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'A database service error occurred. Please try again in a moment.',
            ])->onlyInput('email');
        } catch (\Throwable $e) {
            Log::error('Unexpected error during user login', [
                'operation' => 'login_auth',
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'An authentication service error occurred. Please try again.',
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

            // Idempotently create associated user profile / preferences
            $user->getOrCreatePreferences();

            Log::info("User account created and saved to database: ID {$user->id}, Email {$user->email}");

            // Auto-login registered user and regenerate session
            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', "Welcome to Zonita TV, {$user->name}! Your account has been registered and activated successfully.");
        } catch (QueryException $e) {
            Log::error('Database error during user registration', [
                'operation' => 'register_persistence',
                'sqlstate' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            // Detect duplicate key / unique constraint violation
            if (str_contains($e->getMessage(), 'UNIQUE') || str_contains($e->getMessage(), 'unique') || $e->getCode() === '23505') {
                return back()->withErrors([
                    'email' => 'An account with this email address already exists. Please sign in instead.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            return back()->withErrors([
                'email' => 'A database error occurred while creating your account. Please try again in a few moments.',
            ])->withInput($request->except('password', 'password_confirmation'));
        } catch (\Throwable $e) {
            Log::error('Unexpected error during user registration', [
                'operation' => 'register_auth',
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'An unexpected registration error occurred. Please try again.',
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
