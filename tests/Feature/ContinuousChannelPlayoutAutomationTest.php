<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\ScheduleEntry;
use App\Models\Video;
use App\Services\Zonita\ContinuousPlayoutService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContinuousChannelPlayoutAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_playout_progression_advances_expired_entries_to_completed_and_logs_broadcast_history(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Playout Test One',
            'slug' => 'zonita-playout-test-one',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-playout-test-one',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'past_prog_01',
            'title' => 'Morning Motivation Broadcast',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'confidence_score' => 100,
        ]);

        $pastEntry = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Morning Motivation Broadcast',
            'format_type' => 'feature',
            'start_time' => now()->subMinutes(35),
            'end_time' => now()->subMinutes(5),
            'duration_seconds' => 1800,
            'play_order' => 1,
            'status' => 'playing',
        ]);

        $service = app(ContinuousPlayoutService::class);
        $result = $service->advanceChannelPlayout($channel, null, autoReplenish: false);

        $this->assertEquals(1, $result['completed_count']);

        $pastEntry->refresh();
        $this->assertEquals('completed', $pastEntry->status);

        $this->assertDatabaseHas('broadcast_history', [
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'status' => 'completed',
        ]);
    }

    public function test_playout_progression_activates_current_window_entry_to_playing(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Playout Test Two',
            'slug' => 'zonita-playout-test-two',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-playout-test-two',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'current_prog_02',
            'title' => 'Midday News & Insight',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'confidence_score' => 100,
        ]);

        $currentEntry = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Midday News & Insight',
            'format_type' => 'feature',
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addMinutes(25),
            'duration_seconds' => 1800,
            'play_order' => 2,
            'status' => 'scheduled',
        ]);

        $service = app(ContinuousPlayoutService::class);
        $result = $service->advanceChannelPlayout($channel, null, autoReplenish: false);

        $this->assertEquals(1, $result['activated_count']);
        $this->assertNotNull($result['active_entry']);
        $this->assertEquals('playing', $result['active_entry']['status']);

        $currentEntry->refresh();
        $this->assertEquals('playing', $currentEntry->status);
    }

    public function test_dead_stream_watchdog_auto_detects_unplayable_video_and_recovers_via_continuity(): void
    {
        $category = Category::create(['name' => 'News', 'slug' => 'news']);

        $channel = Channel::create([
            'name' => 'Zonita Watchdog News',
            'slug' => 'zonita-watchdog-news',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-watchdog-news',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);
        $channel->categories()->attach($category->id);

        $unplayableVideo = Video::create([
            'youtube_id' => 'dead_stream_101',
            'title' => 'Live Press Briefing',
            'duration_seconds' => 1800,
            'category_slug' => 'news',
            'availability_status' => 'embedding_disabled',
            'is_embeddable' => false,
            'confidence_score' => 0,
        ]);

        $reserveVideo = Video::create([
            'youtube_id' => 'reserve_news_202',
            'title' => 'Global Economics Special',
            'duration_seconds' => 1800,
            'category_slug' => 'news',
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'is_evergreen' => true,
            'confidence_score' => 100,
        ]);

        $activeEntry = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $unplayableVideo->id,
            'title' => 'Live Press Briefing',
            'format_type' => 'feature',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(20),
            'duration_seconds' => 1800,
            'play_order' => 5,
            'status' => 'playing',
        ]);

        $service = app(ContinuousPlayoutService::class);
        $result = $service->advanceChannelPlayout($channel, null, autoReplenish: false);

        $this->assertTrue($result['recovered']);

        $activeEntry->refresh();
        $this->assertEquals('failed', $activeEntry->status);

        $this->assertDatabaseHas('schedule_entries', [
            'channel_id' => $channel->id,
            'video_id' => $reserveVideo->id,
            'status' => 'playing',
            'is_fallback' => true,
        ]);
    }

    public function test_playout_gap_watchdog_automatically_inserts_emergency_playout_slate(): void
    {
        $emergencySlate = Video::create([
            'youtube_id' => 'emergency_slate_auto',
            'title' => 'Zonita TV 24/7 Continuity Transmission',
            'duration_seconds' => 600,
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'confidence_score' => 100,
        ]);

        $channel = Channel::create([
            'name' => 'Zonita Gap Watchdog Channel',
            'slug' => 'zonita-gap-watchdog-channel',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-gap-watchdog-channel',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
            'fallback_video_id' => $emergencySlate->id,
        ]);

        // Channel has zero schedule entries currently (complete gap)
        $this->assertEquals(0, ScheduleEntry::where('channel_id', $channel->id)->count());

        $service = app(ContinuousPlayoutService::class);
        $result = $service->advanceChannelPlayout($channel, null, autoReplenish: false);

        $this->assertTrue($result['gap_filled']);
        $this->assertNotNull($result['active_entry']);
        $this->assertEquals('playing', $result['active_entry']['status']);
        $this->assertTrue($result['active_entry']['is_fallback']);

        $this->assertDatabaseHas('schedule_entries', [
            'channel_id' => $channel->id,
            'video_id' => $emergencySlate->id,
            'status' => 'playing',
            'is_fallback' => true,
        ]);
    }

    public function test_playout_replenishment_triggers_when_horizon_below_threshold(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Horizon Replenish TV',
            'slug' => 'zonita-horizon-replenish-tv',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-horizon-replenish-tv',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        ChannelSourceRule::create([
            'channel_id' => $channel->id,
            'search_keywords' => ['Africa', 'Horizon'],
            'min_reserve_count' => 3,
            'repeat_cooldown_hours' => 6,
            'timezone' => 'Africa/Lagos',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $video = Video::create([
                'youtube_id' => "horizon_vid_0{$i}",
                'title' => "Horizon Showcase Episode {$i}",
                'duration_seconds' => 3600,
                'availability_status' => 'approved',
                'is_embeddable' => true,
                'confidence_score' => 100,
            ]);
            $channel->channelVideos()->create(['video_id' => $video->id]);
        }

        // Create an entry that only runs for the next 2 hours (horizon < 48 hours)
        $initialVideo = Video::first();
        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $initialVideo->id,
            'title' => 'Short Horizon Entry',
            'format_type' => 'feature',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addHours(2),
            'duration_seconds' => 7800,
            'play_order' => 1,
            'status' => 'playing',
        ]);

        $service = app(ContinuousPlayoutService::class);
        $result = $service->advanceChannelPlayout($channel, null, autoReplenish: true);

        $this->assertTrue($result['replenished']);

        $maxEndTime = ScheduleEntry::where('channel_id', $channel->id)->max('end_time');
        $this->assertNotNull($maxEndTime);
        $this->assertTrue(Carbon::parse($maxEndTime)->greaterThan(now()->addDays(5)));
    }

    public function test_advance_continuous_playout_artisan_command_execution_and_heartbeat(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Command Station',
            'slug' => 'zonita-command-station',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-command-station',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'command_test_vid',
            'title' => 'Command Test Special',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'confidence_score' => 100,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Command Test Special',
            'format_type' => 'feature',
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addMinutes(25),
            'duration_seconds' => 1800,
            'play_order' => 1,
            'status' => 'scheduled',
        ]);

        // 1. Test heartbeat option
        $this->artisan('playout:advance', ['--heartbeat' => true])
            ->expectsOutputToContain('=== Zonita Playout Health Heartbeat ===')
            ->assertExitCode(0);

        // 2. Test single channel advance
        $this->artisan('playout:advance', ['--channel' => $channel->slug])
            ->assertExitCode(0);

        // 3. Test alias playout:tick
        $this->artisan('playout:tick')
            ->assertExitCode(0);
    }
}
