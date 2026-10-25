<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\GospelFilmProduction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GospelFilmDirectoryTest extends TestCase
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
    }

    public function test_sync_gospel_directory_command_populates_all_60_ministries_and_schedules_channel(): void
    {
        $this->artisan('gospel:sync-directory')->assertExitCode(0)->run();

        // Check Directory Size
        $this->assertEquals(60, GospelFilmProduction::count());

        // Check Priority 1 Core Producers
        $this->assertGreaterThanOrEqual(7, GospelFilmProduction::where('lead_priority', 1)->count());

        // Check Specific Vetted Ministries
        $parables = GospelFilmProduction::where('name', 'like', '%Parables Films%')->first();
        $this->assertNotNull($parables);
        $this->assertEquals(1, $parables->lead_priority);
        $this->assertTrue($parables->has_full_length_movies);
        $this->assertTrue($parables->has_english_subtitles);

        $evom = GospelFilmProduction::where('name', 'like', '%EVOM%')->first();
        $this->assertNotNull($evom);
        $this->assertEquals('Ilorin, Kwara State', $evom->headquarters_city);

        $winlos = GospelFilmProduction::where('name', 'like', '%Winlos%')->first();
        $this->assertNotNull($winlos);
        $this->assertEquals('English', $winlos->primary_language);

        // Check Linear Playout Network Channel
        $agfn = Channel::where('slug', 'african-gospel-cinema-tv')->first();
        $this->assertNotNull($agfn);
        $this->assertEquals('embed_player', $agfn->playback_mode);
        $this->assertTrue($agfn->is_active);
        $this->assertGreaterThan(0, $agfn->scheduleEntries()->count());
        $this->assertGreaterThan(0, $agfn->channelVideos()->count());
    }

    public function test_gospel_cinema_web_routes_render_successfully(): void
    {
        $this->artisan('gospel:sync-directory')->run();

        // 1. Gospel Directory Index
        $response = $this->get(route('gospel.index'));
        $response->assertStatus(200);
        $response->assertSee('Gospel Film Productions');
        $response->assertSee('African Gospel Film Network');
        $response->assertSee('Parables Films');

        // 2. Filter by Priority 1
        $responsePriority = $this->get(route('gospel.index', ['priority' => 1]));
        $responsePriority->assertStatus(200);
        $responsePriority->assertSee('EVOM Channel TV');
        $responsePriority->assertSee('The Winlos');
    }

    public function test_african_gospel_cinema_channel_renders_with_realtime_playout(): void
    {
        $this->artisan('gospel:sync-directory')->run();

        $response = $this->get(route('channels.show', 'african-gospel-cinema-tv'));
        $response->assertStatus(200);
        $response->assertSee('African Gospel Film Network');
        $response->assertSee('24/7 REAL-TIME BROADCAST');
        $response->assertSee("'controls': 0", false);
    }
}
