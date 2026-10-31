<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ComedyChannel;
use App\Models\GospelFilmProduction;
use App\Models\IslamicContentProvider;
use App\Models\NollywoodChannel;
use App\Models\ScheduleEntry;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerticalPortalsAndShowcasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_comedy_portal_renders_virtual_channels_and_creator_directory(): void
    {
        $creator = ComedyChannel::create([
            'name' => 'Broda Shaggi Comedy World',
            'channel_category' => 'skit_creators',
            'creator_or_brand' => 'Broda Shaggi',
            'country_code' => 'NGA',
            'primary_language' => 'English, Pidgin',
            'lead_priority' => 1,
            'catalogue_size_est' => 250,
            'licensing_status' => 'active_broadcasting',
        ]);

        $virtualChannel = Channel::create([
            'name' => 'Zonita Comedy Central',
            'slug' => 'zonita-comedy-central',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-comedy-central',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $response = $this->get(route('comedy.index'));
        $response->assertStatus(200);
        $response->assertSee('Broda Shaggi Comedy World');
        $response->assertSee('Broda Shaggi');

        // Test alias route /comedy-central
        $aliasResponse = $this->get('/comedy-central');
        $aliasResponse->assertStatus(200);
        $aliasResponse->assertSee('Broda Shaggi Comedy World');
    }

    public function test_comedy_portal_filtering_by_category_and_search(): void
    {
        ComedyChannel::create([
            'name' => 'Basketmouth Standup Special',
            'channel_category' => 'standup_comedy',
            'creator_or_brand' => 'Basketmouth',
            'country_code' => 'NGA',
            'primary_language' => 'English',
            'lead_priority' => 1,
        ]);

        ComedyChannel::create([
            'name' => 'Taaooma House of Laughs',
            'channel_category' => 'skit_creators',
            'creator_or_brand' => 'Taaooma',
            'country_code' => 'NGA',
            'primary_language' => 'Yoruba, English',
            'lead_priority' => 2,
        ]);

        // Category filter
        $catResponse = $this->get('/comedy?category=standup_comedy');
        $catResponse->assertStatus(200);
        $catResponse->assertSee('Basketmouth Standup Special');
        $catResponse->assertDontSee('Taaooma House of Laughs');

        // Text search
        $searchResponse = $this->get('/comedy?q=Taaooma');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Taaooma House of Laughs');
        $searchResponse->assertDontSee('Basketmouth Standup Special');
    }

    public function test_nollywood_portal_renders_movie_studios_and_aggregators(): void
    {
        NollywoodChannel::create([
            'name' => 'Ruth Kadiri 24/7 Films',
            'channel_category' => 'aggregator_library',
            'creator_or_lead' => 'Ruth Kadiri',
            'country_code' => 'NGA',
            'primary_language' => 'English',
            'lead_priority' => 1,
            'catalogue_size_est' => 150,
            'has_full_length_movies' => true,
            'licensing_status' => 'active_broadcasting',
        ]);

        $cat = Category::create(['name' => 'Movies', 'slug' => 'movies']);

        $channel = Channel::create([
            'name' => 'Ruth Kadiri 247',
            'slug' => 'ruth-kadiri-247',
            'official_destination_url' => 'https://zonitatv.com/channel/ruth-kadiri-247',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);
        $channel->categories()->attach($cat->id);

        $response = $this->get(route('nollywood.index'));
        $response->assertStatus(200);
        $response->assertSee('Ruth Kadiri 24/7 Films');
        $response->assertSee('Ruth Kadiri');

        // Test alias route /nollywood-cinema
        $aliasResponse = $this->get('/nollywood-cinema');
        $aliasResponse->assertStatus(200);
        $aliasResponse->assertSee('Ruth Kadiri 24/7 Films');
    }

    public function test_nollywood_portal_filtering_and_priority_sorting(): void
    {
        NollywoodChannel::create([
            'name' => 'Apata TV Yoruba Cinema',
            'channel_category' => 'yoruba_movies',
            'creator_or_lead' => 'Apata Media',
            'country_code' => 'NGA',
            'primary_language' => 'Yoruba',
            'lead_priority' => 1,
        ]);

        NollywoodChannel::create([
            'name' => 'Royal Arts Academy Films',
            'channel_category' => 'production_studios',
            'creator_or_lead' => 'Emem Isong',
            'country_code' => 'NGA',
            'primary_language' => 'English',
            'lead_priority' => 2,
        ]);

        // Filter by language
        $langResponse = $this->get('/nollywood?lang=Yoruba');
        $langResponse->assertStatus(200);
        $langResponse->assertSee('Apata TV Yoruba Cinema');
        $langResponse->assertDontSee('Royal Arts Academy Films');
    }

    public function test_gospel_and_faith_portal_renders_ministries_and_linear_channels(): void
    {
        GospelFilmProduction::create([
            'name' => 'Mount Zion Faith Ministries',
            'founder_or_minister' => 'Mike Bamiloye',
            'ministry_type' => 'film_production',
            'country_code' => 'NGA',
            'headquarters_city' => 'Ibadan',
            'primary_language' => 'English, Yoruba',
            'lead_priority' => 1,
            'catalogue_size_est' => 120,
            'has_full_length_movies' => true,
            'licensing_status' => 'active_broadcasting',
        ]);

        $cat = Category::create(['name' => 'Christian TV', 'slug' => 'christian-tv']);

        $channel = Channel::create([
            'name' => 'Mount Zion Gospel Drama TV',
            'slug' => 'mount-zion-gospel-drama-tv',
            'official_destination_url' => 'https://zonitatv.com/channel/mount-zion-gospel-drama-tv',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);
        $channel->categories()->attach($cat->id);

        // 1. Primary route /gospel-cinema
        $response = $this->get(route('gospel.index'));
        $response->assertStatus(200);
        $response->assertSee('Mount Zion Faith Ministries');
        $response->assertSee('Mike Bamiloye');

        // 2. Alias route /faith
        $faithResponse = $this->get(route('faith.index'));
        $faithResponse->assertStatus(200);
        $faithResponse->assertSee('Mount Zion Faith Ministries');
    }

    public function test_islamic_broadcasting_portal_renders_channels_and_schedule_previews(): void
    {
        IslamicContentProvider::create([
            'name' => 'Arewa Islamic Media Trust',
            'provider_type' => 'MEDIA_NETWORK',
            'primary_language' => 'Hausa, Arabic',
            'confidence_score' => 95,
            'licensing_status' => 'ACTIVE_BROADCASTING',
            'verification_status' => 'VERIFIED_OWNER',
        ]);

        $cat = Category::create(['name' => 'Islamic', 'slug' => 'islamic']);

        $channel = Channel::create([
            'name' => 'Zonita Quran TV',
            'slug' => 'zonita-quran',
            'official_destination_url' => 'https://zonitatv.com/channel/zonita-quran',
            'country_code' => 'NGA',
            'language_code' => 'ar',
            'playback_mode' => 'zoniva_original',
            'is_virtual' => true,
            'is_active' => true,
            'is_published' => true,
        ]);
        $channel->categories()->attach($cat->id);

        $video = Video::create([
            'youtube_id' => 'quran_recitation_live',
            'title' => 'Noble Quran Tilawat: Surah Al-Baqarah',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
            'is_embeddable' => true,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Noble Quran Tilawat: Surah Al-Baqarah',
            'format_type' => 'feature',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(50),
            'duration_seconds' => 3600,
            'status' => 'playing',
        ]);

        $response = $this->get(route('islamic.index'));
        $response->assertStatus(200);
        $response->assertSee('Arewa Islamic Media Trust');
        $response->assertSee('Zonita Quran TV');
        $response->assertSee('Noble Quran Tilawat: Surah Al-Baqarah');
    }
}
