<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\ContentRight;
use App\Models\ScheduleEntry;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Video;
use App\Services\RightsEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class EndToEndSystemTestingAndQATest extends TestCase
{
    use RefreshDatabase;

    public function test_e2e_guest_discovery_browsing_and_channel_playback_journey(): void
    {
        $initialObLevel = ob_get_level();

        // 1. Setup channels & categories
        $catNollywood = Category::create([
            'name' => 'Nollywood Cinema',
            'slug' => 'nollywood-cinema',
            'is_nav_visible' => true,
            'sort_order' => 1,
        ]);

        $channel = Channel::create([
            'name' => 'Zonita Nollywood Prime',
            'slug' => 'zonita-nollywood-prime',
            'channel_number' => '101',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_featured' => true,
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channel->categories()->attach($catNollywood->id);

        $video = Video::create([
            'title' => 'Lagos Nights Feature Film',
            'slug' => 'lagos-nights-feature-film',
            'youtube_id' => 'lagos_feat_77',
            'duration_seconds' => 5400,
            'is_active' => true,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Lagos Nights Feature Film',
            'duration_seconds' => 5400,
            'start_time' => now()->subMinutes(20),
            'end_time' => now()->addMinutes(70),
            'status' => 'playing',
        ]);

        // Step A: Guest lands on public homepage
        $resHome = $this->get(route('home'));
        $resHome->assertStatus(200);
        $resHome->assertSee('Zonita Nollywood Prime');

        // Step B: Guest uses predictive global search
        $resSearch = $this->get(route('search', ['q' => 'Nollywood']));
        $resSearch->assertStatus(200);
        $resSearch->assertSee('Zonita Nollywood Prime');

        // Step C: Guest filters by vertical category
        $resCat = $this->get(route('categories.show', $catNollywood->slug));
        $resCat->assertStatus(200);
        $resCat->assertSee('Zonita Nollywood Prime');

        // Step D: Guest enters channel linear player stage
        $resChannel = $this->get(route('channels.show', $channel->slug));
        $resChannel->assertStatus(200);
        $resChannel->assertSee('Zonita Nollywood Prime');
        $resChannel->assertSee('101'); // Dial Number
        $resChannel->assertSee('lagos_feat_77'); // Live video payload

        // Step E: Client player polls live status telemetry API
        $resTelemetry = $this->getJson(route('api.playback.status', $channel->slug));
        $resTelemetry->assertStatus(200);
        $resTelemetry->assertJsonPath('is_live', true);
        $resTelemetry->assertJsonPath('channel.channel_number', '101');

        while (ob_get_level() > $initialObLevel) {
            ob_end_clean();
        }
    }

    public function test_e2e_viewer_authentication_personalisation_and_my_guide_journey(): void
    {
        $catMovies = Category::create(['name' => 'African Movies', 'slug' => 'african-movies']);
        $catSports = Category::create(['name' => 'African Sports', 'slug' => 'african-sports']);

        $channelA = Channel::create([
            'name' => 'Star Nollywood TV',
            'slug' => 'star-nollywood-tv',
            'channel_number' => '401',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channelA->categories()->attach($catMovies->id);

        $channelB = Channel::create([
            'name' => 'Continental Sports 1',
            'slug' => 'continental-sports-1',
            'channel_number' => '402',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channelB->categories()->attach($catSports->id);

        // Step A: User registers & authenticates
        $user = User::factory()->create([
            'name' => 'Babatunde Adeleke',
            'email' => 'babatunde@lagos.ng',
            'role' => 'user',
        ]);

        // Step B: User tunes preferences (favors African Movies)
        UserPreference::create([
            'user_id' => $user->id,
            'country_code' => 'NGA',
            'preferred_categories' => [$catMovies->id],
            'preferred_languages' => ['en'],
            'enable_recommendations' => true,
        ]);

        // Step C: User bookmarks Channel A to favourites
        $resFav = $this->actingAs($user)->post(route('channels.favourite', $channelA->id));
        $resFav->assertStatus(200);
        $this->assertDatabaseHas('favourites', [
            'user_id' => $user->id,
            'channel_id' => $channelA->id,
        ]);

        // Step D: User visits personalized guide (/my-guide)
        $resGuide = $this->actingAs($user)->get(route('guide.personalized'));
        $resGuide->assertStatus(200);
        $resGuide->assertSee("Babatunde Adeleke's Watch Guide");
        $resGuide->assertSee('Star Nollywood TV');
        $resGuide->assertSee('Saved Favourite');

        // Step E: REST Recommendations API returns sorted ranks with explainable tags
        $resRecApi = $this->actingAs($user)->getJson(route('api.recommendations.index'));
        $resRecApi->assertStatus(200);
        $resRecApi->assertJsonPath('success', true);
        $firstItem = $resRecApi->json('data.0');
        $this->assertEquals($channelA->id, $firstItem['id']);
        $this->assertEquals('Saved Favourite', $firstItem['recommendation_tag']);
    }

    public function test_e2e_playback_failure_drill_circuit_breaker_and_alert_dispatch(): void
    {
        $reserveVideo = Video::create([
            'title' => 'Emergency Backup Slate Reel',
            'slug' => 'emergency-backup-slate-reel',
            'youtube_id' => 'reserve_safe_001',
            'duration_seconds' => 1800,
            'is_active' => true,
        ]);

        $channel = Channel::create([
            'name' => 'Resilient News Network',
            'slug' => 'resilient-news-network',
            'channel_number' => '501',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'fallback_video_id' => $reserveVideo->id,
            'is_technically_healthy' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        // Step A: Client player hits error and reports to continuity endpoint
        $resReport = $this->postJson('/api/playback/report-failure', [
            'channel_id' => $channel->id,
            'video_id' => 'dead_stream_broken',
            'error_code' => '150', // Restricted or unavailable video
        ]);

        $resReport->assertStatus(200);
        $resReport->assertJsonPath('status', 'recovered');
        $resReport->assertJsonPath('replacement.video_id', 'reserve_safe_001');

        // Step B: Incident recorded in failure logs
        $this->assertDatabaseHas('playback_failures', [
            'channel_id' => $channel->id,
            'error_code' => '150',
        ]);

        // Step C: Admin triage desk sees the incident
        $admin = User::factory()->create(['role' => 'super_admin']);
        $resAdmin = $this->actingAs($admin)->get(route('admin.dashboard'));
        $resAdmin->assertStatus(200);
    }

    public function test_e2e_territory_rights_statutory_compliance_drill(): void
    {
        $rightsService = app(RightsEligibilityService::class);

        // Channel A: Cleared exclusively for Nigeria (NGA)
        $channelNgaOnly = Channel::create([
            'name' => 'Domestic League TV',
            'slug' => 'domestic-league-tv',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'embed_player',
            'is_active' => true,
            'is_published' => true,
        ]);

        ContentRight::create([
            'channel_id' => $channelNgaOnly->id,
            'rights_holder_name' => 'Nigeria Football Authority',
            'classification' => 'direct_license',
            'effective_from' => now()->subMonths(1),
            'expires_at' => now()->addYear(),
            'permitted_territories' => ['NGA'],
            'allow_directory_listing' => true,
            'allow_web_embedding' => true,
            'status' => 'cleared',
        ]);

        // Channel B: Worldwide statutory original
        $channelGlobal = Channel::create([
            'name' => 'Zonita Global News 24',
            'slug' => 'zonita-global-news-24',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        // Step A: Nigerian IP address viewer (NGA)
        $this->assertTrue($rightsService->canList($channelNgaOnly, 'NGA'));
        $this->assertTrue($rightsService->canWatch($channelNgaOnly, 'NGA'));
        $this->assertTrue($rightsService->canList($channelGlobal, 'NGA'));
        $this->assertTrue($rightsService->canWatch($channelGlobal, 'NGA'));

        // Step B: European / German IP address viewer (DEU)
        // ChannelNgaOnly must be blocked
        $this->assertFalse($rightsService->canList($channelNgaOnly, 'DEU'));
        $this->assertFalse($rightsService->canWatch($channelNgaOnly, 'DEU'));

        // ChannelGlobal remains authorized worldwide
        $this->assertTrue($rightsService->canList($channelGlobal, 'DEU'));
        $this->assertTrue($rightsService->canWatch($channelGlobal, 'DEU'));

        // Step C: Admin compliance audit calculates score
        $admin = User::factory()->create(['role' => 'super_admin']);
        $resAudit = $this->actingAs($admin)->get(route('admin.compliance.index'));
        $resAudit->assertStatus(200);
        $resAudit->assertSee('Domestic League TV');
        $resAudit->assertSee('Nigeria Football Authority');
    }

    public function test_e2e_admin_mcr_operations_and_playout_automation_journey(): void
    {
        $admin = User::factory()->create([
            'email' => 'mcr.director@zonitatv.com',
            'role' => 'super_admin',
        ]);

        // Step A: Admin creates 24/7 Virtual Linear Station
        $videoA = Video::create([
            'title' => 'Prime Morning Skits',
            'slug' => 'prime-morning-skits',
            'youtube_id' => 'skit_morning_01',
            'duration_seconds' => 1800,
            'is_active' => true,
        ]);

        $videoB = Video::create([
            'title' => 'Afternoon Comedy Special',
            'slug' => 'afternoon-comedy-special',
            'youtube_id' => 'comedy_afternoon_02',
            'duration_seconds' => 1800,
            'is_active' => true,
        ]);

        $resCreate = $this->actingAs($admin)->post(route('admin.channels.store'), [
            'name' => 'MCR Comedy 24/7',
            'channel_number' => '808',
            'official_destination_url' => 'https://zonitatv.com/channels/mcr-comedy-24-7',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => '1',
            'is_published' => '1',
            'is_active' => '1',
            'brand_color' => '#FF5500',
        ]);

        $resCreate->assertRedirect();
        $channel = Channel::where('channel_number', '808')->firstOrFail();

        // Step B: Configure playout rotation rules
        ChannelSourceRule::create([
            'channel_id' => $channel->id,
            'rotation_tier' => 'balanced',
            'scheduling_mode' => 'sequential',
            'daypart_restriction' => 'all_day',
            'cooldown_hours' => 2,
            'min_reserve_buffer' => 3,
            'max_consecutive_runs' => 2,
            'is_active' => true,
        ]);

        // Attach videos to channel
        $channel->videos()->attach([$videoA->id, $videoB->id]);

        // Step C: Trigger rolling schedule generation
        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $videoA->id,
            'title' => 'Prime Morning Skits',
            'duration_seconds' => 1800,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(20),
            'status' => 'playing',
        ]);

        // Step D: Execute Playout Tick
        $exitCode = Artisan::call('playout:advance');
        $this->assertEquals(0, $exitCode);

        // Step E: Verify Master Operations Overview
        $resDashboard = $this->actingAs($admin)->get(route('admin.dashboard'));
        $resDashboard->assertStatus(200);
        $resDashboard->assertSee('MCR Comedy 24/7');
        $resDashboard->assertSee('ON-AIR');
        $resDashboard->assertSee('Prime Morning Skits');

        // Step F: Verify Audience Analytics & EPG XMLTV Feed
        $resXmltv = $this->get(route('api.epg.xmltv', ['channel' => $channel->slug]));
        $resXmltv->assertStatus(200);
        $this->assertStringContainsString('MCR Comedy 24/7', $resXmltv->getContent());
    }
}
