<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\NollywoodChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NollywoodDirectoryTest extends TestCase
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

        // Seed Movies & Entertainment categories if missing
        Category::firstOrCreate(
            ['slug' => 'movies'],
            ['name' => 'Movies', 'sort_order' => 5, 'is_nav_visible' => true]
        );
        Category::firstOrCreate(
            ['slug' => 'entertainment'],
            ['name' => 'Entertainment', 'sort_order' => 6, 'is_nav_visible' => true]
        );
    }

    public function test_sync_nollywood_directory_command_populates_all_60_channels_and_linear_playout(): void
    {
        $this->artisan('nollywood:sync-directory')->assertExitCode(0)->run();

        // 1. Verify 60 Nollywood Channels in directory
        $this->assertEquals(60, NollywoodChannel::count());

        // 2. Verify Priority 1 Channels
        $this->assertEquals(20, NollywoodChannel::where('priority', 1)->count());

        // 3. Verify Specific Filmmakers & Channels
        $ruth = NollywoodChannel::where('slug', 'ruthkadiri247')->first();
        $this->assertNotNull($ruth);
        $this->assertEquals('RuthKadiri247', $ruth->name);
        $this->assertEquals(1, $ruth->priority);
        $this->assertEquals('popular_nollywood', $ruth->category);
        $this->assertTrue($ruth->has_full_length_movies);

        $apata = NollywoodChannel::where('slug', 'apatatv')->first();
        $this->assertNotNull($apata);
        $this->assertEquals('yoruba_movies', $apata->category);
        $this->assertEquals('Yoruba', $apata->language);

        $ibaka = NollywoodChannel::where('slug', 'ibakatv-nollywood')->first();
        $this->assertNotNull($ibaka);
        $this->assertEquals('aggregators_libraries', $ibaka->category);

        $faan = NollywoodChannel::where('slug', 'faan-tv')->first();
        $this->assertNotNull($faan);
        $this->assertEquals('studios_producers', $faan->category);

        // 4. Verify Linear Broadcast Playout Channels Deployed
        $ruthBroadcast = Channel::where('slug', 'ruthkadiri247')->first();
        $this->assertNotNull($ruthBroadcast);
        $this->assertTrue($ruthBroadcast->is_active);
        $this->assertEquals('embed_player', $ruthBroadcast->playback_mode);
        $this->assertNotNull($ruthBroadcast->currentStream);

        // 5. Verify rolling schedule entries exist
        $this->assertGreaterThan(0, $ruthBroadcast->scheduleEntries()->count());
    }

    public function test_nollywood_cinema_web_routes_render_successfully(): void
    {
        $this->artisan('nollywood:sync-directory')->run();

        // 1. Nollywood Hub Index
        $response = $this->get(route('nollywood.index'));
        $response->assertStatus(200);
        $response->assertSee('Nollywood Cinema');
        $response->assertSee('RuthKadiri247');
        $response->assertSee('Watch Live');

        // 2. Category Filter
        $responseYoruba = $this->get(route('nollywood.index', ['category' => 'yoruba_movies']));
        $responseYoruba->assertStatus(200);
        $responseYoruba->assertSee('APATATV+');

        // 3. Search Filter
        $responseSearch = $this->get(route('nollywood.index', ['q' => 'Maurice Sam']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Maurice Sam TV');

        // 4. Nollywood Cinema alias route
        $responseAlias = $this->get('/nollywood-cinema');
        $responseAlias->assertStatus(200);
    }
}
