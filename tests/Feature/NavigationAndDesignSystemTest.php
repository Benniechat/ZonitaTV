<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NavigationAndDesignSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Tier 1 Global Utility Header renders essential elements.
     */
    public function test_global_utility_header_renders_brand_search_and_system_actions(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // Brand logo
        $response->assertSee('images/logo/zonitatvlogo2.png');
        $response->assertSee('Discovery Platform');
        // Global search bar with filter shortcut
        $response->assertSee('role="search"', false);
        $response->assertSee('global-search-input');
        // Notifications and WhatsApp CTA
        $response->assertSee('aria-label="Filter search results by type"', false);
        $response->assertSee('Notifications');
        $response->assertSee('WhatsApp');
    }

    /**
     * Test Tier 2 Primary Navigation Bar renders strictly 4-5 high level sections.
     */
    public function test_primary_navigation_renders_strictly_five_core_sections(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('role="menubar"', false);
        // Primary visible links strictly limited to 5
        $response->assertSee('Home');
        $response->assertSee('Live TV');
        $response->assertSee('Channels');
        $response->assertSee('Movies');
        $response->assertSee('Categories');
    }

    /**
     * Test Categories Mega-Menu organizes taxonomies into 4 structured columns.
     */
    public function test_categories_mega_menu_contains_structured_taxonomies(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // Column headers
        $response->assertSee('By Format');
        $response->assertSee('By Genre');
        $response->assertSee('Spotlight');

        // Vertical links consolidated inside mega-menu
        $response->assertSee('Nollywood Cinema');
        $response->assertSee('Comedy Central');
        $response->assertSee('Sports Broadcasting');
        $response->assertSee('Gospel TV');
        $response->assertSee('Islamic TV');
    }

    /**
     * Test responsive mobile drawer and accessible ARIA attributes.
     */
    public function test_responsive_mobile_drawer_and_accessibility_attributes(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('aria-label="Open mobile navigation menu"', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-label="Mobile Navigation Menu"', false);
        $response->assertSee('aria-modal="true"', false);
    }

    /**
     * Test Tier 1 Header renders regional market pill indicator.
     */
    public function test_header_renders_regional_market_pill(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('🇳🇬');
        $response->assertSee('NGA');
    }

    /**
     * Test authenticated viewer gets Account Settings & Personalised Guide links in drawer.
     */
    public function test_authenticated_mobile_drawer_renders_account_settings_and_guide(): void
    {
        $user = User::create([
            'name' => 'Mobile Viewer',
            'email' => 'mobile@zonitatv.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('profile.show'));
        $response->assertSee('Account Settings');
        $response->assertSee(route('guide.personalized'));
        $response->assertSee('My Personalised Guide');
        // Standard user does NOT see admin dashboard
        $response->assertDontSee('Control Hub (Admin)');
    }

    /**
     * Test staff role receives Control Hub link in mobile drawer.
     */
    public function test_staff_mobile_drawer_renders_control_hub_link(): void
    {
        $admin = User::create([
            'name' => 'Mobile Admin',
            'email' => 'admin.mobile@zonitatv.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.dashboard'));
        $response->assertSee('Control Hub (Admin)');
    }
}
