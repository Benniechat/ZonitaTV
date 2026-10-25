<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelStream;
use App\Models\ContentRight;
use App\Models\Programme;
use App\Models\User;
use App\Services\EpgSyncService;
use App\Services\Zonita\ChannelProgrammingEngine;
use App\Services\Zonita\GospelFilmDirectoryService;
use App\Services\Zonita\IslamicPlayoutSchedulingService;
use App\Services\Zonita\NollywoodDirectoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@zonivatv.com'],
            [
                'name' => 'Zoniva Administrator',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
            ]
        );

        // 2. Categories
        $categoriesData = [
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'icon_name' => 'football',
                'meta_title' => 'Watch Football & Sports Live',
                'description' => 'Football coverage, analysis, and verified sports viewing destinations.',
                'sort_order' => 1,
            ],
            [
                'name' => 'News',
                'slug' => 'news',
                'icon_name' => 'newspaper',
                'meta_title' => 'Live News Channels - Nigerian, African and Global News',
                'description' => 'Verified news channels offering live reports and national discourse.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Christian',
                'slug' => 'christian-tv',
                'icon_name' => 'cross',
                'meta_title' => 'Christian Television - Sermons & Worship',
                'description' => 'Inspirational sermons, gospel worship, and Bible teachings.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'icon_name' => 'academic-cap',
                'meta_title' => 'Educational Video Programming & Documentaries',
                'description' => 'Documentaries, STEM lessons, and academic tutorials.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Entertainment',
                'slug' => 'entertainment',
                'icon_name' => 'sparkles',
                'meta_title' => 'General Entertainment, Movies & African Culture',
                'description' => 'Nollywood movies, drama series, and lifestyle shows.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Zoniva Originals',
                'slug' => 'zoniva-originals',
                'icon_name' => 'play',
                'meta_title' => 'Zoniva Originals - Exclusive Productions',
                'description' => 'Original sports discussions and in-depth cultural documentaries.',
                'sort_order' => 6,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::updateOrCreate(['slug' => $c['slug']], $c);
        }

        // 3. Channels matching reference structure & Official Live Broadcasts
        $channelsData = [
            [
                'name' => 'The Matchroom Africa',
                'slug' => 'the-matchroom-africa',
                'tvg_id' => 'MatchroomAfrica.zoniva',
                'tagline' => 'The stories, rivalries and moments shaping the game.',
                'description' => 'Zoniva Original flagship in-house sports panel dissecting African football stars, tactical blueprints, and international coverage.',
                'broadcaster_name' => 'Zoniva Media Studios',
                'broadcaster_website' => 'https://zonivatv.com',
                'official_destination_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'zoniva_original',
                'banner_path' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'is_featured' => true,
                'category_slug' => 'sports',
                'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
            ],
            [
                'name' => 'SportyTV Africa',
                'slug' => 'sportytv-africa',
                'tvg_id' => 'SportyTV.ng',
                'tagline' => 'Live Football, League Analysis and Matchday Broadcasts',
                'description' => 'SportyTV delivers 24/7 live sports coverage, European league fixtures, African matchday analysis, and exclusive footballer interviews.',
                'broadcaster_name' => 'Sporty Group',
                'broadcaster_website' => 'https://sporty.com',
                'official_destination_url' => 'https://www.youtube.com/@sportytvafrica/live',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'embed_player',
                'banner_path' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => 'https://yt3.googleusercontent.com/yF-4zY8h78X_W6nJ6wVp6uG_KzJ09Jb8tQ=s900-c-k-c0x00ffffff-no-rj',
                'is_featured' => true,
                'category_slug' => 'sports',
                'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCwu87p766uwEyzG1p8dEMlg',
            ],
            [
                'name' => 'SportyTV Matchday Live',
                'slug' => 'sportytv-matchday-live',
                'tvg_id' => 'SportyTVMatchday.ng',
                'tagline' => 'Weekend Fixtures and Live Studio Commentary',
                'description' => 'Dedicated SportyTV live matchdesk covering live weekend football, scoreboards, and instant halftime reactions.',
                'broadcaster_name' => 'Sporty Group Media',
                'broadcaster_website' => 'https://sporty.com',
                'official_destination_url' => 'https://www.youtube.com/@sportytvafrica',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'embed_player',
                'banner_path' => 'https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => 'https://yt3.googleusercontent.com/yF-4zY8h78X_W6nJ6wVp6uG_KzJ09Jb8tQ=s900-c-k-c0x00ffffff-no-rj',
                'is_featured' => true,
                'category_slug' => 'sports',
                'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCwu87p766uwEyzG1p8dEMlg',
            ],
            [
                'name' => 'Channels Television',
                'slug' => 'channels-tv',
                'tvg_id' => 'ChannelsTV.ng',
                'tagline' => 'Your Window on the World',
                'description' => 'Nigeria\'s multi-award-winning independent television station broadcasting 24-hour live news, politics, and current affairs.',
                'broadcaster_name' => 'Channels Media Group',
                'broadcaster_website' => 'https://www.channelstv.com',
                'official_destination_url' => 'https://www.channelstv.com/live',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'embed_player',
                'banner_path' => 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => 'https://upload.wikimedia.org/wikipedia/en/thumb/e/ef/Channels_Television_logo.svg/1200px-Channels_Television_logo.svg.png',
                'is_featured' => true,
                'category_slug' => 'news',
                'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCEXGDNclvmg6RW0vipJYsTQ',
            ],
            [
                'name' => 'TVC News Nigeria',
                'slug' => 'tvc-news-nigeria',
                'tvg_id' => 'TVCNews.ng',
                'tagline' => 'Through Nigerian Eyes',
                'description' => '24-hour Nigerian news channel providing in-depth analysis, breaking news, politics, and business across the nation.',
                'broadcaster_name' => 'Continental Broadcasting Service',
                'broadcaster_website' => 'https://tvcnews.tv',
                'official_destination_url' => 'https://tvcnews.tv/live',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'embed_player',
                'banner_path' => 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cd/TVC_News_Logo.png/800px-TVC_News_Logo.png',
                'is_featured' => true,
                'category_slug' => 'news',
                'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCgp4A6I8LCWrhUzn-5SbKvA',
            ],
            [
                'name' => 'NTA News 24',
                'slug' => 'nta-news-24',
                'tvg_id' => 'NTANews24.ng',
                'tagline' => 'You Can\'t Beat the Reach',
                'description' => 'Nigerian Television Authority 24-hour national news service covering public policy, federal governance, and nationwide stories.',
                'broadcaster_name' => 'Nigerian Television Authority',
                'broadcaster_website' => 'https://nta.ng',
                'official_destination_url' => 'https://nta.ng/live',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'embed_player',
                'banner_path' => 'https://images.unsplash.com/photo-1526470608268-f674ce90ebd4?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/NTA_Logo.svg/1200px-NTA_Logo.svg.png',
                'is_featured' => true,
                'category_slug' => 'news',
                'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCwm31ua0VRg_vS9ClI8zlsw',
            ],
            [
                'name' => 'Arise News',
                'slug' => 'arise-news',
                'tvg_id' => 'AriseNews.ng',
                'tagline' => 'The African Perspective',
                'description' => 'Global news channel focusing on African, American and European news, business and cultural discourse.',
                'broadcaster_name' => 'Arise Broadcasting Ltd',
                'broadcaster_website' => 'https://arise.tv',
                'official_destination_url' => 'https://arise.tv/live',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'embed_player',
                'banner_path' => 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => 'https://upload.wikimedia.org/wikipedia/en/thumb/f/f6/Arise_News_Logo.png/220px-Arise_News_Logo.png',
                'is_featured' => true,
                'category_slug' => 'news',
                'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCyEJX-kSj0kOOCS7Qlq2G7g',
            ],
            [
                'name' => 'Dove Television',
                'slug' => 'dove-tv',
                'tvg_id' => 'DoveTV.ng',
                'tagline' => 'Spreading the Gospel Across the World',
                'description' => 'Official broadcast station of the Redeemed Christian Church of God broadcasting worship, holy ghost services, and spiritual teachings.',
                'broadcaster_name' => 'Dove Media',
                'broadcaster_website' => 'https://dovemedia.org',
                'official_destination_url' => 'https://dovemedia.org/live',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'embed_player',
                'banner_path' => 'https://images.unsplash.com/photo-1519491050282-cf00c82424b4?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => 'https://dovemedia.org/wp-content/uploads/2021/04/dove-logo-new.png',
                'is_featured' => true,
                'category_slug' => 'christian-tv',
                'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCbgM7xAXYYB6eX_CipYlTQw',
            ],
            [
                'name' => 'Emmanuel TV',
                'slug' => 'emmanuel-tv',
                'tvg_id' => 'EmmanuelTV.ng',
                'tagline' => 'Changing Lives, Changing Nations, Changing the World',
                'description' => 'International Christian television network broadcasting sermons, testimonies, and charitable outreaches.',
                'broadcaster_name' => 'The Synagogue Church of All Nations',
                'broadcaster_website' => 'https://emmanuel.tv',
                'official_destination_url' => 'https://emmanuel.tv',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'embed_player',
                'banner_path' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => 'https://emmanuel.tv/wp-content/uploads/2021/03/emmanuel-logo.png',
                'is_featured' => true,
                'category_slug' => 'christian-tv',
                'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UC-YEJTkeSsHXSYEuKcbJLGQ',
            ],
            [
                'name' => 'Knowledge TV',
                'slug' => 'knowledge-tv',
                'tvg_id' => 'KnowledgeTV.edu',
                'tagline' => 'Science, History and STEM Learning',
                'description' => 'Educational programming designed for students, educators, and lifelong learners.',
                'broadcaster_name' => 'Knowledge Learning Network',
                'broadcaster_website' => 'https://dw.com',
                'official_destination_url' => 'https://dw.com',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'direct_stream',
                'banner_path' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=800&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'is_featured' => true,
                'category_slug' => 'education',
                'stream_url' => 'https://dwamdstream102.akamaized.net/hls/live/2015525/dwstream102/index.m3u8',
            ],
            [
                'name' => 'Culture Plus',
                'slug' => 'culture-plus',
                'tvg_id' => 'CulturePlus.tv',
                'tagline' => 'African Music, Cinema and Lifestyle',
                'description' => 'Vibrant African culture, Nollywood cinema, festival coverage, and urban entertainment.',
                'broadcaster_name' => 'Culture Plus Media',
                'broadcaster_website' => 'https://zonivatv.com',
                'official_destination_url' => 'https://zonivatv.com',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'direct_stream',
                'banner_path' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=800&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'is_featured' => true,
                'category_slug' => 'entertainment',
                'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
            ],
            [
                'name' => 'World Documentaries',
                'slug' => 'world-documentaries',
                'tvg_id' => 'WorldDocs.tv',
                'tagline' => 'Inspiring Global Human Stories',
                'description' => 'Award-winning investigative documentaries, nature expeditions, and architectural wonders.',
                'broadcaster_name' => 'World Docs Consortium',
                'broadcaster_website' => 'https://dw.com',
                'official_destination_url' => 'https://dw.com',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'playback_mode' => 'direct_stream',
                'banner_path' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=800&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'is_featured' => true,
                'category_slug' => 'education',
                'stream_url' => 'https://dwamdstream102.akamaized.net/hls/live/2015525/dwstream102/index.m3u8',
            ],
        ];

        foreach ($channelsData as $c) {
            $catSlug = $c['category_slug'];
            $streamUrl = $c['stream_url'];
            unset($c['category_slug'], $c['stream_url']);

            $c['is_active'] = true;
            $c['is_published'] = true;
            $c['display_order'] = 1;
            $c['is_technically_healthy'] = true;

            $channel = Channel::updateOrCreate(['slug' => $c['slug']], $c);

            if (isset($categories[$catSlug])) {
                $channel->categories()->sync([$categories[$catSlug]->id]);
            }

            // Stream
            ChannelStream::updateOrCreate(
                ['channel_id' => $channel->id, 'label' => 'Primary Feed'],
                [
                    'stream_type' => 'hls_m3u8',
                    'stream_url' => $streamUrl ?: 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                    'is_active' => true,
                ]
            );

            // Rights
            ContentRight::updateOrCreate(
                ['channel_id' => $channel->id],
                [
                    'rights_holder_name' => $channel->broadcaster_name,
                    'contract_reference' => 'ZONIVA-LIC-'.strtoupper(Str::random(6)),
                    'status' => 'directory_approved',
                    'allow_directory_listing' => true,
                    'allow_web_embedding' => true,
                    'allow_direct_hls' => true,
                    'permitted_territories' => ['GLOBAL'],
                    'effective_from' => now()->subMonth(),
                    'expires_at' => now()->addYear(),
                    'reviewed_by_user_id' => $admin->id,
                    'reviewed_at' => now(),
                    'reviewer_notes' => 'Verified in accordance with Zoniva TV Content Rights Policy.',
                ]
            );
        }

        // 4. Seed Live & Trending Programmes matching reference UI
        $now = now();
        $liveTrendingProgrammes = [
            [
                'channel_slug' => 'sports-central',
                'title' => 'Football Today',
                'category_slug' => 'sports',
                'poster_image' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=600&q=80',
                'description' => 'Matchday analysis, pre-game lineups, and real-time commentary across top African and European leagues.',
                'start_time' => $now->copy()->subMinutes(45),
                'end_time' => $now->copy()->addMinutes(135), // 2h 15m left
                'is_live' => true,
            ],
            [
                'channel_slug' => 'global-news',
                'title' => 'Global News 24',
                'category_slug' => 'news',
                'poster_image' => 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=600&q=80',
                'description' => 'Live rolling news coverage tracking geopolitical updates, economic forums, and breaking stories.',
                'start_time' => $now->copy()->subMinutes(30),
                'end_time' => $now->copy()->addMinutes(45), // 45m left
                'is_live' => true,
            ],
            [
                'channel_slug' => 'faith-channel',
                'title' => 'Sunday Worship Live',
                'category_slug' => 'christian-tv',
                'poster_image' => 'https://images.unsplash.com/photo-1519491050282-cf00c82424b4?auto=format&fit=crop&w=600&q=80',
                'description' => 'Uplifting congregational praise, gospel choir renditions, and inspirational biblical preaching.',
                'start_time' => $now->copy()->subMinutes(30),
                'end_time' => $now->copy()->addMinutes(90), // 1h 30m left
                'is_live' => true,
            ],
            [
                'channel_slug' => 'culture-plus',
                'title' => 'The Weekly Show',
                'category_slug' => 'entertainment',
                'poster_image' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=600&q=80',
                'description' => 'Celebrity guest interviews, comedy sketches, trending music countdowns, and red carpet highlights.',
                'start_time' => $now->copy()->subMinutes(25),
                'end_time' => $now->copy()->addMinutes(35), // 35m left
                'is_live' => true,
            ],
            [
                'channel_slug' => 'knowledge-tv',
                'title' => 'Learn & Grow',
                'category_slug' => 'education',
                'poster_image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=600&q=80',
                'description' => 'Interactive educational STEM lectures, technology insights, and business leadership masterclasses.',
                'start_time' => $now->copy()->subMinutes(60),
                'end_time' => $now->copy()->addMinutes(60), // 1h left
                'is_live' => true,
            ],
        ];

        foreach ($liveTrendingProgrammes as $p) {
            $ch = Channel::where('slug', $p['channel_slug'])->first();
            if ($ch) {
                Programme::updateOrCreate(
                    [
                        'channel_id' => $ch->id,
                        'title' => $p['title'],
                    ],
                    [
                        'category_id' => $categories[$p['category_slug']]->id ?? null,
                        'slug' => Str::slug($p['title'].'-'.$ch->id),
                        'poster_image' => $p['poster_image'],
                        'description' => $p['description'],
                        'start_time' => $p['start_time'],
                        'end_time' => $p['end_time'],
                        'duration_minutes' => $p['start_time']->diffInMinutes($p['end_time']),
                        'is_live' => true,
                    ]
                );
            }
        }

        // 5. Generate continuous EPG for all channels
        app(EpgSyncService::class)->generateDynamicSchedulesForAllChannels();

        // 6. Deploy Zonita 24/7 Virtual Playout Network & Rolling Schedules
        app(ChannelProgrammingEngine::class)->deployPilotNetwork();

        // 7. Deploy 60 Gospel Film Channels, 36 Islamic Broadcasting Channels, 60 Nollywood Channels & 70 Comedy Creators / 10 Virtual Playout Channels
        app(GospelFilmDirectoryService::class)->syncDirectory();
        app(IslamicPlayoutSchedulingService::class)->deployIslamicVirtualNetwork(7);
        app(NollywoodDirectoryService::class)->syncDirectory();
        app(\App\Services\Zonita\ComedyDirectoryService::class)->syncDirectory();
    }
}
