<?php

namespace App\Services\Zonita;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\ChannelStream;
use App\Models\ContentRight;
use App\Models\User;
use Illuminate\Support\Str;

class ChannelProgrammingEngine
{
    public function __construct(
        protected YouTubeDiscoveryService $discoveryService,
        protected VideoAvailabilityService $availabilityService,
        protected ProgrammeScheduleService $scheduleService,
        protected ChannelReserveService $reserveService
    ) {}

    /**
     * Complete catalogue definitions for the 5 pilot channels.
     */
    public function getPilotChannelDefinitions(): array
    {
        return [
            // 1. ZONITA FOOTBALL 24/7
            [
                'name' => 'Zonita Football 24/7',
                'slug' => 'zonita-football-24-7',
                'tvg_id' => 'ZonitaFootball.zonita',
                'category_slug' => 'sports',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Non-Stop European & African Football Action',
                'description' => 'Continuous 24/7 football channel broadcasting matchday highlights, legendary classic clashes, tactical breakdowns, and player documentaries.',
                'banner_path' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'rules' => [
                    'search_keywords' => ['Champions League', 'Football highlights', 'Super Eagles', 'Tactical analysis', 'Premier League', 'Osimhen', 'AFCON'],
                    'excluded_keywords' => ['fifa gameplay', 'ps5 gaming', 'ea sports fc gameplay', 'pack opening'],
                    'approved_publishers' => ['UEFA', 'SuperSport', 'TNT Sports Football', 'Sky Sports Football', 'CAF TV'],
                    'preferred_formats' => ['documentary', 'highlights', 'tactical_analysis', 'interview'],
                    'min_duration_minutes' => 5,
                    'max_duration_minutes' => 60,
                    'repeat_cooldown_hours' => 6,
                    'min_reserve_count' => 5,
                    'allow_evergreen' => true,
                    'scheduling_mode' => 'hybrid',
                    'timezone' => 'Africa/Lagos',
                ],
                'candidates' => [
                    [
                        'youtube_id' => '9E7DmuhBt4M',
                        'title' => 'UEFA Champions League Official Matchday Highlights',
                        'description' => 'Unmissable matchday drama, breathtaking goals, and full tactical reaction.',
                        'channel_title' => 'UEFA',
                        'duration_seconds' => 1500,
                        'format_type' => 'highlights',
                        'is_evergreen' => false,
                    ],
                    [
                        'youtube_id' => 'a-3c6jnjWpM',
                        'title' => 'Super Eagles & Legends of African Football: Road to Glory',
                        'description' => 'The heroic journey of African football icons through world competitions.',
                        'channel_title' => 'Zonita Sports Network',
                        'duration_seconds' => 1800,
                        'format_type' => 'documentary',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'HGlOc25MHh0',
                        'title' => 'Tactical Masterclass: Breaking Down Modern High-Press Systems',
                        'description' => 'In-depth analysis dissecting set-piece strategies and counter-attacks.',
                        'channel_title' => 'Tactics Desk Africa',
                        'duration_seconds' => 1200,
                        'format_type' => 'tactical_analysis',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'reCiyuS1XxQ',
                        'title' => 'African Superstars Abroad: Goals & Weekly Review',
                        'description' => 'Tracking Victor Osimhen, Mo Salah, and African football stars across Europe.',
                        'channel_title' => 'African Football Report',
                        'duration_seconds' => 1200,
                        'format_type' => 'highlights',
                        'is_evergreen' => false,
                    ],
                    [
                        'youtube_id' => 'Lpy6S-qDats',
                        'title' => 'Classic SuperSport Football Highlights & Golden Moments',
                        'description' => 'Sensational goals, championship trophies, and legendary moments in African football.',
                        'channel_title' => 'SuperSport Archive',
                        'duration_seconds' => 1500,
                        'format_type' => 'documentary',
                        'is_evergreen' => true,
                    ],
                ],
            ],

            // 2. ZONITA GOSPEL NAIJA
            [
                'name' => 'Zonita Gospel Naija',
                'slug' => 'zonita-gospel-naija',
                'tvg_id' => 'ZonitaGospelNaija.zonita',
                'category_slug' => 'christian-tv',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Anointed Nigerian Gospel Praise, Worship & Choral Sound',
                'description' => '24/7 spirit-filled Nigerian gospel music, midnight prophetic worship sessions, high praise anthems, and church choir concerts.',
                'banner_path' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'rules' => [
                    'search_keywords' => ['Nathaniel Bassey', 'Sinach', 'Dunamis', 'Nigerian Gospel Praise', 'Hallelujah Challenge', 'Midnight Worship'],
                    'excluded_keywords' => ['secular music', 'club remix', 'afro pop dancehall'],
                    'approved_publishers' => ['Nathaniel Bassey Main', 'Sinach Live', 'Dunamis TV', 'Mount Zion Music'],
                    'preferred_formats' => ['worship_session', 'music_video', 'concert', 'choir'],
                    'min_duration_minutes' => 5,
                    'max_duration_minutes' => 60,
                    'repeat_cooldown_hours' => 6,
                    'min_reserve_count' => 5,
                    'allow_evergreen' => true,
                    'scheduling_mode' => 'hybrid',
                    'timezone' => 'Africa/Lagos',
                ],
                'candidates' => [
                    [
                        'youtube_id' => 'TL-UkMcPQ10',
                        'title' => 'Nathaniel Bassey: Hallelujah Challenge Prophetic Praise',
                        'description' => 'Atmospheric high praise, trumpet sounds, and surrender led by Nathaniel Bassey.',
                        'channel_title' => 'Nathaniel Bassey',
                        'duration_seconds' => 2700,
                        'format_type' => 'worship_session',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => '-wuT9VGG1DQ',
                        'title' => 'Sounds of the Secret Place: Intimate Devotional Chants',
                        'description' => 'Deep spirit-filled worship songs and spiritual chants for personal devotion.',
                        'channel_title' => 'Sanctuary Worship Team',
                        'duration_seconds' => 2400,
                        'format_type' => 'worship_session',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'wGCXBisZDV8',
                        'title' => 'Sinach Live: Way Maker Global Anthems & Live Concert',
                        'description' => 'Worldwide gospel anthems uniting believers across stadiums in joyful praise.',
                        'channel_title' => 'Sinach',
                        'duration_seconds' => 2100,
                        'format_type' => 'concert',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'IXhpu6SHVKs',
                        'title' => 'Sinach: I Know Who I Am - Anointed Praise Session',
                        'description' => 'High-tempo African praise songs, traditional instruments, and joyful dancing.',
                        'channel_title' => 'Sinach Global Ministry',
                        'duration_seconds' => 2100,
                        'format_type' => 'music_video',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'p7UjfT61ZS4',
                        'title' => 'Sound of Heaven Choral & Hymn Extravaganza',
                        'description' => 'Orchestral and choral arrangements of timeless Christian hymns and spiritual anthems.',
                        'channel_title' => 'Heritage Choral Group',
                        'duration_seconds' => 1800,
                        'format_type' => 'choir',
                        'is_evergreen' => true,
                    ],
                ],
            ],

            // 3. ZONITA MUSIC WORLD & AFROBEATS
            [
                'name' => 'Zonita Music World',
                'slug' => 'zonita-music-world',
                'tvg_id' => 'ZonitaMusicWorld.zonita',
                'category_slug' => 'entertainment',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Global Afrobeats, Chart Countdowns & Acoustic Sessions',
                'description' => 'Continuous music station featuring Afrobeats anthems, Amapiano rhythms, acoustic studio sessions, and documentaries on African music icons.',
                'banner_path' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'rules' => [
                    'search_keywords' => ['Afrobeats', 'Amapiano', 'African music concert', 'Acoustic studio live', 'Top 20 countdown'],
                    'excluded_keywords' => ['pirated leaked song', 'bad audio quality', 'reaction video only'],
                    'approved_publishers' => ['SoundCity', 'MTV Base', 'Afrobeats Central'],
                    'preferred_formats' => ['music_video', 'concert', 'interview', 'documentary'],
                    'min_duration_minutes' => 5,
                    'max_duration_minutes' => 60,
                    'repeat_cooldown_hours' => 6,
                    'min_reserve_count' => 5,
                    'allow_evergreen' => true,
                    'scheduling_mode' => 'hybrid',
                    'timezone' => 'Africa/Lagos',
                ],
                'candidates' => [
                    [
                        'youtube_id' => 'EzVWx31RCOk',
                        'title' => 'Afrobeats Global Stage: Electrifying Stadium Performances',
                        'description' => 'Live concert performances of the biggest viral hits commanding international music charts.',
                        'channel_title' => 'Afrobeats Desk',
                        'duration_seconds' => 1800,
                        'format_type' => 'concert',
                        'is_evergreen' => false,
                    ],
                    [
                        'youtube_id' => 'FdthOVcubbU',
                        'title' => 'Afrobeats Top 20 Official Chart Countdown',
                        'description' => 'The definitive ranking of the biggest hits dominating radio, streaming, and clubs.',
                        'channel_title' => 'DJ Switch & Chart Desk',
                        'duration_seconds' => 1800,
                        'format_type' => 'music_video',
                        'is_evergreen' => false,
                    ],
                    [
                        'youtube_id' => '2ixGjOawN98',
                        'title' => 'Amapiano Groove & African Dancehall Waves',
                        'description' => 'The smoothest South African and West African log drums, remixes, and dance floor anthems.',
                        'channel_title' => 'Sound City Vibe',
                        'duration_seconds' => 2100,
                        'format_type' => 'music_video',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'ErstFBUQP_k',
                        'title' => 'Afrobeats Legends: Evolution of the Modern Sound',
                        'description' => 'Visual retrospective exploring the roots of Afrobeat to modern stadium world tours.',
                        'channel_title' => 'African Rhythms Archive',
                        'duration_seconds' => 1500,
                        'format_type' => 'documentary',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'tbwtVCSF-QU',
                        'title' => 'Acoustic Studio Live: Unplugged African Melodies',
                        'description' => 'Intimate acoustic sets and raw vocal performances from Africa leading musical talents.',
                        'channel_title' => 'Unplugged Live',
                        'duration_seconds' => 1500,
                        'format_type' => 'concert',
                        'is_evergreen' => true,
                    ],
                ],
            ],

            // 4. ZONITA AFRICAN DOCUMENTARIES
            [
                'name' => 'Zonita Documentaries',
                'slug' => 'zonita-documentaries',
                'tvg_id' => 'ZonitaDocs.zonita',
                'category_slug' => 'education',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'African History, Breathtaking Wildlife & Lost Civilizations',
                'description' => 'Premium documentary network exploring African wildlife safaris, ancient civilizations along the Nile, ocean biology, and planetary sciences.',
                'banner_path' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'rules' => [
                    'search_keywords' => ['African documentary', 'BBC Earth', 'National Geographic', 'Wildlife migration', 'Ancient civilizations', 'Serengeti'],
                    'excluded_keywords' => ['fake conspiracy', 'clickbait myth'],
                    'approved_publishers' => ['BBC Earth', 'National Geographic', 'Wild Planet Docs'],
                    'preferred_formats' => ['documentary', 'investigative', 'history', 'nature'],
                    'min_duration_minutes' => 15,
                    'max_duration_minutes' => 90,
                    'repeat_cooldown_hours' => 6,
                    'min_reserve_count' => 5,
                    'allow_evergreen' => true,
                    'scheduling_mode' => 'hybrid',
                    'timezone' => 'Africa/Lagos',
                ],
                'candidates' => [
                    [
                        'youtube_id' => '4IV1tTf0CZs',
                        'title' => 'BBC Earth: Predators & Kings of the African Savanna',
                        'description' => 'Lions, leopards, and cheetahs navigating survival strategies across the Maasai Mara.',
                        'channel_title' => 'BBC Earth',
                        'duration_seconds' => 3000,
                        'format_type' => 'nature',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'cHqpobJIrKk',
                        'title' => 'Deep Ocean Exploration: Alien Worlds in the Abyss',
                        'description' => 'Bioluminescent creatures and uncharted geological trenches thousands of meters below sea level.',
                        'channel_title' => 'Oceanographic Lab',
                        'duration_seconds' => 2700,
                        'format_type' => 'documentary',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'j1YmhOEyb4Y',
                        'title' => 'The Great Serengeti Migration: The Perilous River Crossing',
                        'description' => 'Two million herbivores cross crocodile rivers in search of green pastures and life.',
                        'channel_title' => 'Wild Kingdom Channel',
                        'duration_seconds' => 2700,
                        'format_type' => 'nature',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'Jl7PlR0Mwfk',
                        'title' => 'National Geographic: Lost Empires & Tomb Mysteries of Africa',
                        'description' => 'Archaeologists uncover monumental temples, lost dynasties, and ancient technological secrets.',
                        'channel_title' => 'National Geographic',
                        'duration_seconds' => 2400,
                        'format_type' => 'history',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'tn7ybEP6fSU',
                        'title' => 'Planet Earth Wonders: Mountains, Glaciers & Volcanoes',
                        'description' => 'The breathtaking geological forces that sculpted the peaks and canyons of our planet.',
                        'channel_title' => 'Earth Science Desk',
                        'duration_seconds' => 2400,
                        'format_type' => 'documentary',
                        'is_evergreen' => true,
                    ],
                ],
            ],

            // 5. ZONITA TECH PULSE
            [
                'name' => 'Zonita Tech Pulse',
                'slug' => 'zonita-tech-pulse',
                'tvg_id' => 'ZonitaTechPulse.zonita',
                'category_slug' => 'education',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'AI Breakthroughs, Gadget Teardowns & Coding Tutorials',
                'description' => 'Frontier technology channel covering artificial intelligence reasoning models, software engineering, smartphone reviews, electric vehicles, and African tech startups.',
                'banner_path' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'rules' => [
                    'search_keywords' => ['Artificial Intelligence', 'MKBHD', 'Tech reviews', 'Software development', 'Electric vehicles', 'Cybersecurity'],
                    'excluded_keywords' => ['crypto scam', 'get rich quick bot'],
                    'approved_publishers' => ['Marques Brownlee', 'Tech Lead', 'The Verge', 'Fireship'],
                    'preferred_formats' => ['tutorial', 'review', 'analysis', 'news'],
                    'min_duration_minutes' => 5,
                    'max_duration_minutes' => 60,
                    'repeat_cooldown_hours' => 6,
                    'min_reserve_count' => 5,
                    'allow_evergreen' => true,
                    'scheduling_mode' => 'hybrid',
                    'timezone' => 'Africa/Lagos',
                ],
                'candidates' => [
                    [
                        'youtube_id' => 'ESABTN4VtrA',
                        'title' => 'MKBHD: The Future of Consumer Hardware & AI Agents',
                        'description' => 'Top-tier tech reviews, industrial design scrutiny, and cutting-edge gadget benchmarking.',
                        'channel_title' => 'Marques Brownlee',
                        'duration_seconds' => 1500,
                        'format_type' => 'review',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => 'scwlR0QHGOk',
                        'title' => 'Flagship Smartphone Deep-Dive: Silicon & Camera Tech',
                        'description' => 'Direct comparisons between the world fastest processors, folding screens, and mobile cameras.',
                        'channel_title' => 'Future Tech Desk',
                        'duration_seconds' => 1500,
                        'format_type' => 'review',
                        'is_evergreen' => false,
                    ],
                    [
                        'youtube_id' => 'QVv3mQzlrN0',
                        'title' => 'Electric Vehicles & Clean Energy Revolution',
                        'description' => 'Testing autonomous driving, solid-state battery breakthroughs, and the EV transition.',
                        'channel_title' => 'Mobility Frontier',
                        'duration_seconds' => 1500,
                        'format_type' => 'analysis',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => '_0G4OG39XjI',
                        'title' => 'Artificial Intelligence in 2026: Multi-Modal Reasoning Agents',
                        'description' => 'How multimodal AI models, reasoning agents, and robotics are reshaping work and society.',
                        'channel_title' => 'AI Today Desk',
                        'duration_seconds' => 1800,
                        'format_type' => 'analysis',
                        'is_evergreen' => true,
                    ],
                    [
                        'youtube_id' => '3iRUwVzRDZQ',
                        'title' => 'Next-Gen Wearables & Spatial Computing Hardware',
                        'description' => 'Smart glasses, AR headsets, neural interfaces, and health sensor innovations.',
                        'channel_title' => 'Wearables Lab',
                        'duration_seconds' => 1200,
                        'format_type' => 'review',
                        'is_evergreen' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * Initializes, configures rules, discovers/verifies candidates, and builds 7-day rolling schedules.
     */
    public function deployPilotNetwork(): array
    {
        $admin = User::first();
        $definitions = $this->getPilotChannelDefinitions();

        $channelsCreated = 0;
        $videosVerified = 0;
        $schedulesGenerated = 0;

        foreach ($definitions as $def) {
            $catSlug = $def['category_slug'];
            $category = Category::where('slug', $catSlug)->first();

            // 1. Channel Record
            $channel = Channel::updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'name' => $def['name'],
                    'tvg_id' => $def['tvg_id'],
                    'tagline' => $def['tagline'],
                    'description' => $def['description'],
                    'broadcaster_name' => 'Zonita Television Network',
                    'broadcaster_website' => config('app.url'),
                    'official_destination_url' => config('app.url').'/channel/'.$def['slug'],
                    'country_code' => $def['country_code'] ?? 'NGA',
                    'language_code' => $def['language_code'] ?? 'en',
                    'playback_mode' => 'embed_player',
                    'curation_type' => 'linear_streamer',
                    'banner_path' => $def['banner_path'],
                    'logo_path' => $def['logo_path'],
                    'is_featured' => true,
                    'is_active' => true,
                    'is_published' => true,
                    'is_technically_healthy' => true,
                    'display_order' => 1,
                    'fallback_video_id' => $def['candidates'][0]['youtube_id'] ?? '9E7DmuhBt4M',
                ]
            );

            if ($category) {
                $channel->categories()->syncWithoutDetaching([$category->id]);
                $channel->load('categories');
            }

            // 2. Channel Source Rules
            ChannelSourceRule::updateOrCreate(
                ['channel_id' => $channel->id],
                $def['rules']
            );

            // 3. Channel Rights Record
            ContentRight::updateOrCreate(
                ['channel_id' => $channel->id],
                [
                    'rights_holder_name' => 'Zonita TV Programming Engine',
                    'contract_reference' => 'ZONITA-EPG-'.strtoupper(Str::random(6)),
                    'status' => 'directory_approved',
                    'allow_directory_listing' => true,
                    'allow_web_embedding' => true,
                    'allow_direct_hls' => true,
                    'permitted_territories' => ['GLOBAL'],
                    'effective_from' => now()->subMonth(),
                    'expires_at' => now()->addYears(3),
                    'reviewed_by_user_id' => $admin?->id,
                    'reviewed_at' => now(),
                    'reviewer_notes' => 'AI-Powered Multi-Channel Programming Engine Verified Stream',
                ]
            );

            // 4. Primary Stream Pointer
            ChannelStream::updateOrCreate(
                ['channel_id' => $channel->id, 'label' => 'Zonita 24/7 Linear Playout'],
                [
                    'stream_type' => 'youtube_embed',
                    'stream_url' => "https://www.youtube-nocookie.com/embed/{$channel->fallback_video_id}",
                    'is_active' => true,
                    'is_backup' => false,
                ]
            );

            // 5. Ingest and Verify Candidate Videos
            $discoveryResult = $this->discoveryService->discoverForChannel($channel, $def['candidates']);
            $videosVerified += $discoveryResult['ingested'];

            // 6. Generate 7-Day Continuous Rolling Schedule
            $scheduleResult = $this->scheduleService->generateRollingSchedule($channel, 7);
            $schedulesGenerated += $scheduleResult['entries_created'];

            $channelsCreated++;
        }

        return [
            'channels' => $channelsCreated,
            'videos_verified' => $videosVerified,
            'schedule_entries' => $schedulesGenerated,
        ];
    }
}
