<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\User;
use App\Models\Video;
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloudinaryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cloudinary_service_is_configured_and_generates_signatures(): void
    {
        $service = app(CloudinaryService::class);

        $this->assertTrue($service->isConfigured());
        $this->assertEquals('jcjugta9', $service->getCloudName());
        $this->assertEquals('269289579262165', $service->getApiKey());

        $signatureData = $service->generateUploadSignature(['folder' => 'zonita/test']);
        $this->assertArrayHasKey('timestamp', $signatureData);
        $this->assertArrayHasKey('api_key', $signatureData);
        $this->assertArrayHasKey('cloud_name', $signatureData);
        $this->assertEquals('jcjugta9', $signatureData['cloud_name']);
        $this->assertEquals('269289579262165', $signatureData['api_key']);
    }

    public function test_cloudinary_service_builds_transformation_and_fetch_urls(): void
    {
        $service = app(CloudinaryService::class);

        $url = $service->url('zonita/logos/sample_logo', [
            'width' => 500,
            'height' => 500,
            'crop' => 'fill',
        ]);

        $this->assertStringContainsString('https://res.cloudinary.com/jcjugta9/image/upload/', $url);
        $this->assertStringContainsString('w_500', $url);
        $this->assertStringContainsString('h_500', $url);
        $this->assertStringContainsString('c_fill', $url);
        $this->assertStringContainsString('f_auto', $url);
        $this->assertStringContainsString('q_auto', $url);

        $fetchUrl = $service->fetchUrl('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg');
        $this->assertStringContainsString('https://res.cloudinary.com/jcjugta9/image/fetch/', $fetchUrl);
        $this->assertStringContainsString('f_auto', $fetchUrl);
    }

    public function test_channel_model_handles_cloudinary_urls_correctly(): void
    {
        $channel = Channel::create([
            'name' => 'Cloud TV',
            'slug' => 'cloud-tv',
            'official_destination_url' => 'https://example.com',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'embed_player',
            'logo_path' => 'https://res.cloudinary.com/jcjugta9/image/upload/v12345/zonita/logos/sample.png',
            'banner_path' => 'https://res.cloudinary.com/jcjugta9/image/upload/v12345/zonita/banners/sample.jpg',
            'is_active' => true,
            'is_published' => true,
        ]);

        $this->assertEquals('https://res.cloudinary.com/jcjugta9/image/upload/v12345/zonita/logos/sample.png', $channel->logo_url);
        $this->assertEquals('https://res.cloudinary.com/jcjugta9/image/upload/v12345/zonita/banners/sample.jpg', $channel->banner_url);
    }

    public function test_video_model_handles_thumbnails_with_cloudinary_cdn(): void
    {
        $videoWithCustomThumbnail = Video::create([
            'youtube_id' => 'abc123xyz',
            'title' => 'Custom Video',
            'thumbnail_url' => 'https://res.cloudinary.com/jcjugta9/image/upload/v1/zonita/thumbnails/custom.jpg',
            'media_url' => 'https://res.cloudinary.com/jcjugta9/video/upload/v1/zonita/media/promo.mp4',
            'duration_seconds' => 300,
        ]);

        $this->assertEquals('https://res.cloudinary.com/jcjugta9/image/upload/v1/zonita/thumbnails/custom.jpg', $videoWithCustomThumbnail->thumbnail_url);

        $videoWithYouTubeFallback = Video::create([
            'youtube_id' => 'def456uvw',
            'title' => 'YouTube Stream Video',
            'duration_seconds' => 600,
        ]);

        $this->assertStringContainsString('https://res.cloudinary.com/jcjugta9/image/fetch/', $videoWithYouTubeFallback->thumbnail_url);
        $this->assertStringContainsString('def456uvw', $videoWithYouTubeFallback->thumbnail_url);
    }

    public function test_admin_media_dashboard_and_signature_endpoints_work(): void
    {
        $response = $this->get(route('admin.media.index'));
        $response->assertStatus(200);
        $response->assertSee('Cloudinary Media Storage & Delivery', false);
        $response->assertSee('jcjugta9', false);
        $response->assertSee('Launch Upload Widget', false);

        $sigResponse = $this->getJson(route('admin.media.signature', ['folder' => 'zonita/logos']));
        $sigResponse->assertStatus(200);
        $sigResponse->assertJsonStructure([
            'timestamp',
            'api_key',
            'cloud_name',
            'upload_preset',
        ]);
        $this->assertEquals('jcjugta9', $sigResponse->json('cloud_name'));
        $this->assertEquals('269289579262165', $sigResponse->json('api_key'));
    }

    public function test_admin_can_save_channel_with_cloudinary_logo_and_banner(): void
    {
        $category = Category::create([
            'name' => 'Entertainment',
            'slug' => 'entertainment',
        ]);

        $postData = [
            'name' => 'Afrobeats HD',
            'tagline' => 'Best African Music',
            'description' => '24/7 Afrobeats channel',
            'logo_path' => 'https://res.cloudinary.com/jcjugta9/image/upload/v1/zonita/logos/afrobeats.png',
            'banner_path' => 'https://res.cloudinary.com/jcjugta9/image/upload/v1/zonita/banners/afrobeats_banner.jpg',
            'official_destination_url' => 'https://afrobeats.example.com',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'embed_player',
            'rights_holder_name' => 'Afrobeats Network Ltd',
            'rights_status' => 'embedding_approved',
            'categories' => [$category->id],
        ];

        $response = $this->post(route('admin.channels.store'), $postData);
        $response->assertRedirect(route('admin.channels.index'));

        $this->assertDatabaseHas('channels', [
            'name' => 'Afrobeats HD',
            'logo_path' => 'https://res.cloudinary.com/jcjugta9/image/upload/v1/zonita/logos/afrobeats.png',
            'banner_path' => 'https://res.cloudinary.com/jcjugta9/image/upload/v1/zonita/banners/afrobeats_banner.jpg',
        ]);
    }
}
