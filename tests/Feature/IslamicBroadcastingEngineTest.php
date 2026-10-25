<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\IslamicContentProvider;
use App\Services\Zonita\IslamicRightsVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IslamicBroadcastingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'youtube.com/oembed*' => Http::response([
                'title' => 'Verified Islamic Broadcast Source',
                'author_name' => 'Official Media Channel',
                'type' => 'video',
                'provider_name' => 'YouTube',
            ], 200),
            '*' => Http::response([], 200),
        ]);
    }

    public function test_sync_islamic_engine_command_syncs_providers_and_deploys_all_8_channels(): void
    {
        $this->artisan('islamic:sync-engine --days=7')->assertExitCode(0)->run();

        // 1. Verify Provider Directory
        $providerCount = IslamicContentProvider::count();
        $this->assertGreaterThanOrEqual(25, $providerCount);

        // Verify Key African Studios & Media Houses
        $arewa24 = IslamicContentProvider::where('name', 'Arewa24')->first();
        $this->assertNotNull($arewa24);
        $this->assertEquals('Hausa', $arewa24->primary_language);
        $this->assertTrue(in_array('zonita-arewa-movies', $arewa24->target_channels));

        $saira = IslamicContentProvider::where('name', 'Saira Movies')->first();
        $this->assertNotNull($saira);
        $this->assertEquals('production_company', $saira->provider_type);
        $this->assertEquals('OFFICIAL_CONTENT_OWNER', $saira->source_classification);

        $fkd = IslamicContentProvider::where('name', 'FKD Productions')->first();
        $this->assertNotNull($fkd);
        $this->assertEquals('Kano / Kaduna', $fkd->headquarters_city);

        // Verify Scholar vs Producer classification
        $menk = IslamicContentProvider::where('name', 'like', '%Mufti Menk%')->first();
        $this->assertNotNull($menk);
        $this->assertEquals('individual_scholar', $menk->provider_type);
        $this->assertFalse(in_array('zonita-arewa-movies', $menk->target_channels));
        $this->assertTrue(in_array('zonita-islamic-knowledge', $menk->target_channels));

        // 2. Verify all 8 Islamic Virtual Channels are Deployed and Scheduled
        $expectedChannels = [
            'zonita-islamic-cinema',
            'zonita-arewa-movies',
            'zonita-yoruba-islamic',
            'zonita-quran',
            'zonita-islamic-knowledge',
            'zonita-islamic-documentaries',
            'zonita-nasheed',
            'zonita-islamic-family',
        ];

        foreach ($expectedChannels as $slug) {
            $channel = Channel::where('slug', $slug)->first();
            $this->assertNotNull($channel, "Channel {$slug} must exist in the database.");
            $this->assertTrue($channel->is_active);
            $this->assertTrue($channel->is_published);
            $this->assertEquals('embed_player', $channel->playback_mode);

            // Channel candidate pool attached
            $this->assertGreaterThan(0, $channel->channelVideos()->count(), "Channel {$slug} must have videos in its pool.");

            // Continuous rolling schedule generated
            $this->assertGreaterThan(0, $channel->scheduleEntries()->count(), "Channel {$slug} must have rolling schedule entries.");
        }
    }

    public function test_rights_verification_service_enforces_broadcast_rules(): void
    {
        $rightsService = app(IslamicRightsVerificationService::class);

        // Schedulable approved provider
        $approvedProvider = new IslamicContentProvider([
            'source_classification' => 'OFFICIAL_CONTENT_OWNER',
            'licensing_status' => 'ACTIVE_BROADCASTING',
            'commercial_use_permitted' => true,
            'continuous_24_7_permitted' => true,
        ]);

        $this->assertTrue($rightsService->isCandidateSchedulable(['youtube_id' => 'valid_video_id'], $approvedProvider));

        // Block DISCOVERY_ONLY and UNVERIFIED leads from linear playout
        $discoveryOnlyProvider = new IslamicContentProvider([
            'source_classification' => 'DISCOVERY_ONLY',
            'licensing_status' => 'PROSPECT_IDENTIFIED',
        ]);

        $this->assertFalse($rightsService->isCandidateSchedulable(['youtube_id' => 'valid_video_id'], $discoveryOnlyProvider));

        $unverifiedProvider = new IslamicContentProvider([
            'source_classification' => 'UNVERIFIED_SOURCE',
            'licensing_status' => 'UNVERIFIED',
        ]);

        $this->assertFalse($rightsService->isCandidateSchedulable(['youtube_id' => 'valid_video_id'], $unverifiedProvider));
    }

    public function test_islamic_web_directory_renders_and_supports_filtering(): void
    {
        $this->artisan('islamic:sync-engine')->run();

        // 1. Index Page
        $response = $this->get(route('islamic.index'));
        $response->assertStatus(200);
        $response->assertSee('Islamic Cinema, Hausa Kannywood, Yoruba Drama');
        $response->assertSee('8 Dedicated 24/7 Islamic Virtual Channels');
        $response->assertSee('Zonita Arewa Movies');
        $response->assertSee('Zonita Quran');
        $response->assertSee('Arewa24');

        // 2. Filter by Hausa / Arewa Channel
        $responseArewa = $this->get(route('islamic.index', ['channel' => 'zonita-arewa-movies']));
        $responseArewa->assertStatus(200);
        $responseArewa->assertSee('Arewa24');
        $responseArewa->assertSee('Saira Movies');

        // 3. Filter by Individual Scholar
        $responseScholars = $this->get(route('islamic.index', ['type' => 'individual_scholar']));
        $responseScholars->assertStatus(200);
        $responseScholars->assertSee('Mufti Menk');
        $responseScholars->assertSee('Assim Al-Hakeem');
    }

    public function test_islamic_channels_render_in_realtime_mode_with_controls_disabled(): void
    {
        $this->artisan('islamic:sync-engine')->run();

        // 1. Arewa Kannywood channel
        $response = $this->get(route('channels.show', 'zonita-arewa-movies'));
        $response->assertStatus(200);
        $response->assertSee('Zonita Arewa Movies');
        $response->assertSee('24/7 REAL-TIME BROADCAST');
        $response->assertSee("'controls': 0", false);
        $response->assertSee("'disablekb': 1", false);

        // 2. Quran 24/7 channel
        $responseQuran = $this->get(route('channels.show', 'zonita-quran'));
        $responseQuran->assertStatus(200);
        $responseQuran->assertSee('Zonita Quran');
        $responseQuran->assertSee("'controls': 0", false);
    }

    public function test_homepage_and_navigation_surface_islamic_broadcasting(): void
    {
        $this->artisan('islamic:sync-engine')->run();

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Islamic');
        $response->assertSee(route('islamic.index'));
        $response->assertSee('Islamic Cinema, Hausa Kannywood & 24/7 Channels');
    }
}
