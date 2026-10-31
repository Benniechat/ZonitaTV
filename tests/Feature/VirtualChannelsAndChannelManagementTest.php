<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\ContentPartner;
use App\Models\ScheduleEntry;
use App\Models\User;
use App\Models\Video;
use App\Services\Zonita\VirtualChannelManagerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VirtualChannelsAndChannelManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_24_7_virtual_channel_with_station_branding_and_rules(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $partner = ContentPartner::create([
            'name' => 'Royal Arts Academy Broadcast Group',
            'partner_type' => 'studio',
            'pipeline_stage' => 'active',
            'country_code' => 'NGA',
        ]);
        $category = Category::create(['name' => 'African Drama', 'slug' => 'african-drama']);

        $service = app(VirtualChannelManagerService::class);

        $channel = $service->createVirtualChannel([
            'name' => 'Zonita Royal Drama 24/7',
            'channel_number' => '201',
            'tagline' => 'Non-stop curated African romance and epics',
            'description' => 'Continuous cinematic drama stream from leading Nigerian studios.',
            'brand_color' => '#8B5CF6',
            'watermark_logo_path' => 'https://cdn.zonita.tv/watermarks/royal_bug.png',
            'logo_path' => 'https://cdn.zonita.tv/logos/royal_logo.png',
            'banner_path' => 'https://cdn.zonita.tv/banners/royal_banner.jpg',
            'fallback_video_id' => 'royal_slate_01',
            'station_ident_video_id' => 'royal_ident_01',
            'content_partner_id' => $partner->id,
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_published' => true,
            'categories' => [$category->id],
            'rules' => [
                'search_keywords' => ['Royal Arts', 'Nollywood Romance', 'Emem Isong', 'African Cinema'],
                'excluded_keywords' => ['trailer', 'teaser', 'interview'],
                'approved_publishers' => ['Royal Arts Academy', 'Nollywood Picture'],
                'preferred_formats' => ['movie', 'episode'],
                'min_duration_minutes' => 30,
                'max_duration_minutes' => 150,
                'repeat_cooldown_hours' => 12,
                'min_reserve_count' => 8,
                'scheduling_mode' => 'hybrid',
                'priority_level' => 'premier_heavy',
                'interstitial_ident_interval' => 3,
                'timezone' => 'Africa/Lagos',
            ],
        ], $admin);

        $this->assertDatabaseHas('channels', [
            'id' => $channel->id,
            'name' => 'Zonita Royal Drama 24/7',
            'channel_number' => '201',
            'brand_color' => '#8B5CF6',
            'watermark_logo_path' => 'https://cdn.zonita.tv/watermarks/royal_bug.png',
            'station_ident_video_id' => 'royal_ident_01',
            'content_partner_id' => $partner->id,
            'is_virtual' => 1,
            'is_published' => 1,
        ]);

        $this->assertTrue($channel->isVirtual());
        $this->assertEquals('201', $channel->display_number);
        $this->assertEquals('#8B5CF6', $channel->accent_color);

        $this->assertDatabaseHas('channel_source_rules', [
            'channel_id' => $channel->id,
            'priority_level' => 'premier_heavy',
            'repeat_cooldown_hours' => 12,
            'min_reserve_count' => 8,
            'scheduling_mode' => 'hybrid',
            'interstitial_ident_interval' => 3,
        ]);

        $this->assertEquals(1, $channel->categories()->count());
        $this->assertNotNull($channel->latestRights);
    }

    public function test_channel_number_and_brand_color_helpers_and_scopes(): void
    {
        $virtualChannel = Channel::create([
            'name' => 'Zonita Action Movies',
            'slug' => 'zonita-action-movies',
            'channel_number' => '305',
            'brand_color' => '#EF4444',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-action-movies',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $syndicatedChannel = Channel::create([
            'name' => 'Channels Television News',
            'slug' => 'channels-television-news',
            'official_destination_url' => 'https://channelstv.com/live',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_virtual' => false,
            'is_active' => true,
            'is_published' => false, // Off-air
        ]);

        $this->assertEquals('305', $virtualChannel->display_number);
        $this->assertEquals('#EF4444', $virtualChannel->accent_color);
        $this->assertTrue($virtualChannel->isVirtual());
        $this->assertFalse($syndicatedChannel->isVirtual());

        // Default fallback dial number
        $this->assertEquals(strval(100 + $syndicatedChannel->id), $syndicatedChannel->display_number);

        // Scopes test
        $this->assertEquals(1, Channel::virtual()->count());
        $this->assertEquals(1, Channel::onAir()->count());
    }

    public function test_playout_priority_rules_configuration_and_updates(): void
    {
        $admin = User::factory()->create(['role' => 'content_manager']);
        $service = app(VirtualChannelManagerService::class);

        $channel = Channel::create([
            'name' => 'Zonita Kids Club',
            'slug' => 'zonita-kids-club',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-kids-club',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
        ]);

        $rule = $service->updatePlayoutPriorityRules($channel, [
            'search_keywords' => ['African Animation', 'Bino and Fino', 'Kids Stories'],
            'excluded_keywords' => ['violence', 'gun'],
            'priority_level' => 'deep_catalog',
            'scheduling_mode' => 'blocks',
            'repeat_cooldown_hours' => 24,
            'min_reserve_count' => 10,
            'min_duration_minutes' => 5,
            'max_duration_minutes' => 30,
            'timezone' => 'Africa/Lagos',
        ]);

        $this->assertEquals('deep_catalog', $rule->priority_level);
        $this->assertEquals('blocks', $rule->scheduling_mode);
        $this->assertEquals(24, $rule->repeat_cooldown_hours);
        $this->assertEquals(10, $rule->min_reserve_count);

        // Update via Controller PUT endpoint
        $response = $this->actingAs($admin)->put(route('admin.channels.update', $channel->id), [
            'name' => 'Zonita Kids & Family Fun',
            'channel_number' => '401',
            'brand_color' => '#10B981',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-kids-club',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => 1,
            'priority_level' => 'balanced',
            'search_keywords' => 'Animation, Stories, Songs',
            'repeat_cooldown_hours' => 18,
        ]);

        $response->assertRedirect(route('admin.channels.show', $channel->id));

        $this->assertDatabaseHas('channels', [
            'id' => $channel->id,
            'name' => 'Zonita Kids & Family Fun',
            'channel_number' => '401',
            'brand_color' => '#10B981',
        ]);

        $this->assertDatabaseHas('channel_source_rules', [
            'channel_id' => $channel->id,
            'priority_level' => 'balanced',
            'repeat_cooldown_hours' => 18,
        ]);
    }

    public function test_virtual_channel_manager_computes_playout_health_and_reserve_buffer(): void
    {
        $service = app(VirtualChannelManagerService::class);

        $channel = Channel::create([
            'name' => 'Zonita Sports 24/7',
            'slug' => 'zonita-sports-24-7',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-sports-24-7',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        ChannelSourceRule::create([
            'channel_id' => $channel->id,
            'search_keywords' => ['Football', 'Boxing'],
            'min_reserve_count' => 3,
            'timezone' => 'Africa/Lagos',
        ]);

        $now = Carbon::now('Africa/Lagos');

        $video1 = Video::create([
            'youtube_id' => 'sp_vid_01',
            'title' => 'African Champions Cup Final',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
            'format_type' => 'highlights',
        ]);

        $video2 = Video::create([
            'youtube_id' => 'sp_vid_02',
            'title' => 'World Boxing Super Series',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
            'format_type' => 'highlights',
        ]);

        // Current on-air entry
        $currentEntry = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video1->id,
            'title' => 'African Champions Cup Final',
            'start_time' => $now->copy()->subMinutes(20),
            'end_time' => $now->copy()->addMinutes(40),
            'duration_seconds' => 3600,
            'play_order' => 1,
            'status' => 'scheduled',
        ]);

        // Upcoming entry
        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video2->id,
            'title' => 'World Boxing Super Series',
            'start_time' => $now->copy()->addMinutes(40),
            'end_time' => $now->copy()->addMinutes(100),
            'duration_seconds' => 3600,
            'play_order' => 2,
            'status' => 'scheduled',
        ]);

        $health = $service->getChannelPlayoutHealth($channel);

        $this->assertTrue($health['is_on_air']);
        $this->assertNotNull($health['current_entry']);
        $this->assertEquals($currentEntry->id, $health['current_entry']->id);
        $this->assertCount(1, $health['upcoming_entries']);
        $this->assertGreaterThan(0, $health['scheduled_hours_ahead']);
        $this->assertArrayHasKey('health_score', $health);
    }

    public function test_admin_can_toggle_channel_on_air_and_off_air_status(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $channel = Channel::create([
            'name' => 'Zonita Heritage',
            'slug' => 'zonita-heritage',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-heritage',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => false,
        ]);

        // 1. Toggle to On-Air (Published)
        $response = $this->actingAs($admin)->post(route('admin.channels.toggle-status', $channel->id), [
            'action' => 'toggle_publish',
        ]);

        $response->assertRedirect();
        $this->assertTrue($channel->fresh()->is_published);

        // 2. Toggle to Off-Air (Unpublished)
        $this->actingAs($admin)->post(route('admin.channels.toggle-status', $channel->id), [
            'action' => 'toggle_publish',
        ]);

        $this->assertFalse($channel->fresh()->is_published);
    }

    public function test_admin_channel_show_profile_displays_station_branding_and_playout_monitor(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $channel = Channel::create([
            'name' => 'Zonita Comedy Club 24/7',
            'slug' => 'zonita-comedy-club-24-7',
            'channel_number' => '501',
            'brand_color' => '#F59E0B',
            'tagline' => 'Laugh Non-Stop with African Standup Stars',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-comedy-club-24-7',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        ChannelSourceRule::create([
            'channel_id' => $channel->id,
            'search_keywords' => ['Standup Comedy', 'Basketmouth', 'AY Live'],
            'priority_level' => 'balanced',
            'repeat_cooldown_hours' => 6,
            'timezone' => 'Africa/Lagos',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.channels.show', $channel->id));

        $response->assertOk();
        $response->assertSee('Zonita Comedy Club 24/7');
        $response->assertSee('CH 501');
        $response->assertSee('#F59E0B');
        $response->assertSee('Laugh Non-Stop with African Standup Stars');
        $response->assertSee('Playout Priority Rules');
        $response->assertSee('Station Health Score');
    }

    public function test_channel_soft_delete_and_lifecycle_management(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $channel = Channel::create([
            'name' => 'Temporary Promo Channel',
            'slug' => 'temporary-promo-channel',
            'official_destination_url' => 'https://zonitatv.com/channel/temporary-promo-channel',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.channels.destroy', $channel->id));

        $response->assertRedirect(route('admin.channels.index'));
        $this->assertSoftDeleted('channels', ['id' => $channel->id]);
    }

    public function test_unprivileged_users_cannot_manage_channels(): void
    {
        $channel = Channel::create([
            'name' => 'Secured Broadcast Channel',
            'slug' => 'secured-broadcast-channel',
            'official_destination_url' => 'https://zonitatv.com/channel/secured-broadcast-channel',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
        ]);

        // Guest redirected
        $guestResp = $this->get(route('admin.channels.index'));
        $guestResp->assertRedirect(route('login'));

        // Standard user forbidden
        $regularUser = User::factory()->create(['role' => 'user']);
        $userResp = $this->actingAs($regularUser)->get(route('admin.channels.index'));
        $userResp->assertForbidden();

        $userShowResp = $this->actingAs($regularUser)->get(route('admin.channels.show', $channel->id));
        $userShowResp->assertForbidden();
    }
}
