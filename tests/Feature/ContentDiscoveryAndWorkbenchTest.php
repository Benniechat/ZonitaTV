<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelVideo;
use App\Models\ContentProvider;
use App\Models\DiscoveryCandidate;
use App\Models\User;
use App\Models\Video;
use App\Services\Zonita\ContentDiscoveryPipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentDiscoveryAndWorkbenchTest extends TestCase
{
    use RefreshDatabase;

    public function test_discovery_candidate_intake_and_metadata_normalization(): void
    {
        $service = app(ContentDiscoveryPipelineService::class);

        $result = $service->ingestCandidate([
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'source_type' => 'youtube_video',
            'title' => 'Sample Cultural Music Showcase',
            'creator_name' => 'African Heritage TV',
            'duration_seconds' => 3600,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
            'rights_basis' => 'official_embed_permitted',
        ]);

        $this->assertEquals('ingested', $result['status']);
        $candidate = $result['candidate'];
        $this->assertInstanceOf(DiscoveryCandidate::class, $candidate);
        $this->assertEquals('dQw4w9WgXcQ', $candidate->source_identifier);
        $this->assertEquals('staged', $candidate->intake_status);
        $this->assertNotEmpty($candidate->content_hash);
        $this->assertEquals('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $candidate->thumbnail_url);

        $this->assertDatabaseHas('discovery_candidates', [
            'id' => $candidate->id,
            'source_identifier' => 'dQw4w9WgXcQ',
            'intake_status' => 'staged',
        ]);
    }

    public function test_discovery_deduplication_engine_detects_duplicate_staged_content(): void
    {
        $service = app(ContentDiscoveryPipelineService::class);

        // First intake
        $result1 = $service->ingestCandidate([
            'source_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'source_type' => 'youtube_video',
            'title' => 'Original Staged Candidate',
        ]);
        $this->assertEquals('ingested', $result1['status']);

        // Duplicate intake attempt with different URL variant of same video
        $result2 = $service->ingestCandidate([
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'source_type' => 'youtube_video',
            'title' => 'Duplicate Candidate Title',
        ]);

        $this->assertEquals('duplicate', $result2['status']);
        $this->assertEquals(1, DiscoveryCandidate::where('source_identifier', 'dQw4w9WgXcQ')->count());
    }

    public function test_discovery_intake_detects_existing_active_catalogue_video(): void
    {
        Video::create([
            'youtube_id' => 'existing_001',
            'title' => 'Already In Broadcast Catalogue',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'rights_basis' => 'official_embed_permitted',
            'format_type' => 'feature',
        ]);

        $service = app(ContentDiscoveryPipelineService::class);
        $result = $service->ingestCandidate([
            'source_url' => 'https://www.youtube.com/watch?v=existing_001',
            'source_type' => 'youtube_video',
        ]);

        $this->assertEquals('duplicate', $result['status']);
        $this->assertStringContainsString('already exists in active broadcast catalogue', $result['reason']);
        $this->assertEquals(0, DiscoveryCandidate::where('source_identifier', 'existing_001')->count());
    }

    public function test_discovery_intake_rejects_blacklisted_or_takedown_content(): void
    {
        Video::create([
            'youtube_id' => 'takedown_001',
            'title' => 'Copyright Infringing Media',
            'duration_seconds' => 1800,
            'availability_status' => 'complaint_received',
            'rights_basis' => 'under_review',
            'format_type' => 'feature',
        ]);

        $service = app(ContentDiscoveryPipelineService::class);
        $result = $service->ingestCandidate([
            'source_url' => 'https://www.youtube.com/watch?v=takedown_001',
            'source_type' => 'youtube_video',
        ]);

        $this->assertEquals('rejected', $result['status']);
        $this->assertStringContainsString('legal takedown', $result['reason']);
        $this->assertEquals(0, DiscoveryCandidate::where('source_identifier', 'takedown_001')->count());
    }

    public function test_admin_workbench_requires_privileged_role(): void
    {
        // Guest redirected
        $guestResponse = $this->get(route('admin.discovery.index'));
        $guestResponse->assertRedirect(route('login'));

        // Standard user forbidden
        $standardUser = User::factory()->create(['role' => 'user']);
        $userResponse = $this->actingAs($standardUser)->get(route('admin.discovery.index'));
        $userResponse->assertForbidden();

        // Content Manager permitted
        $contentManager = User::factory()->create(['role' => 'content_manager']);
        $cmResponse = $this->actingAs($contentManager)->get(route('admin.discovery.index'));
        $cmResponse->assertOk();

        // Super Admin permitted
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $saResponse = $this->actingAs($superAdmin)->get(route('admin.discovery.index'));
        $saResponse->assertOk();
    }

    public function test_candidate_approval_promotes_to_active_video_and_attaches_channel(): void
    {
        $manager = User::factory()->create(['role' => 'content_manager']);

        $channel = Channel::create([
            'name' => 'African Discovery Channel',
            'slug' => 'african-discovery-channel',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'official_destination_url' => 'https://youtube.com/@DiscoveryTV',
            'is_active' => true,
            'is_published' => true,
        ]);

        $category = Category::create([
            'name' => 'Culture & History',
            'slug' => 'culture-history',
            'sort_order' => 1,
            'is_nav_visible' => true,
        ]);

        $candidate = DiscoveryCandidate::create([
            'source_type' => 'youtube_video',
            'source_url' => 'https://www.youtube.com/watch?v=promo_test01',
            'source_identifier' => 'promo_test01',
            'content_hash' => hash('sha256', 'youtube_video:promo_test01'),
            'title' => 'Ancient Kingdoms of West Africa',
            'creator_name' => 'History Guild',
            'thumbnail_url' => 'https://img.youtube.com/vi/promo_test01/hqdefault.jpg',
            'duration_seconds' => 4200,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
            'rights_basis' => 'official_embed_permitted',
            'intake_status' => 'staged',
        ]);

        $approveResponse = $this->actingAs($manager)->post(route('admin.discovery.approve', $candidate->id), [
            'channel_id' => $channel->id,
            'category_id' => $category->id,
        ]);

        $approveResponse->assertRedirect(route('admin.discovery.index'));

        // Assert candidate status updated
        $candidate->refresh();
        $this->assertEquals('approved', $candidate->intake_status);
        $this->assertEquals($manager->id, $candidate->reviewed_by);
        $this->assertNotNull($candidate->reviewed_at);
        $this->assertNotNull($candidate->approved_video_id);

        // Assert Video created in active catalogue
        $video = Video::where('youtube_id', 'promo_test01')->first();
        $this->assertNotNull($video);
        $this->assertEquals('Ancient Kingdoms of West Africa', $video->title);
        $this->assertEquals('approved', $video->availability_status);
        $this->assertEquals('culture-history', $video->category_slug);

        // Assert ChannelVideo relationship established
        $this->assertTrue(ChannelVideo::where('channel_id', $channel->id)->where('video_id', $video->id)->exists());
    }

    public function test_candidate_rejection_records_reason_and_reviewer(): void
    {
        $manager = User::factory()->create(['role' => 'content_manager']);

        $candidate = DiscoveryCandidate::create([
            'source_type' => 'youtube_video',
            'source_url' => 'https://www.youtube.com/watch?v=reject_test01',
            'source_identifier' => 'reject_test01',
            'content_hash' => hash('sha256', 'youtube_video:reject_test01'),
            'title' => 'Low Resolution Test Clip',
            'duration_seconds' => 60,
            'rights_basis' => 'official_embed_permitted',
            'intake_status' => 'staged',
        ]);

        $rejectResponse = $this->actingAs($manager)->post(route('admin.discovery.reject', $candidate->id), [
            'rejection_reason' => 'Production quality below network broadcast minimums',
        ]);

        $rejectResponse->assertRedirect(route('admin.discovery.index'));

        $candidate->refresh();
        $this->assertEquals('rejected', $candidate->intake_status);
        $this->assertEquals('Production quality below network broadcast minimums', $candidate->rejection_reason);
        $this->assertEquals($manager->id, $candidate->reviewed_by);
    }

    public function test_content_provider_registration_and_relationship(): void
    {
        $manager = User::factory()->create(['role' => 'content_manager']);

        $providerData = [
            'name' => 'Nollywood Studio Network',
            'provider_type' => 'youtube_channel',
            'website_url' => 'https://nollywoodstudios.example.com',
            'source_feed_url' => 'https://youtube.com/@NollywoodStudios',
            'contact_email' => 'rights@nollywoodstudios.example.com',
            'default_rights_basis' => 'direct_licence',
            'is_trusted' => '1',
            'notes' => 'Official partner for Nigerian feature films',
        ];

        $response = $this->actingAs($manager)->post(route('admin.discovery.providers.store'), $providerData);
        $response->assertRedirect(route('admin.discovery.providers'));

        $provider = ContentProvider::where('name', 'Nollywood Studio Network')->first();
        $this->assertNotNull($provider);
        $this->assertTrue($provider->is_trusted);
        $this->assertEquals('direct_licence', $provider->default_rights_basis);

        // Stage candidate linked to this provider
        $candidate = DiscoveryCandidate::create([
            'content_provider_id' => $provider->id,
            'source_type' => 'youtube_video',
            'source_url' => 'https://www.youtube.com/watch?v=prov_video01',
            'source_identifier' => 'prov_video01',
            'content_hash' => hash('sha256', 'youtube_video:prov_video01'),
            'title' => 'Royal Destiny (Nollywood Feature)',
            'intake_status' => 'staged',
        ]);

        $this->assertEquals(1, $provider->candidates()->count());
        $this->assertEquals($provider->id, $candidate->provider->id);
    }
}
