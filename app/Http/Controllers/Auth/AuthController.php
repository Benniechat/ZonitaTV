<?php

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
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle standard email & password login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        try {
            $email = strtolower(trim($credentials['email']));

            // Case-insensitive & trimmed lookup across PostgreSQL, MySQL, and SQLite
            $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

            if (! $user) {
                return back()->withErrors([
                    'email' => 'No account found with this email address. Please check your spelling or register a new account.',
                ])->onlyInput('email');
            }

            if (! Hash::check($credentials['password'], $user->password)) {
                return back()->withErrors([
                    'email' => 'The password you entered is incorrect. Please check your password and try again.',
                    'password' => 'The password you entered is incorrect.',
                ])->onlyInput('email');
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, '.$user->name.'!');
        } catch (\Throwable $e) {
            Log::error('Authentication login error: '.$e->getMessage(), [
                'email' => $credentials['email'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Database service error: '.$e->getMessage(),
            ])->onlyInput('email');
        }
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle user account registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $email = strtolower(trim($validated['email']));

            // Case-insensitive duplicate check
            if (User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->exists()) {
                return back()->withErrors([
                    'email' => 'An account with this email address already exists. Please sign in instead.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            $user = User::create([
                'name' => trim($validated['name']),
                'email' => $email,
                'password' => $validated['password'],
                'role' => 'user',
            ]);

            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->route('home')
                ->with('success', 'Your Zonita TV account has been created successfully!');
        } catch (\Throwable $e) {
            Log::error('Authentication registration error: '.$e->getMessage(), [
                'email' => $validated['email'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Database service error: '.$e->getMessage(),
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
            ->with('success', 'You have been signed out.');
    }
}
