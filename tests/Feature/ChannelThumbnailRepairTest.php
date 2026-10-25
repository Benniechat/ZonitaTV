<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelThumbnailRepairTest extends TestCase
{
    use RefreshDatabase;

    public function test_channel_banner_and_logo_fallbacks_never_return_null(): void
    {
        $category = Category::create([
            'name' => 'Sports',
            'slug' => 'sports',
        ]);

        $channel = Channel::create([
            'name' => 'African Football Network',
            'slug' => 'african-football-network',
            'official_destination_url' => 'https://zonivatv.com/afn',
            'country_code' => 'NGA',
            'language_code' => 'ENG',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
            'logo_path' => null,
            'banner_path' => null,
        ]);

        $channel->categories()->attach($category);

        // Banner and logo accessors must be non-null and valid URLs
        $this->assertNotNull($channel->banner_url);
        $this->assertNotEmpty($channel->banner_url);
        $this->assertStringContainsString('http', $channel->banner_url);

        $this->assertNotNull($channel->logo_url);
        $this->assertNotEmpty($channel->logo_url);
        $this->assertStringContainsString('zonita', strtolower($channel->logo_url));

        // Initials test
        $this->assertEquals('AF', $channel->initials);
    }

    public function test_repair_thumbnails_artisan_command_populates_missing_assets(): void
    {
        $category = Category::create([
            'name' => 'News',
            'slug' => 'news',
        ]);

        $channel = Channel::create([
            'name' => 'Lagos News 24',
            'slug' => 'lagos-news-24',
            'official_destination_url' => 'https://zonivatv.com/ln24',
            'country_code' => 'NGA',
            'language_code' => 'ENG',
            'playback_mode' => 'embed_player',
            'is_active' => true,
            'is_published' => true,
            'logo_path' => null,
            'banner_path' => null,
        ]);

        $channel->categories()->attach($category);

        $this->artisan('channels:repair-thumbnails')
            ->assertExitCode(0);

        $channel->refresh();

        $this->assertNotNull($channel->banner_path);
        $this->assertNotEmpty($channel->banner_path);
        $this->assertNotNull($channel->logo_path);
        $this->assertNotEmpty($channel->logo_path);
    }

    public function test_channel_directory_renders_with_resilient_banner_and_logo(): void
    {
        $category = Category::create([
            'name' => 'Entertainment',
            'slug' => 'entertainment',
        ]);

        $channel = Channel::create([
            'name' => 'Silverbird Television',
            'slug' => 'silverbird-television',
            'official_destination_url' => 'https://zonivatv.com/stv',
            'country_code' => 'NGA',
            'language_code' => 'ENG',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
            'logo_path' => null,
            'banner_path' => null,
        ]);

        $channel->categories()->attach($category);

        $response = $this->get('/channels');
        $response->assertStatus(200);
        $response->assertSee('Silverbird Television');
        $response->assertSee('onerror="this.onerror=null;', false);
    }
}
