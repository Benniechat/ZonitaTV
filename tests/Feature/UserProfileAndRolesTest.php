<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('profile.show'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::create([
            'name' => 'Profile User',
            'email' => 'profile@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Profile Information');
        $response->assertSee('Profile User');
        $response->assertSee('profile@example.com');
        $response->assertSee('Role: User');
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profile details updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::create([
            'name' => 'Password User',
            'email' => 'pw@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Password updated successfully.');

        $this->assertTrue(Hash::check('newpassword456', $user->fresh()->password));
    }

    public function test_password_change_fails_with_incorrect_current_password(): void
    {
        $user = User::create([
            'name' => 'Password User',
            'email' => 'pw2@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('oldpassword123', $user->fresh()->password));
    }

    public function test_user_can_update_viewing_preferences(): void
    {
        $user = User::create([
            'name' => 'Pref User',
            'email' => 'pref@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->patch(route('profile.preferences'), [
            'country_code' => 'GHA',
            'enable_recommendations' => '0',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Viewing preferences saved.');

        $prefs = $user->getOrCreatePreferences();
        $this->assertEquals('GHA', $prefs->country_code);
        $this->assertFalse((bool) $prefs->enable_recommendations);
    }

    public function test_user_can_delete_own_account_with_password_confirmation(): void
    {
        $user = User::create([
            'name' => 'Delete Me',
            'email' => 'deleteme@example.com',
            'password' => Hash::make('mypassword123'),
        ]);

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'confirm_password' => 'mypassword123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_role_middleware_restricts_unauthorized_roles(): void
    {
        $standardUser = User::create([
            'name' => 'Regular Viewer',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($standardUser)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_superadmin_role_can_access_admin_dashboard(): void
    {
        $adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@zonitatv.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }
}
