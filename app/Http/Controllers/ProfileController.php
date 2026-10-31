<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's account settings and profile page.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $preferences = $user->getOrCreatePreferences();

        return view('profile.show', [
            'user' => $user,
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update the user's profile information (name, email).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'name.required' => 'Your full name is required.',
            'email.required' => 'A valid email address is required.',
            'email.unique' => 'An account with this email address already exists.',
        ]);

        $user->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
        ]);

        return back()->with('success', 'Profile details updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Please provide your current password.',
            'password.required' => 'Please provide a new password.',
            'password.min' => 'New password must be at least 8 characters long.',
            'password.confirmed' => 'New password confirmation does not match.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password you provided is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Update user viewing preferences (theme, autoplay, country, language).
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $user = $request->user();
        $preferences = $user->getOrCreatePreferences();

        $validated = $request->validate([
            'country_code' => ['nullable', 'string', 'max:3'],
            'enable_recommendations' => ['nullable', 'boolean'],
        ]);

        $preferences->update([
            'country_code' => $validated['country_code'] ?? $preferences->country_code,
            'enable_recommendations' => $request->has('enable_recommendations') ? $request->boolean('enable_recommendations') : $preferences->enable_recommendations,
        ]);

        return back()->with('success', 'Viewing preferences saved.');
    }

    /**
     * Deactivate / delete account request.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'confirm_password' => ['required', 'string'],
        ], [
            'confirm_password.required' => 'Please provide your password to confirm account deletion.',
        ]);

        if (! Hash::check($request->input('confirm_password'), $user->password)) {
            return back()->withErrors([
                'confirm_password' => 'The password provided is incorrect.',
            ]);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deleted.');
    }
}
