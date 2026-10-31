<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\Programme;
use App\Models\ScheduleEntry;
use App\Models\Video;
use App\Services\Zonita\ProgrammeScheduleService;
use App\Services\Zonita\XmltvExportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerAndEpgManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_7_to_30_day_rolling_epg_generation_and_horizon_bounds(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Nollywood Classics',
            'slug' => 'zonita-nollywood-classics',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-nollywood-classics',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        ChannelSourceRule::create([
            'channel_id' => $channel->id,
            'search_keywords' => ['Nollywood', 'Classics'],
            'min_reserve_count' => 3,
            'repeat_cooldown_hours' => 6,
            'timezone' => 'Africa/Lagos',
        ]);

        // Create 4 approved videos for the candidate pool
        for ($i = 1; $i <= 4; $i++) {
            $video = Video::create([
                'youtube_id' => "noll_classics_0{$i}",
                'title' => "Nollywood Epic Chapter {$i}",
                'duration_seconds' => 3600,
                'availability_status' => 'approved',
                'is_embeddable' => true,
                'category_slug' => 'movies',
            ]);
            $channel->channelVideos()->create(['video_id' => $video->id]);
        }

        $service = app(ProgrammeScheduleService::class);

        // 1. Generate 14-day rolling schedule
        $result = $service->generateRollingSchedule($channel, 14);

        $this->assertEquals('ok', $result['status']);
        $this->assertGreaterThan(50, $result['entries_created']);

        $maxEndTime = ScheduleEntry::where('channel_id', $channel->id)->max('end_time');
        $this->assertNotNull($maxEndTime);
        $this->assertTrue(Carbon::parse($maxEndTime)->greaterThan(now()->addDays(13)));

        // 2. Assert Programme records synchronized for public guide
        $this->assertGreaterThan(0, Programme::where('channel_id', $channel->id)->count());
    }

    public function test_schedule_conflict_resolver_realigns_overlaps_and_fills_gaps(): void
    {
        $channel = Channel::create([
            'name' => 'Conflict Test Station',
            'slug' => 'conflict-test-station',
            'official_destination_url' => 'https://zonitatv.com/channel/conflict-test-station',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
        ]);

        $video1 = Video::create([
            'youtube_id' => 'cfl_vid_01',
            'title' => 'First Programme Segment',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
        ]);
        $video2 = Video::create([
            'youtube_id' => 'cfl_vid_02',
            'title' => 'Overlapping Second Segment',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
        ]);
        $video3 = Video::create([
            'youtube_id' => 'cfl_vid_03',
            'title' => 'Gapped Third Segment',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
        ]);

        $baseTime = now('UTC')->startOfHour();

        // Entry 1: 10:00 to 11:00
        $e1 = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video1->id,
            'title' => 'First Programme Segment',
            'start_time' => $baseTime,
            'end_time' => $baseTime->copy()->addHour(),
            'duration_seconds' => 3600,
            'play_order' => 1,
            'status' => 'scheduled',
        ]);

        // Entry 2: Overlapping! Starts at 10:30 (before e1 finishes at 11:00)
        $e2 = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video2->id,
            'title' => 'Overlapping Second Segment',
            'start_time' => $baseTime->copy()->addMinutes(30),
            'end_time' => $baseTime->copy()->addMinutes(90),
            'duration_seconds' => 3600,
            'play_order' => 2,
            'status' => 'scheduled',
        ]);

        // Entry 3: Gapped! Starts at 13:00 (leaving a 1-hour gap from 12:00 to 13:00)
        $e3 = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video3->id,
            'title' => 'Gapped Third Segment',
            'start_time' => $baseTime->copy()->addHours(3),
            'end_time' => $baseTime->copy()->addHours(4),
            'duration_seconds' => 3600,
            'play_order' => 3,
            'status' => 'scheduled',
        ]);

        $service = app(ProgrammeScheduleService::class);
        $resolution = $service->resolveScheduleConflicts($channel);

        $this->assertGreaterThanOrEqual(1, $resolution['overlaps_resolved']);
        $this->assertGreaterThanOrEqual(1, $resolution['gaps_filled']);

        // Assert e2 start_time was pushed to e1 end_time
        $e2->refresh();
        $this->assertEquals($e1->end_time->toDateTimeString(), $e2->start_time->toDateTimeString());

        // Assert gap filler was created
        $fallback = ScheduleEntry::where('channel_id', $channel->id)
            ->where('is_fallback', true)
            ->first();

        $this->assertNotNull($fallback);
        $this->assertEquals('interstitial', $fallback->format_type);
        $this->assertEquals($e2->end_time->toDateTimeString(), $fallback->start_time->toDateTimeString());
    }

    public function test_artisan_command_epg_generate_rolling(): void
    {
        $channel = Channel::create([
            'name' => 'Artisan Playout Network',
            'slug' => 'artisan-playout-network',
            'official_destination_url' => 'https://zonitatv.com/channel/artisan-playout-network',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'art_epg_01',
            'title' => 'Artisan Programme Stream',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'is_embeddable' => true,
        ]);
        $channel->channelVideos()->create(['video_id' => $video->id]);

        $this->artisan('epg:generate-rolling', [
            '--channel' => 'artisan-playout-network',
            '--days' => 7,
            '--resolve-conflicts' => true,
        ])->assertExitCode(0);

        $this->assertGreaterThan(0, ScheduleEntry::where('channel_id', $channel->id)->count());
    }

    public function test_xmltv_export_generates_valid_rfc_compliant_xml(): void
    {
        $channel = Channel::create([
            'name' => 'Zonita Premier Sports',
            'slug' => 'zonita-premier-sports',
            'channel_number' => '102',
            'tvg_id' => 'ZonitaPremierSports.zonita',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-premier-sports',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        $now = now('UTC');
        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'title' => 'African Nations Cup Special Report',
            'description' => 'In-depth tactical recap and expert interviews.',
            'format_type' => 'documentary',
            'start_time' => $now,
            'end_time' => $now->copy()->addHour(),
            'duration_seconds' => 3600,
            'play_order' => 1,
            'status' => 'playing',
        ]);

        $service = app(XmltvExportService::class);
        $xmlOutput = $service->generateXml();

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xmlOutput);
        $this->assertStringContainsString('<tv generator-info-name="Zonita TV XMLTV Engine"', $xmlOutput);
        $this->assertStringContainsString('channel id="ZonitaPremierSports.zonita"', $xmlOutput);
        $this->assertStringContainsString('<display-name>Zonita Premier Sports</display-name>', $xmlOutput);
        $this->assertStringContainsString('<display-name>CH 102</display-name>', $xmlOutput);
        $this->assertStringContainsString('African Nations Cup Special Report', $xmlOutput);
        $this->assertMatchesRegularExpression('/\d{14} \+0000/', $xmlOutput);
    }

    public function test_xmltv_http_endpoints_and_download(): void
    {
        $channel = Channel::create([
            'name' => 'Feed Channel One',
            'slug' => 'feed-channel-one',
            'official_destination_url' => 'https://zonitatv.com/channel/feed-channel-one',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
        ]);

        // 1. Direct API XMLTV stream
        $apiResp = $this->get(route('api.epg.xmltv'));
        $apiResp->assertOk();
        $this->assertStringContainsString('application/xml', $apiResp->headers->get('Content-Type'));
        $this->assertStringContainsString('<tv generator-info-name=', $apiResp->getContent());

        // 2. Download endpoint with attachment disposition
        $exportResp = $this->get(route('guide.xmltv.export'));
        $exportResp->assertOk();
        $this->assertStringContainsString('attachment;', $exportResp->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xml', $exportResp->headers->get('Content-Disposition'));
    }

    public function test_interactive_guide_page_renders_with_multi_timezone_and_dayparts(): void
    {
        $channel = Channel::create([
            'name' => 'West Africa News Live',
            'slug' => 'west-africa-news-live',
            'channel_number' => '105',
            'official_destination_url' => 'https://zonitatv.com/channel/west-africa-news-live',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
        ]);

        $morningNyUtc = Carbon::now('America/New_York')->setTime(9, 0)->utc();

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'title' => 'Morning Prime Broadcast',
            'start_time' => $morningNyUtc,
            'end_time' => $morningNyUtc->copy()->addHour(),
            'duration_seconds' => 3600,
            'play_order' => 1,
            'status' => 'scheduled',
        ]);

        // Query with timezone and daypart filters
        $response = $this->get(route('guide.index', [
            'tz' => 'America/New_York',
            'daypart' => 'morning',
        ]));

        $response->assertOk();
        $response->assertSee('Interactive Programme Guide');
        $response->assertSee('West Africa News Live');
        $response->assertSee('Morning Prime Broadcast');
        $response->assertSee('America/New_York');
        $response->assertSee('XMLTV Feed (.xml)');
    }
}
