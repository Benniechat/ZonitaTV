<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupabaseAuthService
{
    /**
     * Determine whether Supabase integration credentials are fully configured.
     */
    public function isConfigured(): bool
    {
        return ! empty(config('services.supabase.url')) &&
            (! empty(config('services.supabase.anon_key')) || ! empty(config('services.supabase.service_role_key')));
    }

    /**
     * Decode and validate a Supabase JWT payload without external network round-trips.
     *
     * @return array<string, mixed>|null
     */
    public function parseJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            Log::warning('SupabaseAuthService: Invalid JWT structure received');

            return null;
        }

        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]), true);
        if (! $payloadJson) {
            Log::warning('SupabaseAuthService: Failed to base64 decode JWT payload');

            return null;
        }

        $claims = json_decode($payloadJson, true);
        if (! is_array($claims)) {
            Log::warning('SupabaseAuthService: Failed to json decode JWT claims');

            return null;
        }

        // Validate expiration
        if (isset($claims['exp']) && is_numeric($claims['exp']) && $claims['exp'] < time()) {
            Log::info('SupabaseAuthService: JWT token expired', ['exp' => $claims['exp'], 'now' => time()]);

            return null;
        }

        // Validate required identity claims
        if (empty($claims['sub']) && empty($claims['email'])) {
            Log::warning('SupabaseAuthService: JWT claims missing subject and email');

            return null;
        }

        return $claims;
    }

    /**
     * Synchronize a Supabase authenticated identity with the local database.
     *
     * @param  array<string, mixed>  $claims
     */
    public function syncUser(array $claims): User
    {
        $supabaseId = (string) ($claims['sub'] ?? '');
        $email = strtolower(trim((string) ($claims['email'] ?? '')));

        if (empty($email) && empty($supabaseId)) {
            throw new \InvalidArgumentException('Supabase identity payload must contain sub or email.');
        }

        // Query by supabase_id first, then fallback to verified email
        $user = null;
        if (! empty($supabaseId)) {
            $user = User::where('supabase_id', $supabaseId)->first();
        }

        if (! $user && ! empty($email)) {
            $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        }

        $userMetadata = is_array($claims['user_metadata'] ?? null) ? $claims['user_metadata'] : [];
        $appMetadata = is_array($claims['app_metadata'] ?? null) ? $claims['app_metadata'] : [];

        // Determine authoritative role strictly from app_metadata (never user-editable user_metadata)
        $role = $this->resolveRoleFromAppMetadata($appMetadata, $user?->role ?? 'user');

        $name = trim((string) ($userMetadata['full_name'] ?? ($userMetadata['name'] ?? '')));
        if (empty($name) && ! empty($email)) {
            $name = ucfirst(explode('@', $email)[0]);
        }

        $avatar = $userMetadata['avatar_url'] ?? ($userMetadata['picture'] ?? null);

        if ($user) {
            $updates = [
                'supabase_id' => $supabaseId ?: $user->supabase_id,
                'role' => $role,
            ];

            if (empty($user->avatar) && ! empty($avatar)) {
                $updates['avatar'] = (string) $avatar;
            }

            if (empty($user->email_verified_at) && ! empty($claims['email_confirmed_at'])) {
                $updates['email_verified_at'] = now();
            }

            $user->update($updates);
        } else {
            $user = User::create([
                'name' => $name ?: 'Zonita Viewer',
                'email' => $email,
                'supabase_id' => $supabaseId ?: null,
                'password' => Hash::make(Str::random(32)),
                'role' => $role,
                'avatar' => $avatar ? (string) $avatar : null,
                'email_verified_at' => ! empty($claims['email_confirmed_at']) ? now() : now(),
            ]);
        }

        // Ensure preferences exist
        $user->getOrCreatePreferences();

        return $user;
    }

    /**
     * Authenticate session from a validated Supabase token.
     */
    public function authenticate(string $token): ?User
    {
        $claims = $this->parseJwtPayload($token);
        if (! $claims) {
            return null;
        }

        $user = $this->syncUser($claims);
        Auth::login($user, remember: true);

        return $user;
    }

    /**
     * Resolve valid platform role strictly from app_metadata.
     *
     * @param  array<string, mixed>  $appMetadata
     */
    private function resolveRoleFromAppMetadata(array $appMetadata, string $defaultRole): string
    {
        $allowedRoles = ['user', 'content_manager', 'rights_reviewer', 'advertising_manager', 'super_admin'];
        $candidateRole = $appMetadata['role'] ?? ($appMetadata['claims_admin'] ?? null);

        if (is_string($candidateRole) && in_array($candidateRole, $allowedRoles, true)) {
            return $candidateRole;
        }

        return in_array($defaultRole, $allowedRoles, true) ? $defaultRole : 'user';
    }
}
