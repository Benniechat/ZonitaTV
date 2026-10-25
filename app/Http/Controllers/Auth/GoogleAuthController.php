<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google's OAuth 2.0 server.
     */
    public function redirect(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');

        if (empty($clientId)) {
            return redirect()->route('login')
                ->with('error', 'Google Sign-In is not currently configured. Please use your email to sign in or register below.');
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away("https://accounts.google.com/o/oauth2/v2/auth?{$query}");
    }

    /**
     * Handle the callback from Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            Log::warning('Google OAuth Error: '.$request->input('error'));

            return redirect()->route('login')
                ->with('error', 'Google authentication was cancelled or encountered an error.');
        }

        $code = $request->input('code');

        if (! $code) {
            return redirect()->route('login')
                ->with('error', 'No authorization code received from Google.');
        }

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        try {
            // Exchange authorization code for access token
            $tokenResponse = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if (! $tokenResponse->successful()) {
                Log::error('Failed to exchange Google OAuth code: '.$tokenResponse->body());

                return redirect()->route('login')
                    ->with('error', 'Could not authenticate with Google. Please try again.');
            }

            $accessToken = $tokenResponse->json('access_token');

            // Fetch user profile from Google UserInfo endpoint
            $userResponse = Http::withToken($accessToken)->timeout(10)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (! $userResponse->successful()) {
                Log::error('Failed to fetch Google userinfo: '.$userResponse->body());

                return redirect()->route('login')
                    ->with('error', 'Failed to retrieve profile details from Google.');
            }

            $googleData = $userResponse->json();
            $googleId = $googleData['sub'] ?? null;
            $email = $googleData['email'] ?? null;
            $name = $googleData['name'] ?? ($googleData['given_name'] ?? 'Google User');
            $avatar = $googleData['picture'] ?? null;

            if (! $email) {
                return redirect()->route('login')
                    ->with('error', 'No verified email address found on this Google account.');
            }

            // Find existing user by google_id or email
            $user = User::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                // Update profile with Google attributes
                $user->update([
                    'google_id' => $googleId,
                    'avatar' => $avatar ?? $user->avatar,
                    'name' => $user->name ?: $name,
                ]);
            } else {
                // Create new user account with secure random placeholder password
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $avatar,
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', "Signed in as {$user->name} via Google!");

        } catch (\Throwable $e) {
            Log::error('Exception during Google authentication: '.$e->getMessage());

            return redirect()->route('login')
                ->with('error', 'An error occurred during Google sign in.');
        }
    }
}
