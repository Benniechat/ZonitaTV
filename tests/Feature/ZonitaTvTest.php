<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Services\EpgSyncService;
use App\Services\IptvPlaylistIngestService;
use App\Services\RecommendationService;
use App\Services\RightsEligibilityService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZonitaTvTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_channel_directory_loads_and_filters(): void
    {
        $response = $this->get('/channels');
        $response->assertStatus(200);
        $response->assertSee('The Matchroom Africa');

        $filtered = $this->get('/channels?category=sports');
        $filtered->assertStatus(200);
        $filtered->assertSee('The Matchroom Africa');
    }

    public function test_channel_player_page_resolves_playback_modes(): void
    {
        // Embed Player channel (SportyTV Africa)
        $response = $this->get('/channel/sportytv-africa');
        $response->assertStatus(200);
        $response->assertSee('SportyTV Africa');

        // Embed Player channel (Channels Television)
        $channelsTv = $this->get('/channel/channels-tv');
        $channelsTv->assertStatus(200);
        $channelsTv->assertSee('Channels Television');

        // Zonita Original channel (The Matchroom Africa)
        $matchroom = $this->get('/channel/the-matchroom-africa');
        $matchroom->assertStatus(200);
        $matchroom->assertSee('The Matchroom Africa');
    }

    public function test_rights_eligibility_blocks_rejected_channel(): void
    {
        $channel = Channel::where('slug', 'sportytv-africa')->first();
        $this->assertNotNull($channel);
        $channel->latestRights->update(['status' => 'rejected']);

        $rightsService = app(RightsEligibilityService::class);
        $this->assertFalse($rightsService->canList($channel));

        $playback = $rightsService->resolvePermittedPlayback($channel);
        $this->assertFalse($playback['allowed']);
    }

    public function test_recommendation_scoring_formula(): void
    {
        $recommendationService = app(RecommendationService::class);
        $ranked = $recommendationService->rankChannelsForUser(null, 5);

        $this->assertNotEmpty($ranked);
        $this->assertLessThanOrEqual(5, $ranked->count());
    }

    public function test_newsletter_subscription(): void
    {
        $response = $this->post('/newsletter/subscribe', [
            'email' => 'viewer@zonitatv.com',
            'topics' => ['sports', 'news'],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'viewer@zonitatv.com',
        ]);
    }

    public function test_admin_dashboard_loads(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Compliance Dashboard');
        $response->assertSee('Published Channels');
    }

    public function test_iptv_playlist_parser_creates_channels(): void
    {
        $sampleM3u = <<<'M3U'
#EXTM3U
#EXTINF:-1 tvg-id="TestNews.ng" tvg-logo="https://example.com/logo.png" group-title="News",Test News 24
https://example.com/stream/index.m3u8
#EXTINF:-1 tvg-id="TestSports.ng" tvg-logo="https://example.com/sports.png" group-title="Sports",Test Sports HD
https://example.com/sports/live.m3u8
M3U;

        $ingestService = app(IptvPlaylistIngestService::class);
        $report = $ingestService->parseAndImport($sampleM3u, 'NG', 10);

        $this->assertEquals(2, $report['imported']);
        $this->assertDatabaseHas('channels', [
            'tvg_id' => 'TestNews.ng',
            'name' => 'Test News 24',
        ]);
        $this->assertDatabaseHas('channels', [
            'tvg_id' => 'TestSports.ng',
            'name' => 'Test Sports HD',
        ]);
    }

    public function test_epg_xmltv_and_schedule_synchronization(): void
    {
        $epgService = app(EpgSyncService::class);
        $count = $epgService->generateDynamicSchedulesForAllChannels();

        $this->assertGreaterThan(0, $count);
        $this->assertDatabaseHas('programmes', [
            'is_live' => true,
        ]);

        // Test EPG index page renders live guide
        $response = $this->get('/guide');
        $response->assertStatus(200);
        $response->assertSee('Electronic Programme Guide');
        $response->assertSee('On Air Right Now');
    }

    public function test_admin_ingest_dashboard_loads(): void
    {
        $response = $this->get('/admin/ingest');
        $response->assertStatus(200);
        $response->assertSee('IPTV Playlist Ingest');
        $response->assertSee('Electronic Program Guide (EPG)');
    }
}
