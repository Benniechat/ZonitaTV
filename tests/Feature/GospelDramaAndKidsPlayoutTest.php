<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Services\Zonita\ProgrammeScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GospelDramaAndKidsPlayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_deploy_playout_networks_command_provisions_gospel_drama_and_kids_channels(): void
    {
        $exitCode = $this->artisan('channels:deploy-networks');
        $exitCode->assertExitCode(0);

        // Verify Mount Zion Gospel Drama TV
        $mountZion = Channel::where('slug', 'mount-zion-gospel-drama-tv')->first();
        $this->assertNotNull($mountZion);
        $this->assertEquals('embed_player', $mountZion->playback_mode);
        $this->assertTrue($mountZion->is_active);
        $this->assertGreaterThan(0, $mountZion->scheduleEntries()->count());
        $this->assertGreaterThan(0, $mountZion->channelVideos()->count());

        // Verify Prem TV Gospel Movies
        $premTv = Channel::where('slug', 'prem-tv-gospel-movies')->first();
        $this->assertNotNull($premTv);
        $this->assertGreaterThan(0, $premTv->scheduleEntries()->count());

        // Verify CoComelon & Kids Fun TV
        $cocomelon = Channel::where('slug', 'cocomelon-kids-tv')->first();
        $this->assertNotNull($cocomelon);
        $this->assertGreaterThan(0, $cocomelon->scheduleEntries()->count());

        // Verify Tales of Africa Folktales
        $africanTales = Channel::where('slug', 'african-folktales-tv')->first();
        $this->assertNotNull($africanTales);
        $this->assertGreaterThan(0, $africanTales->scheduleEntries()->count());

        // Verify Bible Stories & Christian Cartoons
        $bibleStories = Channel::where('slug', 'christian-cartoons-bible-tv')->first();
        $this->assertNotNull($bibleStories);
        $this->assertGreaterThan(0, $bibleStories->scheduleEntries()->count());
    }

    public function test_gospel_drama_channel_view_renders_with_realtime_playout(): void
    {
        $this->artisan('channels:deploy-networks');

        $response = $this->get(route('channels.show', 'mount-zion-gospel-drama-tv'));

        $response->assertStatus(200);
        $response->assertSee('Mount Zion Gospel Drama TV');
        $response->assertSee('Real-Time Broadcast');
        $response->assertSee('controls=0');
    }

    public function test_kids_folktales_channel_view_renders_with_realtime_playout(): void
    {
        $this->artisan('channels:deploy-networks');

        $response = $this->get(route('channels.show', 'african-folktales-tv'));

        $response->assertStatus(200);
        $response->assertSee('Tales of Africa');
        $response->assertSee('Real-Time Broadcast');
        $response->assertSee('controls=0');
    }

    public function test_epg_service_auto_replenishes_schedule_when_entries_are_low(): void
    {
        $this->artisan('channels:deploy-networks');

        $channel = Channel::where('slug', 'cocomelon-kids-tv')->firstOrFail();
        $initialCount = $channel->scheduleEntries()->count();
        $this->assertGreaterThan(10, $initialCount);

        // Delete all future entries except 2
        $keepIds = $channel->scheduleEntries()
            ->where('end_time', '>', now())
            ->limit(2)
            ->pluck('id');

        ScheduleEntry::where('channel_id', $channel->id)
            ->whereNotIn('id', $keepIds)
            ->delete();

        $this->assertLessThanOrEqual(2, $channel->scheduleEntries()->where('end_time', '>', now())->count());

        // Call EPG service getEpgData - should auto-replenish schedule because future entries < 10
        $epgService = app(ProgrammeScheduleService::class);
        $epgData = $epgService->getEpgData($channel);

        $newFutureCount = $channel->scheduleEntries()->where('end_time', '>', now())->count();
        $this->assertGreaterThan(10, $newFutureCount);
        $this->assertNotNull($epgData['now_playing']);
    }
}
