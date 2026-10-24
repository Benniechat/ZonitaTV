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
            if (Auth::attempt($credentials, $remember)) {
                $request->session()->regenerate();

                return redirect()->intended(route('home'))
                    ->with('success', 'Welcome back, '.Auth::user()->name.'!');
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
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
