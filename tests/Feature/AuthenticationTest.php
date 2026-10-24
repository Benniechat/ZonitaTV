<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_user_can_register_with_email_and_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
            'name' => 'John Doe',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'janesmith@example.com',
            'password' => Hash::make('mypassword123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'janesmith@example.com',
            'password' => 'mypassword123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_case_insensitive_and_padded_email(): void
    {
        $user = User::create([
            'name' => 'Case User',
            'email' => 'caseuser@example.com',
            'password' => 'mypassword123',
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

    public function test_login_fails_with_invalid_password(): void
    {
        User::create([
            'name' => 'Jane Smith',
            'email' => 'janesmith@example.com',
            'password' => Hash::make('mypassword123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'janesmith@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_google_redirect_checks_for_client_id(): void
    {
        // When client_id is not set
        config(['services.google.client_id' => null]);

        $response = $this->get('/auth/google');
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');

        // When client_id is set
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
    }

    public function test_public_header_does_not_display_admin_pill_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('Admin Portal Pill');
        $response->assertSee('Sign In');
        $response->assertSee('Register');
    }
}
