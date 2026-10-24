<?php

namespace Tests\Feature;

use App\Models\BroadcastSession;
use App\Models\Channel;
use App\Models\FixtureBroadcast;
use App\Models\SportsBroadcastRight;
use App\Models\SportsCompetition;
use App\Models\SportsFixture;
use App\Models\SportsTeam;
use App\Models\User;
use App\Services\Sports\Adapters\MockBroadcastAdapter;
use App\Services\Sports\AutomatedBroadcastController;
use App\Services\Sports\BroadcastRecoveryService;
use App\Services\Sports\BroadcastVerificationService;
use App\Services\Sports\FixtureDiscoveryService;
use App\Services\Sports\MatchSchedulingService;
use App\Services\Sports\Providers\MockSportsDataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportsBroadcastingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected SportsCompetition $competition;

    protected SportsTeam $arsenal;

    protected SportsTeam $chelsea;

    protected Channel $sportsChannel;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->competition = SportsCompetition::create([
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'code' => 'PL',
            'country' => 'England',
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->arsenal = SportsTeam::create([
            'name' => 'Arsenal FC',
            'slug' => 'arsenal-fc',
            'short_name' => 'Arsenal',
            'tla' => 'ARS',
            'crest_url' => 'https://crests.football-data.org/57.png',
        ]);

        $this->chelsea = SportsTeam::create([
            'name' => 'Chelsea FC',
            'slug' => 'chelsea-fc',
            'short_name' => 'Chelsea',
            'tla' => 'CHE',
            'crest_url' => 'https://crests.football-data.org/61.png',
        ]);

        $this->sportsChannel = Channel::create([
            'name' => 'Zonita Football 24/7',
            'slug' => 'zonita-football-24-7',
            'tvg_id' => 'ZonitaFootball247.zoniva',
            'tagline' => 'Non-Stop European & African Football Action',
            'description' => 'Flagship 24/7 football channel',
            'official_destination_url' => 'https://zonita.tv/channel/zonita-football-24-7',
            'playback_mode' => 'embed_player',
            'is_active' => true,
            'is_published' => true,
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'super_admin',
        ]);
    }

    public function test_public_sports_index_page_loads_successfully(): void
    {
        $fixture = SportsFixture::create([
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'utc_kickoff' => now()->addHours(2),
            'status' => 'scheduled',
            'venue' => 'Emirates Stadium',
        ]);

        $response = $this->get(route('sports.index'));

        $response->assertStatus(200);
        $response->assertSee('Zonita Sports Live Engine');
        $response->assertSee('Arsenal FC');
        $response->assertSee('Chelsea FC');
    }

    public function test_public_sports_live_and_upcoming_views(): void
    {
        $liveFixture = SportsFixture::create([
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'utc_kickoff' => now()->subMinutes(30),
            'status' => 'live',
            'minute' => 30,
            'home_score' => 1,
            'away_score' => 0,
        ]);

        $liveResponse = $this->get(route('sports.live'));
        $liveResponse->assertStatus(200);
        $liveResponse->assertSee('Live Football Matches');
        $liveResponse->assertSee('Arsenal FC');

        $upcomingResponse = $this->get(route('sports.upcoming'));
        $upcomingResponse->assertStatus(200);
        $upcomingResponse->assertSee('Upcoming Football Fixtures');

        $scheduleResponse = $this->get(route('sports.schedule'));
        $scheduleResponse->assertStatus(200);
        $scheduleResponse->assertSee('Sports Programme Guide');
    }

    public function test_public_match_show_view_displays_match_and_sportytv_destination(): void
    {
        $fixture = SportsFixture::create([
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'utc_kickoff' => now()->addHours(3),
            'status' => 'scheduled',
            'venue' => 'Emirates Stadium',
        ]);

        FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'source_url' => 'https://www.sporty.tv/live/arsenal-chelsea',
            'operating_mode' => 'mode_a_link',
            'verification_status' => 'confirmed',
            'territory' => 'NGA',
        ]);

        $response = $this->get(route('sports.show', $fixture->id));

        $response->assertStatus(200);
        $response->assertSee('Arsenal FC');
        $response->assertSee('Chelsea FC');
        $response->assertSee('SportyTV');
        $response->assertSee('https://www.sporty.tv/live/arsenal-chelsea');
    }

    public function test_fixture_discovery_service_syncs_and_deduplicates(): void
    {
        $discovery = new FixtureDiscoveryService(new MockSportsDataProvider);

        $result1 = $discovery->syncFixtures();
        $this->assertGreaterThan(0, $result1['new_fixtures']);

        $countBefore = SportsFixture::count();

        // Re-sync should update instead of creating duplicate records
        $result2 = $discovery->syncFixtures();
        $this->assertEquals(0, $result2['new_fixtures']);
        $this->assertEquals($countBefore, SportsFixture::count());
    }

    public function test_broadcast_verification_enforces_mode_a_by_default_unless_mode_c_authorised(): void
    {
        $fixture = SportsFixture::create([
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'utc_kickoff' => now()->addHours(4),
            'status' => 'scheduled',
        ]);

        $verifier = app(BroadcastVerificationService::class);
        $verifier->verifyUpcomingFixtures();

        $broadcast = FixtureBroadcast::where('fixture_id', $fixture->id)->first();
        $this->assertNotNull($broadcast);
        // By default without rights ledger Mode C, must be Mode A
        $this->assertEquals('mode_a_link', $broadcast->operating_mode);

        // Now grant valid rights ledger for Mode C
        SportsBroadcastRight::create([
            'title' => 'SportyTV Broadcast Agreement',
            'rights_holder' => 'SportyTV',
            'territory' => 'NGA',
            'allow_mode_b' => true,
            'allow_mode_c' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
            'status' => 'approved',
            'document_reference' => 'SPORTY-NGA-2026-001',
        ]);

        $updatedBroadcast = $verifier->reverifyFixture($fixture);
        $this->assertEquals('mode_c_rebroadcast', $updatedBroadcast->operating_mode);
        $this->assertTrue($updatedBroadcast->isAuthorisedForRebroadcasting());
    }

    public function test_match_scheduling_service_adds_buffers_into_linear_channel(): void
    {
        $fixture = SportsFixture::create([
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'utc_kickoff' => now()->addHours(5),
            'status' => 'scheduled',
        ]);

        $scheduler = new MatchSchedulingService;
        $result = $scheduler->syncFixtureSchedule();

        $this->assertEquals(1, $result['scheduled']);

        $entry = $this->sportsChannel->scheduleEntries()->first();
        $this->assertNotNull($entry);
        $this->assertStringContainsString('LIVE: Arsenal vs Chelsea', $entry->title);
        // Duration includes 15 min pre-match, 105 min match, 30 min post-match = 150 mins (9000 seconds)
        $this->assertEquals(9000, $entry->duration_seconds);
    }

    public function test_automated_broadcast_controller_transitions_and_fallback(): void
    {
        $fixture = SportsFixture::create([
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'utc_kickoff' => now()->addMinutes(10),
            'status' => 'scheduled',
        ]);

        $broadcast = FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'operating_mode' => 'mode_a_link',
            'verification_status' => 'confirmed',
            'source_url' => 'https://www.sporty.tv/live/match',
        ]);

        $mockAdapter = new MockBroadcastAdapter;
        $controller = new AutomatedBroadcastController($mockAdapter);

        // 1. Prepare broadcast
        $session = $controller->prepareBroadcast($fixture);
        $this->assertNotNull($session);
        $this->assertEquals('pre_show', $session->current_state);

        // 2. Go live
        $controller->goLive($session);
        $this->assertEquals('on_air', $session->fresh()->current_state);
        $this->assertEquals('live', $fixture->fresh()->status);

        // 3. Halftime
        $controller->enterHalftime($session);
        $this->assertEquals('half_time', $session->fresh()->current_state);

        // 4. Conclude
        $controller->concludeBroadcast($session);
        $this->assertEquals('completed', $session->fresh()->current_state);
        $this->assertEquals('finished', $fixture->fresh()->status);
    }

    public function test_admin_mcr_controls_and_emergency_fallback(): void
    {
        $fixture = SportsFixture::create([
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'utc_kickoff' => now()->addMinutes(5),
            'status' => 'live',
        ]);

        $broadcast = FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'operating_mode' => 'mode_b_embed',
            'verification_status' => 'confirmed',
        ]);

        $session = BroadcastSession::create([
            'fixture_id' => $fixture->id,
            'title' => 'Arsenal FC vs Chelsea FC',
            'operating_mode' => 'mode_b_embed',
            'backend' => 'MockBroadcastAdapter',
            'current_state' => 'on_air',
            'scheduled_start_at' => now(),
            'actual_start_at' => now(),
        ]);

        // 1. Admin Index
        $response = $this->actingAs($this->adminUser)->get(route('admin.sports.index'));
        $response->assertStatus(200);
        $response->assertSee('Sports Master Control Room (MCR)');
        $response->assertSee('Arsenal FC vs Chelsea FC');

        // 2. Emergency Fallback action
        $fallbackResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.sports.emergency-fallback', $session->id));
        $fallbackResponse->assertRedirect(route('admin.sports.index'));
        $this->assertEquals('fallback', $session->fresh()->current_state);

        // 3. Force Mode A Kill Switch
        $forceResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.sports.force-mode-a', $session->id));
        $forceResponse->assertRedirect(route('admin.sports.index'));
        $this->assertEquals('mode_a_link', $session->fresh()->operating_mode);
        $this->assertEquals('mode_a_link', $broadcast->fresh()->operating_mode);

        // 4. Toggle Mode
        $toggleResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.sports.toggle-mode', $fixture->id), ['mode' => 'mode_b_embed']);
        $toggleResponse->assertRedirect(route('admin.sports.index'));
        $this->assertEquals('mode_b_embed', $broadcast->fresh()->operating_mode);
    }

    public function test_recovery_service_automatically_triggers_fallback_after_failures(): void
    {
        $fixture = SportsFixture::create([
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'utc_kickoff' => now(),
            'status' => 'live',
        ]);

        FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'operating_mode' => 'mode_c_rebroadcast',
            'feed_endpoint' => 'http://invalid-probe-domain-999.test/stream.m3u8',
            'verification_status' => 'confirmed',
        ]);

        $session = BroadcastSession::create([
            'fixture_id' => $fixture->id,
            'title' => 'Arsenal FC vs Chelsea FC',
            'operating_mode' => 'mode_c_rebroadcast',
            'backend' => 'MockBroadcastAdapter',
            'current_state' => 'on_air',
            'scheduled_start_at' => now(),
            'actual_start_at' => now(),
        ]);

        // Record 3 failures in session
        for ($i = 0; $i < 3; $i++) {
            $session->recordFailure('stream_drop', 'Probe failed');
        }

        $recovery = app(BroadcastRecoveryService::class);
        $result = $recovery->evaluateAndRecover();

        $this->assertEquals(1, $result['fallback_triggered']);
        $this->assertEquals('fallback', $session->fresh()->current_state);
    }
}
