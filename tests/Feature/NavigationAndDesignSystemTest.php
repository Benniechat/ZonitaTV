<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
