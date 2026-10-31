<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\FixtureBroadcast;
use App\Models\SportsBroadcastRight;
use App\Models\SportsCompetition;
use App\Models\SportsFixture;
use App\Models\SportsTeam;
use App\Models\User;
use App\Models\Video;
use App\Services\Sports\AutomatedBroadcastController;
use App\Services\Sports\BroadcastVerificationService;
use App\Services\Sports\FixtureDiscoveryService;
use App\Services\Sports\MatchSchedulingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveEventsAndSportsProgrammingTest extends TestCase
{
    use RefreshDatabase;

    public function test_automated_sports_fixture_discovery_and_normalization(): void
    {
        $discoveryService = app(FixtureDiscoveryService::class);
        $result = $discoveryService->syncFixtures(daysAhead: 7);

        $this->assertGreaterThan(0, $result['discovered'] + $result['updated']);

        $this->assertDatabaseHas('sports_competitions', [
            'code' => 'PL',
        ]);

        $fixture = SportsFixture::with(['competition', 'homeTeam', 'awayTeam'])->first();
        $this->assertNotNull($fixture);
        $this->assertNotNull($fixture->utc_kickoff);
        $this->assertNotNull($fixture->lagos_kickoff);
        $this->assertNotEmpty($fixture->homeTeam?->name);
        $this->assertNotEmpty($fixture->awayTeam?->name);
        $this->assertNotEmpty($fixture->title);
    }

    public function test_broadcast_verification_and_statutory_rights_mode_assignment(): void
    {
        $comp = SportsCompetition::create([
            'name' => 'UEFA Champions League',
            'code' => 'CL',
            'country' => 'Europe',
            'is_active' => true,
        ]);

        $home = SportsTeam::create(['name' => 'Real Madrid', 'short_name' => 'Real Madrid', 'country' => 'Spain']);
        $away = SportsTeam::create(['name' => 'Bayern Munich', 'short_name' => 'Bayern', 'country' => 'Germany']);

        $fixture = SportsFixture::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'provider' => 'mock',
            'provider_id' => 'ucl-semi-001',
            'utc_kickoff' => now()->addHours(3),
            'status' => 'scheduled',
        ]);

        $verifier = app(BroadcastVerificationService::class);

        // Scenario 1: Default coverage without explicit rights record -> Mode A (Official Link)
        $broadcastA = $verifier->attachVerifiedBroadcast($fixture, [
            'broadcaster_name' => 'SportyTV',
            'source_url' => 'https://www.sporty.tv/live/ucl-semi-001',
            'territory' => 'NGA',
        ]);

        $this->assertEquals('mode_a_link', $broadcastA->operating_mode);
        $this->assertEquals('confirmed', $broadcastA->verification_status);
        $this->assertFalse($broadcastA->allow_redistribution);

        // Scenario 2: Active Mode B Rights Record -> Mode B (Embed Player)
        $rightsB = SportsBroadcastRight::create([
            'title' => 'SportyTV Digital Syndication Agreement',
            'rights_holder' => 'SportyTV',
            'territory' => 'NGA',
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addYear(),
            'allow_mode_a' => true,
            'allow_mode_b' => true,
            'allow_mode_c' => false,
            'status' => 'approved',
            'document_reference' => 'SPTV-EMBED-2026',
        ]);

        $broadcastB = $verifier->attachVerifiedBroadcast($fixture, [
            'broadcaster_name' => 'SportyTV',
            'feed_endpoint' => 'https://player.sporty.tv/embed/ucl-semi-001',
            'territory' => 'NGA',
        ]);

        $this->assertEquals('mode_b_embed', $broadcastB->operating_mode);
        $this->assertTrue($broadcastB->allow_embedding);
        $this->assertFalse($broadcastB->allow_redistribution);

        // Scenario 3: Active Mode C Rights Record -> Mode C (Rebroadcast Stream)
        $rightsB->update([
            'allow_mode_c' => true,
        ]);

        $broadcastC = $verifier->attachVerifiedBroadcast($fixture, [
            'broadcaster_name' => 'SportyTV',
            'feed_endpoint' => 'https://stream.sporty.tv/live/ucl-master.m3u8',
            'territory' => 'NGA',
        ]);

        $this->assertEquals('mode_c_rebroadcast', $broadcastC->operating_mode);
        $this->assertTrue($broadcastC->allow_redistribution);
        $this->assertEquals('authorised_for_distribution', $broadcastC->verification_status);
    }

    public function test_automated_broadcast_session_state_machine(): void
    {
        $comp = SportsCompetition::create([
            'name' => 'Premier League',
            'code' => 'PL',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = SportsTeam::create(['name' => 'Liverpool FC', 'short_name' => 'Liverpool', 'country' => 'England']);
        $away = SportsTeam::create(['name' => 'Manchester United FC', 'short_name' => 'Man United', 'country' => 'England']);

        $fixture = SportsFixture::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'provider' => 'mock',
            'provider_id' => 'pl-derby-001',
            'utc_kickoff' => now()->addMinutes(15),
            'status' => 'scheduled',
        ]);

        FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'source_url' => 'https://www.sporty.tv/live/pl-derby-001',
            'operating_mode' => 'mode_a_link',
            'verification_status' => 'confirmed',
            'territory' => 'NGA',
        ]);

        $controller = app(AutomatedBroadcastController::class);

        // Step 1: Prepare Broadcast (Pre-show initiated)
        $session = $controller->prepareBroadcast($fixture);
        $this->assertNotNull($session);
        $this->assertEquals('pre_show', $session->current_state);

        // Step 2: Go Live on Air
        $liveSuccess = $controller->goLive($session);
        $this->assertTrue($liveSuccess);
        $session->refresh();
        $fixture->refresh();
        $this->assertEquals('on_air', $session->current_state);
        $this->assertEquals('live', $fixture->status);

        // Step 3: Halftime
        $halfSuccess = $controller->enterHalftime($session);
        $this->assertTrue($halfSuccess);
        $session->refresh();
        $this->assertEquals('half_time', $session->current_state);

        // Step 4: Conclude Broadcast
        $concludeSuccess = $controller->concludeBroadcast($session);
        $this->assertTrue($concludeSuccess);
        $session->refresh();
        $fixture->refresh();
        $this->assertEquals('completed', $session->current_state);
        $this->assertEquals('finished', $fixture->status);
        $this->assertNotNull($session->ended_at);

        // Verify state transitions audit trail
        $transitions = $session->transitions()->pluck('to_state')->toArray();
        $this->assertContains('preparing', $transitions);
        $this->assertContains('pre_show', $transitions);
        $this->assertContains('on_air', $transitions);
        $this->assertContains('half_time', $transitions);
        $this->assertContains('post_show', $transitions);
        $this->assertContains('completed', $transitions);
    }

    public function test_broadcast_mode_c_rights_gate_and_emergency_fallback(): void
    {
        $comp = SportsCompetition::create(['name' => 'Serie A', 'code' => 'SA', 'country' => 'Italy', 'is_active' => true]);
        $home = SportsTeam::create(['name' => 'Juventus', 'short_name' => 'Juve', 'country' => 'Italy']);
        $away = SportsTeam::create(['name' => 'AC Milan', 'short_name' => 'Milan', 'country' => 'Italy']);

        $fixture = SportsFixture::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'provider' => 'mock',
            'provider_id' => 'sa-classic-001',
            'utc_kickoff' => now()->addMinutes(10),
            'status' => 'scheduled',
        ]);

        // Attempting Mode C without redistribution rights
        $broadcast = FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'operating_mode' => 'mode_c_rebroadcast',
            'verification_status' => 'confirmed',
            'allow_redistribution' => false,
            'territory' => 'NGA',
        ]);

        $controller = app(AutomatedBroadcastController::class);

        // Rights Gate: Downgrades to Mode A automatically
        $session = $controller->prepareBroadcast($fixture);
        $broadcast->refresh();
        $this->assertEquals('mode_a_link', $broadcast->operating_mode);

        // Emergency Fallback Test
        $fallbackSuccess = $controller->triggerEmergencyFallback($session, 'Encoder network stream drop');
        $this->assertTrue($fallbackSuccess);
        $session->refresh();
        $this->assertEquals('fallback', $session->current_state);

        $this->assertDatabaseHas('broadcast_failures', [
            'session_id' => $session->id,
            'failure_type' => 'emergency_manual_fallback',
        ]);

        // Admin Emergency Kill-Switch: Force Mode A
        $admin = User::create([
            'name' => 'Admin Controller',
            'email' => 'mcr.admin@zonitatv.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.sports.force-mode-a', $session));
        $response->assertRedirect(route('admin.sports.index'));

        $session->refresh();
        $this->assertEquals('mode_a_link', $session->operating_mode);
    }

    public function test_public_sports_hub_and_matchday_stage_rendering(): void
    {
        $comp = SportsCompetition::create([
            'name' => 'La Liga',
            'code' => 'PD',
            'country' => 'Spain',
            'is_active' => true,
            'display_order' => 1,
        ]);

        $home = SportsTeam::create(['name' => 'FC Barcelona', 'short_name' => 'Barcelona', 'country' => 'Spain']);
        $away = SportsTeam::create(['name' => 'Atletico Madrid', 'short_name' => 'Atletico', 'country' => 'Spain']);

        $fixture = SportsFixture::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'provider' => 'mock',
            'provider_id' => 'laliga-001',
            'utc_kickoff' => now()->addHours(2),
            'status' => 'scheduled',
            'is_featured' => true,
        ]);

        FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'source_url' => 'https://www.sporty.tv/live/laliga-001',
            'operating_mode' => 'mode_a_link',
            'verification_status' => 'confirmed',
            'territory' => 'NGA',
        ]);

        // 1. Hub
        $hubResponse = $this->get(route('sports.index'));
        $hubResponse->assertStatus(200);
        $hubResponse->assertSee('La Liga');
        $hubResponse->assertSee('FC Barcelona');
        $hubResponse->assertSee('Atletico Madrid');

        // 2. Live Page
        $liveResponse = $this->get(route('sports.live'));
        $liveResponse->assertStatus(200);

        // 3. Upcoming Page
        $upcomingResponse = $this->get(route('sports.upcoming'));
        $upcomingResponse->assertStatus(200);
        $upcomingResponse->assertSee('FC Barcelona');

        // 4. Schedule Page
        $scheduleResponse = $this->get(route('sports.schedule'));
        $scheduleResponse->assertStatus(200);

        // 5. Match Detail & Viewing Stage
        $matchResponse = $this->get(route('sports.show', $fixture));
        $matchResponse->assertStatus(200);
        $matchResponse->assertSee('FC Barcelona');
        $matchResponse->assertSee('Atletico Madrid');
        $matchResponse->assertSee('Mode A: Official Broadcaster Discovery');
    }

    public function test_sports_fixtures_sync_to_linear_channel_schedule(): void
    {
        $video = Video::create([
            'youtube_id' => 'sports_bumper_video',
            'title' => 'Zonita Football 24/7 Playout Bumper',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'is_embeddable' => true,
        ]);

        $sportsChannel = Channel::create([
            'name' => 'Zonita Football 24/7',
            'slug' => 'zonita-football-24-7',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-football-24-7',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'fallback_video_id' => $video->id,
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $comp = SportsCompetition::create(['name' => 'Premier League', 'code' => 'PL', 'country' => 'England', 'is_active' => true]);
        $home = SportsTeam::create(['name' => 'Aston Villa', 'short_name' => 'Villa', 'country' => 'England']);
        $away = SportsTeam::create(['name' => 'Tottenham Hotspur', 'short_name' => 'Spurs', 'country' => 'England']);

        $fixture = SportsFixture::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'provider' => 'mock',
            'provider_id' => 'pl-villa-spurs',
            'utc_kickoff' => now()->addHours(4),
            'status' => 'scheduled',
        ]);

        FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'source_url' => 'https://www.sporty.tv/live/villa-spurs',
            'operating_mode' => 'mode_a_link',
            'verification_status' => 'confirmed',
            'territory' => 'NGA',
        ]);

        $scheduler = app(MatchSchedulingService::class);
        $syncResult = $scheduler->syncFixtureSchedule();

        $this->assertGreaterThanOrEqual(1, $syncResult['scheduled']);

        $this->assertDatabaseHas('schedule_entries', [
            'channel_id' => $sportsChannel->id,
            'title' => 'LIVE: Villa vs Spurs (Premier League)',
        ]);
    }

    public function test_sports_artisan_commands_execution(): void
    {
        $this->artisan('sports:sync-fixtures', ['--days' => 5])
            ->assertExitCode(0);

        $this->artisan('sports:verify-broadcasts')
            ->assertExitCode(0);

        $this->artisan('sports:run-scheduler', ['--once' => true])
            ->assertExitCode(0);
    }
}
