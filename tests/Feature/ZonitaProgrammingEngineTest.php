<?php

namespace Tests\Feature;

use App\Jobs\Zonita\GenerateChannelReports;
use App\Jobs\Zonita\MonitorScheduleHealth;
use App\Jobs\Zonita\ProcessPlaybackFailure;
use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\ContentRight;
use App\Models\Video;
use App\Services\Zonita\ChannelRelevanceService;
use App\Services\Zonita\ChannelReserveService;
use App\Services\Zonita\ContentRotationService;
use App\Services\Zonita\PlaybackContinuityService;
use App\Services\Zonita\ProgrammeScheduleService;
use App\Services\Zonita\VideoAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZonitaProgrammingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Channel $footballChannel;

    protected Category $sportsCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sportsCategory = Category::firstOrCreate(
            ['slug' => 'sports'],
            ['name' => 'Sports', 'description' => 'Live sports and analysis']
        );

        $this->footballChannel = Channel::firstOrCreate(
            ['slug' => 'zonita-football-test'],
            [
                'name' => 'Zonita Football Test',
                'tvg_id' => 'ZonitaFootballTest.zonita',
                'tagline' => 'Football 24/7 Action',
                'description' => 'Test football channel',
                'broadcaster_name' => 'Zonita Television Network',
                'official_destination_url' => 'https://zonita.tv/channel/zonita-football-test',
                'playback_mode' => 'embed_player',
                'curation_type' => 'linear_streamer',
                'fallback_video_id' => '9E7DmuhBt4M',
                'is_active' => true,
                'is_published' => true,
                'is_technically_healthy' => true,
            ]
        );

        $this->footballChannel->categories()->syncWithoutDetaching([$this->sportsCategory->id]);

        ChannelSourceRule::updateOrCreate(
            ['channel_id' => $this->footballChannel->id],
            [
                'search_keywords' => ['Champions League', 'Football', 'Highlights'],
                'excluded_keywords' => ['fifa gameplay', 'betting scam'],
                'approved_publishers' => ['UEFA', 'SuperSport'],
                'min_duration_minutes' => 5,
                'max_duration_minutes' => 60,
                'repeat_cooldown_hours' => 6,
                'min_reserve_count' => 3,
                'allow_evergreen' => true,
            ]
        );
    }

    public function test_non_embeddable_video_is_excluded_from_playback_schedule(): void
    {
        $nonEmbedVideo = Video::create([
            'youtube_id' => 'NONEMBED01',
            'title' => 'UEFA Blocked Video',
            'is_embeddable' => false,
            'availability_status' => 'embedding_disabled',
            'confidence_score' => 0,
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        $this->assertFalse($nonEmbedVideo->isPlayable());
        $this->assertEmpty(Video::eligibleForScheduling()->where('id', $nonEmbedVideo->id)->get());
    }

    public function test_channel_relevance_rejects_excluded_keywords(): void
    {
        $relevanceService = app(ChannelRelevanceService::class);

        $validVideo = Video::create([
            'youtube_id' => 'VALIDFBL01',
            'title' => 'UEFA Champions League Matchday Highlights',
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        $spamVideo = Video::create([
            'youtube_id' => 'SPAMFBL01',
            'title' => 'FIFA Gameplay Pack Opening and Betting Scam',
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        $validScore = $relevanceService->scoreCandidate($validVideo, $this->footballChannel);
        $spamScore = $relevanceService->scoreCandidate($spamVideo, $this->footballChannel);

        $this->assertTrue($validScore['is_relevant']);
        $this->assertFalse($spamScore['is_relevant']);
        $this->assertStringContainsString('excluded keyword', $spamScore['reason']);
    }

    public function test_circuit_breaker_cooldown_triggers_after_repeated_failures(): void
    {
        $availabilityService = app(VideoAvailabilityService::class);

        $video = Video::create([
            'youtube_id' => 'CIRCUIT01',
            'title' => 'Flaky Video Stream',
            'is_embeddable' => true,
            'availability_status' => 'approved',
            'confidence_score' => 100,
            'consecutive_failures' => 0,
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        // 1st failure
        $availabilityService->recordPlaybackFailure($video, 'network_timeout');
        $video->refresh();
        $this->assertSame(1, $video->consecutive_failures);
        $this->assertSame('approved', $video->availability_status);

        // 2nd failure
        $availabilityService->recordPlaybackFailure($video, 'network_timeout');
        $video->refresh();
        $this->assertSame(2, $video->consecutive_failures);

        // 3rd failure: circuit breaker trips!
        $availabilityService->recordPlaybackFailure($video, 'network_timeout');
        $video->refresh();
        $this->assertSame(3, $video->consecutive_failures);
        $this->assertSame('temporarily_unavailable', $video->availability_status);
        $this->assertNotNull($video->cooldown_until);
        $this->assertFalse($video->isPlayable());
    }

    public function test_playback_failure_automatically_recovers_with_reserve_video(): void
    {
        $continuityService = app(PlaybackContinuityService::class);

        // Failed video
        $failedVideo = Video::create([
            'youtube_id' => 'FAILVID01',
            'title' => 'Broken Match Feed',
            'is_embeddable' => true,
            'availability_status' => 'approved',
            'confidence_score' => 100,
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        // Evergreen fallback reserve video
        $fallbackVideo = Video::create([
            'youtube_id' => 'RESERVE01',
            'title' => 'Verified African Football Legends Documentary',
            'is_embeddable' => true,
            'availability_status' => 'approved',
            'confidence_score' => 100,
            'category_slug' => 'sports',
            'is_evergreen' => true,
            'duration_seconds' => 1800,
        ]);

        $recovery = $continuityService->handlePlayerFailure(
            $this->footballChannel->id,
            $failedVideo->youtube_id,
            '101'
        );

        $this->assertSame('recovered', $recovery['status']);
        $this->assertSame('RESERVE01', $recovery['replacement']['video_id']);

        $failedVideo->refresh();
        $this->assertSame('embedding_disabled', $failedVideo->availability_status);
        $this->assertDatabaseHas('playback_failures', [
            'channel_id' => $this->footballChannel->id,
            'video_id' => $failedVideo->id,
            'error_code' => '101',
            'recovered_with_video_id' => $fallbackVideo->id,
        ]);
    }

    public function test_rotation_service_enforces_repeat_cooldown(): void
    {
        $rotationService = app(ContentRotationService::class);

        $videoA = Video::create([
            'youtube_id' => 'VROT01',
            'title' => 'UCL Highlights 2026',
            'is_embeddable' => true,
            'availability_status' => 'approved',
            'confidence_score' => 100,
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        $videoB = Video::create([
            'youtube_id' => 'VROT02',
            'title' => 'Tactics 101',
            'is_embeddable' => true,
            'availability_status' => 'approved',
            'confidence_score' => 100,
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        $candidates = collect([$videoA, $videoB]);
        $scheduledTime = Carbon::now();

        // If Video A was just scheduled within cooldown
        $inMemoryRecent = [$videoA->id];
        $eligible = $rotationService->filterEligibleCandidates($candidates, $this->footballChannel, $scheduledTime, $inMemoryRecent);

        $this->assertFalse($eligible->contains('id', $videoA->id));
        $this->assertTrue($eligible->contains('id', $videoB->id));
    }

    public function test_rolling_schedule_and_epg_generation(): void
    {
        $scheduleService = app(ProgrammeScheduleService::class);

        // Seed 3 verified videos
        for ($i = 1; $i <= 3; $i++) {
            Video::create([
                'youtube_id' => "EPGVID0{$i}",
                'title' => "Football Programme 0{$i}",
                'is_embeddable' => true,
                'availability_status' => 'approved',
                'confidence_score' => 100,
                'category_slug' => 'sports',
                'format_type' => 'documentary',
                'duration_seconds' => 1800,
                'is_evergreen' => true,
            ]);
        }

        $result = $scheduleService->generateRollingSchedule($this->footballChannel, 2);
        $this->assertSame('ok', $result['status']);
        $this->assertGreaterThan(10, $result['entries_created']);

        // Test EPG endpoint
        $epg = $scheduleService->getEpgData($this->footballChannel);
        $this->assertNotNull($epg['current']);
        $this->assertNotEmpty($epg['playlist_queue']);
        $this->assertNotEmpty($epg['upcoming']);
    }

    public function test_api_report_failure_endpoint(): void
    {
        $video = Video::create([
            'youtube_id' => 'APITEST01',
            'title' => 'API Test Video',
            'is_embeddable' => true,
            'availability_status' => 'approved',
            'confidence_score' => 100,
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        $response = $this->postJson('/api/playback/report-failure', [
            'channel_id' => $this->footballChannel->id,
            'video_id' => 'APITEST01',
            'error_code' => '150',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'channel_slug']);
    }

    public function test_pilot_channels_render_successfully(): void
    {
        ContentRight::create([
            'channel_id' => $this->footballChannel->id,
            'contract_reference' => 'ZONITA-TEST-REF',
            'rights_holder_name' => 'Zonita TV Test',
            'status' => 'directory_approved',
            'allow_directory_listing' => true,
            'allow_web_embedding' => true,
            'permitted_territories' => ['GLOBAL'],
            'effective_from' => now()->subDay(),
        ]);

        $response = $this->get('/channel/'.$this->footballChannel->slug);
        $response->assertStatus(200);
        $response->assertSee('Zonita Football Test');
        $response->assertSee('Programme Guide');
    }

    public function test_monitor_schedule_health_detects_low_reserve_and_alerts(): void
    {
        // Channel has min_reserve_count = 3, but no eligible videos in sports
        $job = new MonitorScheduleHealth($this->footballChannel, minFutureHours: 24, minReserveCount: 3);
        $job->handle(app(ChannelReserveService::class));

        $this->assertDatabaseHas('system_alerts', [
            'channel_id' => $this->footballChannel->id,
            'alert_type' => 'low_reserve',
            'level' => 'critical',
        ]);
    }

    public function test_process_playback_failure_creates_stream_degraded_alert_on_frequency(): void
    {
        $video = Video::create([
            'youtube_id' => 'FAILTEST01',
            'title' => 'Failing Video',
            'is_embeddable' => true,
            'availability_status' => 'approved',
            'confidence_score' => 100,
            'category_slug' => 'sports',
            'duration_seconds' => 1200,
        ]);

        // Dispatch 5 failure jobs
        for ($i = 0; $i < 5; $i++) {
            $job = new ProcessPlaybackFailure(
                channelId: $this->footballChannel->id,
                youtubeId: 'FAILTEST01',
                errorCode: '150'
            );
            $job->handle(app(PlaybackContinuityService::class));
        }

        $this->assertDatabaseHas('system_alerts', [
            'channel_id' => $this->footballChannel->id,
            'alert_type' => 'stream_degraded',
            'level' => 'critical',
        ]);
    }

    public function test_generate_channel_reports_aggregates_metrics(): void
    {
        $job = new GenerateChannelReports($this->footballChannel, lookbackHours: 24);
        $reports = $job->handle(app(ChannelReserveService::class));

        $this->assertArrayHasKey($this->footballChannel->slug, $reports);
        $this->assertSame($this->footballChannel->id, $reports[$this->footballChannel->slug]['channel_id']);
        $this->assertArrayHasKey('uptime_percentage', $reports[$this->footballChannel->slug]);
    }

    public function test_admin_broadcasting_management_interface(): void
    {
        $response = $this->get(route('admin.broadcasting.index'));
        $response->assertStatus(200);
        $response->assertSee('24/7 Virtual Broadcasting Network');
        $response->assertSee($this->footballChannel->name);

        $rulesResponse = $this->get(route('admin.broadcasting.rules.edit', $this->footballChannel));
        $rulesResponse->assertStatus(200);
        $rulesResponse->assertSee('Programming Rules: '.$this->footballChannel->name);

        $updateResponse = $this->post(route('admin.broadcasting.rules.update', $this->footballChannel), [
            'search_keywords' => 'Updated Football, Soccer Highlights',
            'excluded_keywords' => 'gaming, esports',
            'approved_publishers' => 'UEFA, FIFA',
            'min_duration_minutes' => 10,
            'max_duration_minutes' => 90,
            'repeat_cooldown_hours' => 12,
            'min_reserve_count' => 6,
            'scheduling_mode' => 'continuous',
            'timezone' => 'Africa/Lagos',
        ]);

        $updateResponse->assertRedirect(route('admin.broadcasting.index'));
        $this->assertDatabaseHas('channel_source_rules', [
            'channel_id' => $this->footballChannel->id,
            'min_duration_minutes' => 10,
            'repeat_cooldown_hours' => 12,
            'scheduling_mode' => 'continuous',
        ]);
    }
}
