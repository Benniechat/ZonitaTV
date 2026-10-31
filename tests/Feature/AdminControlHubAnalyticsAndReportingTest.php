<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\ContentRight;
use App\Models\ScheduleEntry;
use App\Models\User;
use App\Models\Video;
use App\Models\ViewingEvent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControlHubAnalyticsAndReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create([
            'email' => 'admin@zonitatv.com',
            'role' => 'super_admin',
        ]);
    }

    public function test_executive_control_hub_dashboard_renders_metrics_and_health_gauges(): void
    {
        $channelA = Channel::create([
            'name' => 'NTA National 1',
            'slug' => 'nta-national-1',
            'channel_number' => '101',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_technically_healthy' => true,
            'is_virtual' => true,
            'is_published' => true,
            'is_active' => true,
        ]);

        $channelB = Channel::create([
            'name' => 'Degraded Regional Stream',
            'slug' => 'degraded-regional-stream',
            'channel_number' => '102',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'embed_player',
            'is_technically_healthy' => false,
            'is_virtual' => false,
            'is_published' => true,
            'is_active' => true,
        ]);

        $video = Video::create([
            'title' => 'Evening News Live',
            'slug' => 'evening-news-live',
            'youtube_id' => 'news_live_99',
            'duration_seconds' => 3600,
            'is_active' => true,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channelA->id,
            'video_id' => $video->id,
            'title' => 'Evening News Live',
            'duration_seconds' => 3600,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(50),
            'status' => 'playing',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Executive Control Hub');
        $response->assertSee('NTA National 1');
        $response->assertSee('Evening News Live');
        $response->assertSee('Stream Disruption Detected');
        $response->assertSee('50% Uptime'); // 1 healthy out of 2 = 50%
    }

    public function test_audience_analytics_dashboard_renders_kpis_and_timeframe_filters(): void
    {
        $channel = Channel::create([
            'name' => 'Channels Television HD',
            'slug' => 'channels-tv-hd',
            'channel_number' => '202',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);

        // Create viewing events
        for ($i = 0; $i < 6; $i++) {
            ViewingEvent::create([
                'channel_id' => $channel->id,
                'session_id' => 'sess_'.$i,
                'event_type' => 'playback_started',
                'ip_country' => 'NGA',
                'created_at' => now()->subDays(1),
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            ViewingEvent::create([
                'channel_id' => $channel->id,
                'session_id' => 'sess_uk_'.$i,
                'event_type' => 'guide_view',
                'ip_country' => 'GBR',
                'created_at' => now()->subDays(2),
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.analytics.index', ['period' => '7d']));

        $response->assertStatus(200);
        $response->assertSee('Audience Analytics');
        $response->assertSee('Channels Television HD');
        $response->assertSee('Playback Started Events');
        $response->assertSee('6'); // 6 playback starts
        $response->assertSee('NGA');
        $response->assertSee('GBR');
    }

    public function test_audience_analytics_export_endpoint_returns_json_report(): void
    {
        $channel = Channel::create([
            'name' => 'Soundcity Music Live',
            'slug' => 'soundcity-music-live',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);

        ViewingEvent::create([
            'channel_id' => $channel->id,
            'event_type' => 'playback_started',
            'ip_country' => 'NGA',
            'created_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.analytics.export', ['period' => '30d']));

        $response->assertStatus(200);
        $response->assertJsonPath('report', 'Zoniva TV Audience Analytics');
        $response->assertJsonPath('period', '30d');
        $response->assertJsonStructure([
            'report',
            'period',
            'generated_at',
            'summary' => [
                'total_events',
                'playback_starts',
            ],
            'top_channels',
            'countries',
        ]);
    }

    public function test_rights_compliance_audit_dashboard_calculates_clearance_score_and_radar(): void
    {
        $channelExpiring = Channel::create([
            'name' => 'Expiring Soon Station',
            'slug' => 'expiring-soon-station',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'embed_player',
            'is_active' => true,
            'is_published' => true,
        ]);

        ContentRight::create([
            'channel_id' => $channelExpiring->id,
            'rights_holder_name' => 'African Media Group',
            'classification' => 'direct_license',
            'effective_from' => now()->subMonths(6),
            'expires_at' => now()->addDays(5), // Expiring in 5 days
            'permitted_territories' => ['NGA'],
            'allow_directory_listing' => true,
            'allow_web_embedding' => true,
            'status' => 'cleared',
        ]);

        $channelPerpetual = Channel::create([
            'name' => 'Perpetual Original Station',
            'slug' => 'perpetual-original-station',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.compliance.index'));

        $response->assertStatus(200);
        $response->assertSee('Rights Compliance');
        $response->assertSee('Compliance Health Score');
        $response->assertSee('Expiring Within 7 Days');
        $response->assertSee('African Media Group');
        $response->assertSee('Expiring Soon Station');
    }

    public function test_rights_remediation_action_extends_licensing_validity(): void
    {
        $channel = Channel::create([
            'name' => 'Remediation Test TV',
            'slug' => 'remediation-test-tv',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'embed_player',
            'is_active' => true,
            'is_published' => true,
        ]);

        $right = ContentRight::create([
            'channel_id' => $channel->id,
            'rights_holder_name' => 'Test Licensor Inc',
            'classification' => 'direct_license',
            'effective_from' => now()->subMonths(11),
            'expires_at' => now()->addDays(2),
            'permitted_territories' => ['*'],
            'allow_directory_listing' => true,
            'allow_web_embedding' => true,
            'status' => 'cleared',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.compliance.remediate', $right->id), [
            'action' => 'extend_1_year',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $right->refresh();
        $this->assertTrue(Carbon::parse($right->expires_at)->isAfter(now()->addMonths(11)));
        $this->assertEquals('cleared', $right->status);
    }

    public function test_unauthorized_guests_and_regular_viewers_cannot_access_analytics_or_compliance(): void
    {
        // 1. Guest redirected to login
        $resGuestAnalytics = $this->get(route('admin.analytics.index'));
        $resGuestAnalytics->assertRedirect(route('login'));

        $resGuestCompliance = $this->get(route('admin.compliance.index'));
        $resGuestCompliance->assertRedirect(route('login'));

        // 2. Regular user (role = 'user') gets 403 Forbidden
        $viewer = User::factory()->create(['role' => 'user']);

        $resViewerAnalytics = $this->actingAs($viewer)->get(route('admin.analytics.index'));
        $resViewerAnalytics->assertStatus(403);

        $resViewerCompliance = $this->actingAs($viewer)->get(route('admin.compliance.index'));
        $resViewerCompliance->assertStatus(403);
    }
}
