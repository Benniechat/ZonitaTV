<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ComedyChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ComedyDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'youtube.com/oembed*' => Http::response([
                'title' => 'Sample Broadcaster Video',
                'author_name' => 'Official Channel',
                'type' => 'video',
                'provider_name' => 'YouTube',
            ], 200),
            '*' => Http::response([], 200),
        ]);

        Category::firstOrCreate(
            ['slug' => 'entertainment'],
            ['name' => 'Entertainment', 'sort_order' => 6, 'is_nav_visible' => true]
        );
    }

    public function test_sync_comedy_directory_command_populates_all_70_creators_and_10_virtual_channels(): void
    {
        $this->artisan('comedy:sync-directory')->assertExitCode(0)->run();

        // 1. Verify 70 Comedy Creators in directory
        $this->assertEquals(70, ComedyChannel::count());

        // 2. Verify Categorization
        $this->assertGreaterThanOrEqual(20, ComedyChannel::where('channel_category', 'standup_comedy')->count());
        $this->assertGreaterThanOrEqual(30, ComedyChannel::where('channel_category', 'skit_creators')->count());
        $this->assertGreaterThanOrEqual(3, ComedyChannel::where('channel_category', 'animated_comedy')->count());
        $this->assertGreaterThanOrEqual(14, ComedyChannel::where('channel_category', 'production_studios')->count());

        // 3. Verify Specific Creators
        $sabinus = ComedyChannel::where('slug', 'sabinus-mr-funny')->first();
        $this->assertNotNull($sabinus);
        $this->assertEquals(1, $sabinus->lead_priority);
        $this->assertEquals('skit_creators', $sabinus->channel_category);

        $basketmouth = ComedyChannel::where('slug', 'basketmouth')->first();
        $this->assertNotNull($basketmouth);
        $this->assertEquals('standup_comedy', $basketmouth->channel_category);
        $this->assertTrue($basketmouth->has_full_length_episodes);

        $ajebo = ComedyChannel::where('slug', 'house-of-ajebo')->first();
        $this->assertNotNull($ajebo);
        $this->assertEquals('animated_comedy', $ajebo->channel_category);

        // 4. Verify 10 Virtual 24/7 Comedy Broadcast Channels
        $virtualSlugs = [
            'zonita-comedy-central',
            'zonita-standup-comedy',
            'zonita-comedy-classics',
            'zonita-family-laughs',
            'zonita-animated-comedy',
            'zonita-street-comedy',
            'zonita-relationship-comedy',
            'zonita-naija-sitcoms',
            'zonita-church-social-comedy',
            'zonita-african-comedy',
        ];

        foreach ($virtualSlugs as $slug) {
            $ch = Channel::where('slug', $slug)->first();
            $this->assertNotNull($ch, "Virtual channel {$slug} must be deployed");
            $this->assertTrue($ch->is_active);
            $this->assertEquals('embed_player', $ch->playback_mode);
            $this->assertNotNull($ch->primaryStream);
            $this->assertGreaterThan(0, $ch->scheduleEntries()->count());
        }
    }

    public function test_comedy_hub_web_routes_render_successfully(): void
    {
        $this->artisan('comedy:sync-directory')->run();

        // 1. Comedy Hub Index
        $response = $this->get(route('comedy.index'));
        $response->assertStatus(200);
        $response->assertSee('African Comedy');
        $response->assertSee('Zonita Comedy Central');
        $response->assertSee('Sabinus');
        $response->assertSee('Watch Live Now');

        // 2. Stand-Up Category Filter
        $responseStandUp = $this->get(route('comedy.index', ['category' => 'standup_comedy']));
        $responseStandUp->assertStatus(200);
        $responseStandUp->assertSee('Basketmouth');

        // 3. Search Filter
        $responseSearch = $this->get(route('comedy.index', ['q' => 'Taaooma']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Taaooma');

        // 4. Comedy Central Alias Route
        $responseAlias = $this->get('/comedy-central');
        $responseAlias->assertStatus(200);

        // 5. Category Show Route for Comedy
        $responseCategory = $this->get('/category/comedy');
        $responseCategory->assertStatus(200);
        $responseCategory->assertSee('Zonita Comedy Central');
    }
}
