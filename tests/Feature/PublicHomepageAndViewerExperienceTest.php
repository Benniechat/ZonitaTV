<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Programme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomepageAndViewerExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully_with_viewer_sections(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Zonita TV - Discover More. Watch Smarter.');
        // Verify Hero & Core Sections
        $response->assertSee('Great Stories');
        $response->assertSee('Start Watching Now');
        $response->assertSee('24/7 Virtual Channels Playout');
        $response->assertSee('Live &amp; Trending Now', false);
    }

    public function test_homepage_renders_active_channels_from_database(): void
    {
        $channel = Channel::create([
            'name' => 'African Premier TV',
            'slug' => 'african-premier-tv',
            'description' => 'Top African sports and live entertainment.',
            'official_destination_url' => 'https://youtube.com/@AfricanPremierTV',
            'is_active' => true,
            'is_published' => true,
            'display_order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('African Premier TV');
    }

    public function test_homepage_renders_live_programmes_when_present(): void
    {
        $channel = Channel::create([
            'name' => 'Metro Cinema TV',
            'slug' => 'metro-cinema-tv',
            'official_destination_url' => 'https://youtube.com/@MetroCinemaTV',
            'is_active' => true,
            'is_published' => true,
        ]);

        $category = Category::create([
            'name' => 'Entertainment',
            'slug' => 'entertainment',
            'is_active' => true,
        ]);

        Programme::create([
            'channel_id' => $channel->id,
            'category_id' => $category->id,
            'title' => 'The African Spotlight Show',
            'slug' => 'the-african-spotlight-show',
            'start_time' => now()->subMinutes(15),
            'end_time' => now()->addMinutes(45),
            'is_live' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('The African Spotlight Show');
        $response->assertSee('Live &amp; Trending Now', false);
    }

    public function test_homepage_has_accessible_quick_category_pills(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('channels.index'));
        $response->assertSee(route('guide.index'));
        $response->assertSee(route('movies.index'));
        $response->assertSee(route('nollywood.index'));
        $response->assertSee(route('comedy.index'));
    }

    public function test_homepage_prioritizes_flagship_channels_in_playout_showcase(): void
    {
        $flagshipChannel = Channel::create([
            'name' => 'Zonita African Cinema',
            'slug' => 'zonita-african-cinema',
            'official_destination_url' => 'https://youtube.com/@ZonitaCinema',
            'is_active' => true,
            'is_published' => true,
            'display_order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Zonita African Cinema');
        $response->assertSee(route('channels.show', 'zonita-african-cinema'));
    }

    public function test_guest_can_freely_browse_homepage_and_access_streams(): void
    {
        $channel = Channel::create([
            'name' => 'Free Guest Channel',
            'slug' => 'free-guest-channel',
            'official_destination_url' => 'https://youtube.com/@FreeGuest',
            'is_active' => true,
            'is_published' => true,
        ]);

        // Unauthenticated guest request
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $this->assertGuest();
        $response->assertSee(route('channels.show', 'free-guest-channel'));
    }
}
