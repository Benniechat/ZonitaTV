<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Episode;
use App\Models\Programme;
use App\Models\Season;
use App\Models\Series;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentCatalogueManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_video_metadata_persistence_and_advisory_tags(): void
    {
        $video = Video::create([
            'youtube_id' => 'v_metadata_01',
            'title' => 'Afrobeat Cultural Documentary',
            'description' => 'A documentary exploring the history of African rhythm and modern Afrobeat.',
            'channel_title' => 'African Heritage TV',
            'duration_seconds' => 5400,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
            'content_rating' => 'PG',
            'advisory_tags' => ['Cultural History', 'Live Music'],
            'language_code' => 'yo',
            'country_code' => 'NGA',
            'privacy_status' => 'public',
            'is_embeddable' => true,
            'availability_status' => 'approved',
            'confidence_score' => 100,
            'rights_basis' => 'official_embed_permitted',
            'format_type' => 'feature',
        ]);

        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'youtube_id' => 'v_metadata_01',
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
            'content_rating' => 'PG',
            'language_code' => 'yo',
            'country_code' => 'NGA',
        ]);

        $fresh = Video::find($video->id);
        $this->assertIsArray($fresh->advisory_tags);
        $this->assertContains('Cultural History', $fresh->advisory_tags);
        $this->assertEquals(5400, $fresh->duration_seconds);
    }

    public function test_series_seasons_episodes_relational_hierarchy(): void
    {
        $category = Category::create([
            'name' => 'Drama & Series',
            'slug' => 'drama-series',
            'sort_order' => 1,
            'is_nav_visible' => true,
        ]);

        $series = Series::create([
            'title' => 'Lagos Chronicles',
            'slug' => 'lagos-chronicles',
            'synopsis' => 'A drama series navigating life, dreams, and family ties in Lagos.',
            'release_year' => 2026,
            'genre' => 'Drama',
            'category_id' => $category->id,
            'language_code' => 'en',
            'country_code' => 'NGA',
            'content_rating' => '13+',
            'advisory_tags' => ['Drama', 'Language'],
            'status' => 'running',
            'is_published' => true,
        ]);

        $season1 = Season::create([
            'series_id' => $series->id,
            'season_number' => 1,
            'title' => 'Season 1: New Beginnings',
            'air_year' => 2026,
        ]);

        $season2 = Season::create([
            'series_id' => $series->id,
            'season_number' => 2,
            'title' => 'Season 2: Ambitions',
            'air_year' => 2026,
        ]);

        $videoEp1 = Video::create([
            'youtube_id' => 'ep_lagos_01',
            'title' => 'Lagos Chronicles S01E01 - Pilot',
            'duration_seconds' => 2700,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
            'content_rating' => '13+',
            'availability_status' => 'approved',
            'rights_basis' => 'direct_licence',
            'format_type' => 'episode',
        ]);

        $episode1 = Episode::create([
            'season_id' => $season1->id,
            'episode_number' => 1,
            'title' => 'The Arrival',
            'slug' => 'the-arrival',
            'video_id' => $videoEp1->id,
            'duration_seconds' => 2700,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
        ]);

        $episode2 = Episode::create([
            'season_id' => $season2->id,
            'episode_number' => 1,
            'title' => 'Turning the Tide',
            'slug' => 'turning-the-tide',
            'duration_seconds' => 2800,
        ]);

        // Assert relationships
        $this->assertCount(2, $series->seasons);
        $this->assertEquals(2, $series->episodes()->count());
        $this->assertEquals('Lagos Chronicles', $episode1->season->series->title);
        $this->assertEquals($videoEp1->id, $episode1->video->id);
    }

    public function test_channel_programme_video_relational_hierarchy(): void
    {
        $channel = Channel::create([
            'name' => 'African Heritage Network',
            'slug' => 'african-heritage-network',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'official_destination_url' => 'https://youtube.com/@HeritageTV',
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'v_prog_heritage_01',
            'title' => 'Great Monuments of the Sahara',
            'duration_seconds' => 3600,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
            'availability_status' => 'approved',
            'rights_basis' => 'official_embed_permitted',
            'format_type' => 'feature',
        ]);

        $programme = Programme::create([
            'channel_id' => $channel->id,
            'title' => 'Heritage Prime: Great Monuments of the Sahara',
            'slug' => 'heritage-prime-great-monuments',
            'description' => 'An architectural and historical exploration.',
            'video_record_id' => $video->id,
            'video_id' => $video->youtube_id,
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
        ]);

        $this->assertNotNull($programme->videoRecord);
        $this->assertEquals($video->id, $programme->videoRecord->id);
        $this->assertEquals('Great Monuments of the Sahara', $programme->resolved_video->title);
        $this->assertEquals($channel->id, $programme->channel->id);
    }

    public function test_admin_catalogue_requires_privileged_role(): void
    {
        // Guests redirected to login
        $guestResponse = $this->get(route('admin.catalogue.index'));
        $guestResponse->assertRedirect(route('login'));

        // Standard user forbidden
        $standardUser = User::factory()->create(['role' => 'user']);
        $userResponse = $this->actingAs($standardUser)->get(route('admin.catalogue.index'));
        $userResponse->assertForbidden();

        // Content Manager permitted
        $contentManager = User::factory()->create(['role' => 'content_manager']);
        $cmResponse = $this->actingAs($contentManager)->get(route('admin.catalogue.index'));
        $cmResponse->assertOk();

        // Super Admin permitted
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $saResponse = $this->actingAs($superAdmin)->get(route('admin.catalogue.index'));
        $saResponse->assertOk();
    }

    public function test_admin_can_store_and_update_catalogue_video_metadata(): void
    {
        $manager = User::factory()->create(['role' => 'content_manager']);

        $postData = [
            'title' => 'The African Innovation Summit',
            'youtube_id' => 'v_summit_2026',
            'channel_title' => 'Tech Africa Media',
            'description' => 'Keynote speeches and presentations from the summit.',
            'duration_seconds' => 7200,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
            'content_rating' => 'G',
            'advisory_tags' => 'Technology, Education, Keynote',
            'language_code' => 'en',
            'country_code' => 'RWA',
            'rights_basis' => 'official_embed_permitted',
            'format_type' => 'feature',
            'availability_status' => 'approved',
            'is_embeddable' => '1',
            'is_evergreen' => '1',
        ];

        $response = $this->actingAs($manager)->post(route('admin.catalogue.store'), $postData);

        $video = Video::where('youtube_id', 'v_summit_2026')->first();
        $this->assertNotNull($video);
        $response->assertRedirect(route('admin.catalogue.show', $video->id));

        $this->assertEquals('The African Innovation Summit', $video->title);
        $this->assertEquals('RWA', $video->country_code);
        $this->assertContains('Technology', $video->advisory_tags);

        // Update metadata
        $updateData = array_merge($postData, [
            'title' => 'The African Innovation Summit - Extended Cut',
            'resolution' => '4k',
            'content_rating' => 'PG',
        ]);

        $updateResponse = $this->actingAs($manager)->put(route('admin.catalogue.update', $video->id), $updateData);
        $updateResponse->assertRedirect(route('admin.catalogue.show', $video->id));

        $fresh = $video->fresh();
        $this->assertEquals('The African Innovation Summit - Extended Cut', $fresh->title);
        $this->assertEquals('4k', $fresh->resolution);
        $this->assertEquals('PG', $fresh->content_rating);
    }

    public function test_admin_can_soft_delete_and_restore_catalogue_asset(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $video = Video::create([
            'youtube_id' => 'v_soft_del_test',
            'title' => 'Content Under Review',
            'duration_seconds' => 1200,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
            'availability_status' => 'approved',
            'rights_basis' => 'under_review',
            'format_type' => 'clip',
        ]);

        // Soft delete
        $delResponse = $this->actingAs($admin)->delete(route('admin.catalogue.destroy', $video->id));
        $delResponse->assertRedirect(route('admin.catalogue.index'));

        $this->assertSoftDeleted('videos', ['id' => $video->id]);

        // Restore
        $restoreResponse = $this->actingAs($admin)->post(route('admin.catalogue.restore', $video->id));
        $restoreResponse->assertRedirect(route('admin.catalogue.show', $video->id));

        $this->assertNotSoftDeleted('videos', ['id' => $video->id]);
    }

    public function test_catalogue_search_and_filtering(): void
    {
        $manager = User::factory()->create(['role' => 'content_manager']);

        Video::create([
            'youtube_id' => 'v_search_01',
            'title' => 'Nollywood Gold Classics',
            'channel_title' => 'Nollywood Classics Channel',
            'duration_seconds' => 6000,
            'availability_status' => 'approved',
            'rights_basis' => 'official_embed_permitted',
            'format_type' => 'feature',
        ]);

        Video::create([
            'youtube_id' => 'v_search_02',
            'title' => 'European Football Weekly Highlights',
            'channel_title' => 'Sports Desk',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'rights_basis' => 'official_embed_permitted',
            'format_type' => 'short',
        ]);

        // Search for Nollywood
        $response = $this->actingAs($manager)->get(route('admin.catalogue.index', ['q' => 'Nollywood']));
        $response->assertOk();
        $response->assertSee('Nollywood Gold Classics');
        $response->assertDontSee('European Football Weekly Highlights');

        // Series tab renders cleanly
        $seriesTabResponse = $this->actingAs($manager)->get(route('admin.catalogue.index', ['type' => 'series']));
        $seriesTabResponse->assertOk();
        $seriesTabResponse->assertSee('Series');
        $seriesTabResponse->assertSee('Episodes');
    }
}
