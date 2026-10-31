<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Favourite;
use App\Models\ScheduleEntry;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Video;
use App\Models\ViewingEvent;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationsAndPersonalisationTest extends TestCase
{
    use RefreshDatabase;

    protected RecommendationService $recommendationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recommendationService = app(RecommendationService::class);
    }

    public function test_guest_recommendations_fall_back_to_curated_and_healthy_channels(): void
    {
        $featured = Channel::create([
            'name' => 'Zonita Premier News',
            'slug' => 'zonita-premier-news',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_featured' => true,
            'is_technically_healthy' => true,
            'display_order' => 10,
            'is_active' => true,
            'is_published' => true,
        ]);

        $standard = Channel::create([
            'name' => 'Standard Regional Broadcast',
            'slug' => 'standard-regional-broadcast',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_featured' => false,
            'is_technically_healthy' => false,
            'display_order' => 1,
            'is_active' => true,
            'is_published' => true,
        ]);

        $ranked = $this->recommendationService->rankChannelsForUser(null, 10, 'NGA');

        $this->assertNotEmpty($ranked);
        $this->assertEquals($featured->id, $ranked->first()->id);
        $this->assertEquals("Editor's Choice", $ranked->first()->recommendation_tag);
        $this->assertGreaterThan($ranked->last()->recommendation_score, $ranked->first()->recommendation_score);
    }

    public function test_authenticated_user_recommendations_incorporate_category_preferences_and_favourites(): void
    {
        $catMovies = Category::create(['name' => 'Nollywood Movies', 'slug' => 'nollywood-movies']);
        $catNews = Category::create(['name' => 'Live News', 'slug' => 'live-news']);

        $channelMovies = Channel::create([
            'name' => 'Nollywood Gold HD',
            'slug' => 'nollywood-gold-hd',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channelMovies->categories()->attach($catMovies->id);

        $channelNews = Channel::create([
            'name' => 'National News 24',
            'slug' => 'national-news-24',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channelNews->categories()->attach($catNews->id);

        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'country_code' => 'NGA',
            'preferred_categories' => [$catMovies->id],
            'preferred_languages' => ['en'],
            'enable_recommendations' => true,
        ]);

        // User also saves channelMovies to favourites
        Favourite::create([
            'user_id' => $user->id,
            'channel_id' => $channelMovies->id,
        ]);

        $ranked = $this->recommendationService->rankChannelsForUser($user, 10, 'NGA');

        $this->assertEquals($channelMovies->id, $ranked->first()->id);
        $this->assertEquals('Saved Favourite', $ranked->first()->recommendation_tag);
        $this->assertGreaterThan($channelNews->id ? 70 : 0, $ranked->first()->recommendation_score);
    }

    public function test_viewing_history_events_boost_channel_recommendation_rankings(): void
    {
        $catSports = Category::create(['name' => 'Sports Network', 'slug' => 'sports-network']);

        $channelWatched = Channel::create([
            'name' => 'Super Sports Live',
            'slug' => 'super-sports-live',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channelWatched->categories()->attach($catSports->id);

        $channelUnwatched = Channel::create([
            'name' => 'Alternative Sports TV',
            'slug' => 'alternative-sports-tv',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channelUnwatched->categories()->attach($catSports->id);

        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'country_code' => 'NGA',
            'preferred_categories' => [$catSports->id],
            'preferred_languages' => ['en'],
            'enable_recommendations' => true,
        ]);

        // Record multiple viewing events on channelWatched
        for ($i = 0; $i < 5; $i++) {
            ViewingEvent::create([
                'user_id' => $user->id,
                'channel_id' => $channelWatched->id,
                'event_type' => 'playback_started',
                'ip_country' => 'NGA',
                'created_at' => now()->subMinutes($i * 10),
            ]);
        }

        $ranked = $this->recommendationService->rankChannelsForUser($user, 10, 'NGA');

        $firstRanked = $ranked->first();
        $this->assertEquals($channelWatched->id, $firstRanked->id);
        $this->assertEquals('Frequently Watched', $firstRanked->recommendation_tag);
        $this->assertStringContainsString('viewing', strtolower($firstRanked->recommendation_reason));
    }

    public function test_similar_channel_matching_engine_ranks_topically_related_stations(): void
    {
        $catFaith = Category::create(['name' => 'Gospel & Faith', 'slug' => 'gospel-faith']);
        $catMusic = Category::create(['name' => 'Afrobeats Music', 'slug' => 'afrobeats-music']);

        $targetChannel = Channel::create([
            'name' => 'Faith TV Global',
            'slug' => 'faith-tv-global',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'curation_type' => 'youtube_live',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $targetChannel->categories()->attach($catFaith->id);

        $similarChannel = Channel::create([
            'name' => 'Gospel Broadcast Network',
            'slug' => 'gospel-broadcast-network',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'curation_type' => 'youtube_live',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $similarChannel->categories()->attach($catFaith->id);

        $unrelatedChannel = Channel::create([
            'name' => 'Afrobeats Top 40',
            'slug' => 'afrobeats-top-40',
            'country_code' => 'NGA',
            'language_code' => 'fr',
            'curation_type' => 'hls_direct',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => false,
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => false,
        ]);
        $unrelatedChannel->categories()->attach($catMusic->id);

        $similarList = $this->recommendationService->getSimilarChannels($targetChannel, 5, 'NGA');

        $this->assertNotEmpty($similarList);
        $this->assertEquals($similarChannel->id, $similarList->first()->id);
        $this->assertStringContainsString('Gospel & Faith', $similarList->first()->similarity_reason);
        $this->assertGreaterThan($similarList->last()->similarity_score, $similarList->first()->similarity_score);
    }

    public function test_recommendations_api_endpoints_return_structured_json(): void
    {
        $cat = Category::create(['name' => 'Entertainment', 'slug' => 'entertainment']);

        $channel = Channel::create([
            'name' => 'Galaxy Entertainment',
            'slug' => 'galaxy-entertainment',
            'channel_number' => '404',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_featured' => true,
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channel->categories()->attach($cat->id);

        // 1. Test /api/recommendations
        $resRec = $this->getJson(route('api.recommendations.index'));
        $resRec->assertStatus(200);
        $resRec->assertJsonPath('success', true);
        $resRec->assertJsonStructure([
            'success',
            'count',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'channel_number',
                    'recommendation_score',
                    'recommendation_tag',
                    'recommendation_reason',
                    'categories',
                ],
            ],
        ]);

        // 2. Test /api/recommendations/similar/{slug}
        $resSim = $this->getJson(route('api.recommendations.similar', $channel->slug));
        $resSim->assertStatus(200);
        $resSim->assertJsonPath('success', true);
        $resSim->assertJsonPath('channel.slug', $channel->slug);

        // 3. Test /api/recommendations/guide
        $resGuide = $this->getJson(route('api.recommendations.guide'));
        $resGuide->assertStatus(200);
        $resGuide->assertJsonPath('success', true);
        $resGuide->assertJsonStructure([
            'success',
            'data' => [
                'recommended_channels',
                'favourites',
                'recent_history',
                'stats',
            ],
        ]);
    }

    public function test_personalized_guide_page_renders_with_live_on_air_schedules(): void
    {
        $cat = Category::create(['name' => 'Documentary', 'slug' => 'documentary']);

        $channel = Channel::create([
            'name' => 'African Discovery HD',
            'slug' => 'african-discovery-hd',
            'channel_number' => '505',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_featured' => true,
            'is_active' => true,
            'is_published' => true,
            'is_technically_healthy' => true,
        ]);
        $channel->categories()->attach($cat->id);

        $video = Video::create([
            'title' => 'Serengeti Wonders Live',
            'slug' => 'serengeti-wonders-live',
            'youtube_id' => 'abc123serengeti',
            'duration_seconds' => 3600,
            'is_active' => true,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Serengeti Wonders Live',
            'duration_seconds' => 3600,
            'start_time' => now()->subMinutes(15),
            'end_time' => now()->addMinutes(45),
            'status' => 'playing',
        ]);

        $user = User::factory()->create(['name' => 'Chinedu Eze']);
        UserPreference::create([
            'user_id' => $user->id,
            'country_code' => 'NGA',
            'preferred_categories' => [$cat->id],
            'preferred_languages' => ['en'],
            'enable_recommendations' => true,
        ]);

        $response = $this->actingAs($user)->get(route('guide.personalized'));

        $response->assertStatus(200);
        $response->assertSee("Chinedu Eze's Watch Guide");
        $response->assertSee('African Discovery HD');
        $response->assertSee('Serengeti Wonders Live');
        $response->assertSee('AI Personalisation Engine');
    }
}
