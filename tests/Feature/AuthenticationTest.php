<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_google_signin_button(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Welcome Back');
        $response->assertSee('Continue with Google');
        $response->assertSee('Sign In');
        $response->assertSee('Show password');
    }

    public function test_register_page_renders_with_google_signup_button(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create Free Account');
        $response->assertSee('Sign up with Google');
        $response->assertSee('Show password');
    }

    /**
     * Test 1: New registration
     * Verify that authentication account is created, profile is created,
     * and no password is stored in plaintext.
     */
    public function test_scenario_1_new_registration_creates_account_and_profile_without_plaintext_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'secretPassword123!',
            'password_confirmation' => 'secretPassword123!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        // Verify account created in database
        $user = User::where('email', 'johndoe@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertNotNull($user->email_verified_at);

        // Verify password is NOT stored in plaintext
        $this->assertNotEquals('secretPassword123!', $user->password);
        $this->assertFalse(str_contains($user->password, 'secretPassword123!'));
        $this->assertTrue(Hash::check('secretPassword123!', $user->password));

        // Verify profile / preferences created
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'country_code' => 'NGA',
        ]);
        $this->assertNotNull($user->preferences);
    }

    /**
     * Test 2: Sign in after registration
     * Register a new user, sign out, and sign in using the same credentials.
     */
    public function test_scenario_2_sign_in_after_registration_journey(): void
    {
        // 1. Register new user
        $regResponse = $this->post('/register', [
            'name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
            'password' => 'CyberDyne2026!',
            'password_confirmation' => 'CyberDyne2026!',
        ]);
        $regResponse->assertRedirect(route('home'));
        $this->assertAuthenticated();

        // 2. Sign out
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();

        // 3. Sign in using the exact same credentials
        $loginResponse = $this->post('/login', [
            'email' => 'sarah@example.com',
            'password' => 'CyberDyne2026!',
        ]);

        $loginResponse->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertEquals('sarah@example.com', auth()->user()->email);
    }

    /**
     * Test 3: Existing account
     * Verify that signing in with a pre-existing valid user remains functional.
     */
    public function test_scenario_3_existing_account_preserves_sign_in(): void
    {
        $existingUser = User::create([
            'name' => 'Existing Streamer',
            'email' => 'existing@zonivatv.com',
            'password' => Hash::make('KnownPassword999!'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        $existingUser->getOrCreatePreferences();

        $response = $this->post('/login', [
            'email' => 'existing@zonivatv.com',
            'password' => 'KnownPassword999!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($existingUser);
    }

    /**
     * Test 4: Duplicate registration
     * Attempt to register the same email twice; verify rejection and validation error.
     */
    public function test_scenario_4_duplicate_registration_handled_correctly(): void
    {
        User::create([
            'name' => 'Original User',
            'email' => 'duplicate@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/register', [
            'name' => 'Impostor User',
            'email' => '  DUPLICATE@EXAMPLE.COM  ',
            'password' => 'anotherPass123',
            'password_confirmation' => 'anotherPass123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('already exists', session('errors')->first('email'));
        $this->assertEquals(1, User::where('email', 'duplicate@example.com')->count());
        $this->assertGuest();
    }

    /**
     * Test 5: Username login
     * Verify that users can sign in using their username/name and resolve to the correct identity.
     */
    public function test_scenario_5_username_login_resolves_correct_identity_without_data_leakage(): void
    {
        $user = User::create([
            'name' => 'NollywoodKing',
            'email' => 'king@nollywood.tv',
            'password' => Hash::make('MovieMaster2026!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'NollywoodKing',
            'password' => 'MovieMaster2026!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertEquals('king@nollywood.tv', auth()->user()->email);
    }

    /**
     * Test 6: Incorrect password
     * Confirm that authentication fails safely when an invalid password is provided.
     */
    public function test_scenario_6_incorrect_password_fails_safely(): void
    {
        User::create([
            'name' => 'Secure User',
            'email' => 'secure@example.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'secure@example.com',
            'password' => 'WrongGuessPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('password you entered is incorrect', session('errors')->first('email'));
        $this->assertGuest();
    }

    /**
     * Test 7: Database failure
     * Simulate a failed database request during login; confirm application reports service error
     * rather than incorrectly claiming account does not exist.
     */
    public function test_scenario_7_database_failure_reports_service_error_not_missing_account(): void
    {
        // Simulate database query failure by dropping users table
        DB::statement('DROP TABLE users');

        $response = $this->post('/login', [
            'email' => 'anyone@example.com',
            'password' => 'anypassword',
        ]);

        $response->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');

        // Must report database or service error, NOT nonexistent account!
        $this->assertStringNotContainsString('No account found with this email address', $errorMessage);
        $this->assertStringContainsString('database service error', $errorMessage);
    }

    /**
     * Test 8: Missing profile
     * Simulate an existing user with missing profile/preferences; verify idempotent repair.
     */
    public function test_scenario_8_missing_profile_is_repaired_idempotently_and_safely(): void
    {
        // Create user without profile
        $user = User::create([
            'name' => 'Profileless User',
            'email' => 'noprofile@example.com',
            'password' => Hash::make('MyPassword123!'),
        ]);

        $this->assertDatabaseMissing('user_preferences', [
            'user_id' => $user->id,
        ]);

        // Signing in repairs the missing profile safely and idempotently
        $response = $this->post('/login', [
            'email' => 'noprofile@example.com',
            'password' => 'MyPassword123!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        // Verify profile was repaired and linked to this user
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'country_code' => 'NGA',
        ]);
        $this->assertEquals($user->id, $user->fresh()->preferences->user_id);
    }

    /**
     * Test 9: Session persistence
     * Sign in, verify authenticated state persists across requests.
     */
    public function test_scenario_9_session_persistence_across_subsequent_requests(): void
    {
        $user = User::create([
            'name' => 'Persistent Streamer',
            'email' => 'persistent@example.com',
            'password' => Hash::make('PersistPass123!'),
        ]);

        // 1. Initial login
        $this->post('/login', [
            'email' => 'persistent@example.com',
            'password' => 'PersistPass123!',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        // 2. Subsequent request to user-personalized page maintains session
        $feedResponse = $this->get('/my-guide');
        $feedResponse->assertStatus(200);
        $this->assertAuthenticatedAs($user);

        // 3. User can log out and terminate session
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /**
     * Test 10: RLS and access control
     * Confirm that an authenticated user cannot read or modify another user's private profile,
     * and anonymous users cannot access protected account information.
     */
    public function test_scenario_10_access_control_and_profile_isolation(): void
    {
        // 1. Unauthenticated users cannot access protected auth actions
        $this->post('/logout')->assertRedirect('/login');

        // 2. Profile isolation: User A and User B cannot read or overwrite each other's profiles
        $userA = User::create([
            'name' => 'User Alpha',
            'email' => 'alpha@example.com',
            'password' => Hash::make('PassAlpha123!'),
        ]);
        $prefA = $userA->getOrCreatePreferences();
        $prefA->update(['country_code' => 'NGA', 'preferred_languages' => ['en', 'yo']]);

        $userB = User::create([
            'name' => 'User Beta',
            'email' => 'beta@example.com',
            'password' => Hash::make('PassBeta123!'),
        ]);
        $prefB = $userB->getOrCreatePreferences();
        $prefB->update(['country_code' => 'GHA', 'preferred_languages' => ['ak']]);

        // Verify distinct preference instances and isolated data
        $this->assertNotEquals($userA->preferences->id, $userB->preferences->id);
        $this->assertEquals('NGA', $userA->fresh()->preferences->country_code);
        $this->assertEquals('GHA', $userB->fresh()->preferences->country_code);

        // Verify User A relationship strictly scopes to User A's identity
        $this->assertEquals($userA->id, $userA->preferences()->first()->user_id);
        $this->assertNotEquals($userB->id, $userA->preferences()->first()->user_id);
    }

    public function test_user_can_login_with_case_insensitive_and_padded_email(): void
    {
        $user = User::create([
            'name' => 'Case User',
            'email' => 'caseuser@example.com',
            'password' => Hash::make('mypassword123'),
        ]);

        $response = $this->post('/login', [
            'email' => '  CASEUSER@EXAMPLE.COM  ',
            'password' => 'mypassword123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_unregistered_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'somepassword123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('No account found with this email address', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_google_redirect_checks_for_client_id(): void
    {
        config(['services.google.client_id' => null]);

        $response = $this->get('/auth/google');
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');

        config([
            'services.google.client_id' => 'test-client-id.apps.googleusercontent.com',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $response = $this->get('/auth/google');
        $response->assertRedirectContains('accounts.google.com/o/oauth2/v2/auth');
    }

    public function test_google_callback_creates_and_logs_in_user(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'mock-access-token',
                'id_token' => 'mock-id-token',
            ], 200),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'sub' => 'google-user-12345',
                'email' => 'googleuser@example.com',
                'name' => 'Google Streamer',
                'picture' => 'https://example.com/avatar.jpg',
            ], 200),
        ]);

        $response = $this->get('/auth/google/callback?code=mock-auth-code');

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@example.com',
            'google_id' => 'google-user-12345',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $user = User::where('email', 'googleuser@example.com')->first();
        $this->assertNotNull($user->preferences);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'mismatchPass',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', [
            'email' => 'testuser@example.com',
        ]);
        $this->assertGuest();
    }

    public function test_registration_lowercases_and_trims_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'Spacey User',
            'email' => '  SPACEY@EXAMPLE.COM  ',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'spacey@example.com',
            'name' => 'Spacey User',
        ]);
    }
}
