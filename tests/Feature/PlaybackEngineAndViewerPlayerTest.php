<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelStream;
use App\Models\ContentRight;
use App\Models\ScheduleEntry;
use App\Models\Video;
use App\Services\RightsEligibilityService;
use App\Services\Zonita\PlaybackContinuityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaybackEngineAndViewerPlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_permitted_playback_mode_resolution_for_virtual_and_direct_channels(): void
    {
        $virtualChannel = Channel::create([
            'name' => 'Zonita Sports Pulse',
            'slug' => 'zonita-sports-pulse',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-sports-pulse',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $rightsService = app(RightsEligibilityService::class);
        $resolution = $rightsService->resolvePermittedPlayback($virtualChannel, 'NGA');

        $this->assertTrue($resolution['allowed']);
        $this->assertEquals('zoniva_original', $resolution['mode']);

        $directChannel = Channel::create([
            'name' => 'African News Network',
            'slug' => 'african-news-network',
            'official_destination_url' => 'https://ann.tv',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_virtual' => false,
            'is_active' => true,
            'is_published' => true,
        ]);

        ChannelStream::create([
            'channel_id' => $directChannel->id,
            'stream_url' => 'https://stream.ann.tv/live/master.m3u8',
            'stream_type' => 'hls_m3u8',
            'is_active' => true,
            'is_primary' => true,
        ]);

        ContentRight::create([
            'channel_id' => $directChannel->id,
            'rights_holder_name' => 'African News Network Ltd',
            'permitted_territories' => ['NGA', 'GHA'],
            'allow_direct_hls' => true,
            'allow_directory_listing' => true,
            'status' => 'embedding_approved',
            'effective_from' => now()->subDay(),
            'expires_at' => now()->addYear(),
        ]);

        $directResolution = $rightsService->resolvePermittedPlayback($directChannel, 'NGA');
        $this->assertTrue($directResolution['allowed']);
        $this->assertEquals('direct_stream', $directResolution['mode']);
    }

    public function test_playback_failure_reporting_triggers_circuit_breaker_and_recovery(): void
    {
        $category = Category::create(['name' => 'Sports', 'slug' => 'sports']);

        $channel = Channel::create([
            'name' => 'Zonita Premier Playout',
            'slug' => 'zonita-premier-playout',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-premier-playout',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);
        $channel->categories()->attach($category->id);

        $failingVideo = Video::create([
            'youtube_id' => 'fail_vid_101',
            'title' => 'Match Day Highlights',
            'duration_seconds' => 1800,
            'category_slug' => 'sports',
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'consecutive_failures' => 0,
            'confidence_score' => 100,
        ]);

        $reserveVideo = Video::create([
            'youtube_id' => 'reserve_vid_202',
            'title' => 'Top 10 Champions Goals',
            'duration_seconds' => 900,
            'category_slug' => 'sports',
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'is_evergreen' => true,
            'confidence_score' => 100,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $failingVideo->id,
            'title' => 'Match Day Highlights',
            'format_type' => 'feature',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(20),
            'duration_seconds' => 1800,
            'play_order' => 1,
            'status' => 'playing',
        ]);

        $response = $this->postJson('/api/playback/report-failure', [
            'channel_id' => $channel->id,
            'video_id' => 'fail_vid_101',
            'error_code' => '150',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'recovered',
            'replacement' => [
                'video_id' => 'reserve_vid_202',
                'title' => 'Top 10 Champions Goals',
            ],
            'channel_slug' => $channel->slug,
        ]);

        $failingVideo->refresh();
        $this->assertEquals(1, $failingVideo->consecutive_failures);
        $this->assertEquals('embedding_disabled', $failingVideo->availability_status);
        $this->assertFalse($failingVideo->is_embeddable);

        $this->assertDatabaseHas('playback_failures', [
            'channel_id' => $channel->id,
            'video_id' => $failingVideo->id,
            'error_code' => '150',
            'recovered_with_video_id' => $reserveVideo->id,
        ]);
    }

    public function test_emergency_fallback_video_deployment_when_reserve_empty(): void
    {
        $emergencySlate = Video::create([
            'youtube_id' => 'emergency_slate_999',
            'title' => 'Zonita TV Station Ident & Standby Loop',
            'duration_seconds' => 300,
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'confidence_score' => 100,
        ]);

        $channel = Channel::create([
            'name' => 'Zonita Docu Station',
            'slug' => 'zonita-docu-station',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-docu-station',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
            'fallback_video_id' => $emergencySlate->id,
        ]);

        $failingVideo = Video::create([
            'youtube_id' => 'failing_doc_404',
            'title' => 'Deep Sea Odyssey',
            'duration_seconds' => 2400,
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'confidence_score' => 100,
        ]);

        $activeEntry = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $failingVideo->id,
            'title' => 'Deep Sea Odyssey',
            'format_type' => 'documentary',
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addMinutes(35),
            'duration_seconds' => 2400,
            'play_order' => 10,
            'status' => 'playing',
        ]);

        $service = app(PlaybackContinuityService::class);
        $result = $service->handlePlayerFailure(
            $channel->id,
            $failingVideo->youtube_id,
            '100'
        );

        $this->assertEquals('recovered', $result['status']);
        $this->assertEquals('emergency_slate_999', $result['replacement']['video_id']);

        $activeEntry->refresh();
        $this->assertEquals('failed', $activeEntry->status);

        $this->assertDatabaseHas('schedule_entries', [
            'channel_id' => $channel->id,
            'video_id' => $emergencySlate->id,
            'status' => 'playing',
            'is_fallback' => true,
        ]);
    }

    public function test_live_playback_status_endpoint_returns_realtime_metrics(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Telemetry Channel',
            'slug' => 'zonita-telemetry-channel',
            'channel_number' => 108,
            'brand_color' => '#00FFCC',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-telemetry-channel',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $video1 = Video::create([
            'youtube_id' => 'telemetry_vid_01',
            'title' => 'Live Concert Hour 1',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'is_embeddable' => true,
        ]);

        $video2 = Video::create([
            'youtube_id' => 'telemetry_vid_02',
            'title' => 'Live Concert Hour 2',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'is_embeddable' => true,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video1->id,
            'title' => 'Live Concert Hour 1',
            'format_type' => 'music',
            'start_time' => now()->subSeconds(300),
            'end_time' => now()->addSeconds(1500),
            'duration_seconds' => 1800,
            'play_order' => 1,
            'status' => 'playing',
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video2->id,
            'title' => 'Live Concert Hour 2',
            'format_type' => 'music',
            'start_time' => now()->addSeconds(1500),
            'end_time' => now()->addSeconds(3300),
            'duration_seconds' => 1800,
            'play_order' => 2,
            'status' => 'scheduled',
        ]);

        $response = $this->getJson("/api/playback/status/{$channel->slug}");

        $response->assertStatus(200);
        $response->assertJson([
            'is_live' => true,
            'channel' => [
                'id' => $channel->id,
                'name' => 'Zonita Telemetry Channel',
                'channel_number' => 108,
                'brand_color' => '#00FFCC',
                'is_virtual' => true,
            ],
            'current_entry' => [
                'title' => 'Live Concert Hour 1',
                'video_id' => 'telemetry_vid_01',
                'duration_seconds' => 1800,
            ],
            'next_programme' => [
                'title' => 'Live Concert Hour 2',
                'duration_seconds' => 1800,
            ],
        ]);

        $data = $response->json();
        $this->assertGreaterThanOrEqual(295, $data['current_entry']['elapsed_seconds']);
        $this->assertLessThanOrEqual(1505, $data['current_entry']['remaining_seconds']);
    }

    public function test_player_page_renders_with_station_branding_and_linear_queue(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Afro Beats TV',
            'slug' => 'zonita-afro-beats-tv',
            'channel_number' => 42,
            'brand_color' => '#FF0055',
            'watermark_logo_path' => 'images/watermarks/zonita_afro.png',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-afro-beats-tv',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'afrobeats_summer_mix',
            'title' => 'Afrobeats Summer Megamix',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
            'is_embeddable' => true,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Afrobeats Summer Megamix',
            'format_type' => 'music',
            'start_time' => now()->subMinutes(15),
            'end_time' => now()->addMinutes(45),
            'duration_seconds' => 3600,
            'play_order' => 1,
            'status' => 'playing',
        ]);

        $initialLevel = ob_get_level();
        $response = $this->get(route('channels.show', $channel->slug));
        while (ob_get_level() > $initialLevel) {
            ob_end_flush();
        }

        $response->assertStatus(200);
        $response->assertSee('CH 42');
        $response->assertSee('24/7 REAL-TIME BROADCAST');
        $response->assertSee('Afrobeats Summer Megamix');
        $response->assertSee('images/watermarks/zonita_afro.png');
        $response->assertSee('#FF0055');
    }

    public function test_playback_failure_logging_and_telemetry_persistence(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Telemetry Audit',
            'slug' => 'zonita-telemetry-audit',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-telemetry-audit',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'geo_blocked_vid',
            'title' => 'International Documentary',
            'duration_seconds' => 1200,
            'availability_status' => 'approved',
            'is_embeddable' => true,
        ]);

        $response = $this->withHeaders([
            'CF-IPCountry' => 'GBR',
            'User-Agent' => 'ZonivaTV-TestPlayer/2.0',
        ])->postJson('/api/playback/report-failure', [
            'channel_id' => $channel->id,
            'video_id' => 'geo_blocked_vid',
            'error_code' => '101',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('playback_failures', [
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'error_code' => '101',
            'ip_country' => 'GBR',
            'user_agent' => 'ZonivaTV-TestPlayer/2.0',
        ]);
    }
}
