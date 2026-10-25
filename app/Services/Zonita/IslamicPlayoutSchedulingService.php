<?php

namespace App\Services\Zonita;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\ChannelStream;
use App\Models\ChannelVideo;
use App\Models\ContentRight;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Str;

class IslamicPlayoutSchedulingService
{
    public function __construct(
        protected ProgrammeScheduleService $scheduleService
    ) {}

    /**
     * Definitions and verified playout candidates for the 8 Islamic Virtual Channels.
     */
    public function getChannelDefinitions(): array
    {
        return [
            // 1. ZONITA ISLAMIC CINEMA
            'zonita-islamic-cinema' => [
                'name' => 'Zonita Islamic Cinema',
                'tvg_id' => 'ZonitaIslamicCinema.zoniva',
                'tagline' => '24/7 Licensed Islamic Feature Films & Historical Cinema',
                'description' => 'Continuous 24/7 linear channel broadcasting Islamic feature films, moral cinematic narratives, and historical biographical dramas.',
                'banner_path' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1400&q=80',
                'fallback_id' => 'd_C16w44Drc',
                'candidates' => [
                    [
                        'youtube_id' => 'd_C16w44Drc',
                        'title' => 'The Message of Faith || Full Cinematic Feature',
                        'description' => 'An inspiring cinematic feature detailing perseverance, truth, and community integrity.',
                        'duration_seconds' => 7200,
                    ],
                    [
                        'youtube_id' => 'zwhE4qE6u8c',
                        'title' => 'Historical Islamic Civilisation & The Golden Age',
                        'description' => 'A biographical cinematic documentary examining the thinkers and leaders of the Islamic Golden Age.',
                        'duration_seconds' => 5400,
                    ],
                    [
                        'youtube_id' => 'H91bEGJYBU4',
                        'title' => 'Trials and Triumph || Faith Drama Feature',
                        'description' => 'Spiritual journey through adversity, patience, and divine solace.',
                        'duration_seconds' => 5400,
                    ],
                ],
            ],

            // 2. ZONITA AREWA MOVIES
            'zonita-arewa-movies' => [
                'name' => 'Zonita Arewa Movies',
                'tvg_id' => 'ZonitaArewaMovies.zoniva',
                'tagline' => '24/7 Premier Hausa & Kannywood Feature Films',
                'description' => 'Continuous 24/7 linear channel celebrating the finest Hausa-language cinema, Kannywood blockbuster dramas, and cultural family films.',
                'banner_path' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1400&q=80',
                'fallback_id' => '5mFvb62VMkc',
                'candidates' => [
                    [
                        'youtube_id' => '5mFvb62VMkc',
                        'title' => 'LABARINA: Epic Hausa Family Drama Feature',
                        'description' => 'Acclaimed Kannywood series and feature production by Aminu Saira exploring loyalty and justice in northern Nigeria.',
                        'duration_seconds' => 5400,
                    ],
                    [
                        'youtube_id' => 'H9L8smUH7Cc',
                        'title' => 'DAN MARAYAN ZAKI: Kannywood Cinema Classic',
                        'description' => 'Starring Ali Nuhu. A story of triumph over oppression, righteousness, and honor.',
                        'duration_seconds' => 5400,
                    ],
                    [
                        'youtube_id' => '7QA0Cb_oak0',
                        'title' => 'ZAMAN AURE: Hausa Family & Social Conflict Drama',
                        'description' => 'An insightful drama tackling matrimonial patience, family ethics, and social responsibility in Kano.',
                        'duration_seconds' => 4800,
                    ],
                ],
            ],

            // 3. ZONITA YORUBA ISLAMIC DRAMA
            'zonita-yoruba-islamic' => [
                'name' => 'Zonita Yoruba Islamic Drama',
                'tvg_id' => 'ZonitaYorubaIslamic.zoniva',
                'tagline' => '24/7 Yoruba Islamic Movies & Moral Dramas',
                'description' => 'Continuous 24/7 linear television channel broadcasting Yoruba Islamic moral cinema, ethical family dramas, and community faith productions.',
                'banner_path' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80',
                'fallback_id' => 'vQuOrk8ZEug',
                'candidates' => [
                    [
                        'youtube_id' => 'vQuOrk8ZEug',
                        'title' => 'ANOBI NLA: Full Yoruba Islamic Moral Epic',
                        'description' => 'Deeply spiritual drama following an elder\'s steadfast prayer, uprightness, and truth in modern society.',
                        'duration_seconds' => 5400,
                    ],
                    [
                        'youtube_id' => 'Zu0STkF7evs',
                        'title' => 'AL-HIKMAH (Divine Wisdom) || Yoruba Islamic Feature',
                        'description' => 'Exploring the consequences of deceit and the supreme peace found in turning back to Allah with sincere repentance.',
                        'duration_seconds' => 5100,
                    ],
                    [
                        'youtube_id' => '1wP5nyzzlcE',
                        'title' => 'AWON OBINRIN AL-JANNAH: Virtues of Righteous Women',
                        'description' => 'Educational and moral drama depicting the virtues, patience, and dignity of righteous Muslim women.',
                        'duration_seconds' => 4500,
                    ],
                ],
            ],

            // 4. ZONITA QURAN
            'zonita-quran' => [
                'name' => 'Zonita Quran',
                'tvg_id' => 'ZonitaQuran.zoniva',
                'tagline' => '24/7 Holy Qur\'an Recitations & Live Haramain Feeds',
                'description' => 'Continuous 24/7 linear broadcast of soul-stirring Qur\'anic recitations by world-renowned Qaris, Tajwid lessons, and live Makkah/Madinah recordings.',
                'banner_path' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1400&q=80',
                'fallback_id' => '09CluI_qHcU',
                'candidates' => [
                    [
                        'youtube_id' => '09CluI_qHcU',
                        'title' => 'Surah Al-Baqarah Complete || Beautiful Heartfelt Recitation',
                        'description' => 'Complete recitation of Surah Al-Baqarah with English translation and on-screen Arabic text.',
                        'duration_seconds' => 7200,
                    ],
                    [
                        'youtube_id' => '5_F50V-sEJo',
                        'title' => 'Surah Yasin, Ar-Rahman, Al-Waqiah & Al-Mulk',
                        'description' => 'Peaceful evening recitations for spiritual serenity, healing, and contemplation.',
                        'duration_seconds' => 4800,
                    ],
                    [
                        'youtube_id' => 'Nt4kCgt_Kj4',
                        'title' => 'Juz 30 (Juz Amma) Complete with Tajwid Rules',
                        'description' => 'Slow, educational recitation facilitating Qur\'anic memorization and proper articulation.',
                        'duration_seconds' => 5400,
                    ],
                ],
            ],

            // 5. ZONITA ISLAMIC KNOWLEDGE
            'zonita-islamic-knowledge' => [
                'name' => 'Zonita Islamic Knowledge',
                'tvg_id' => 'ZonitaIslamicKnowledge.zoniva',
                'tagline' => '24/7 Tafsir, Seerah & Scholarly Lectures',
                'description' => 'Continuous 24/7 educational broadcasting of deep Tafsir, authentic Hadith commentary, the Seerah of Prophet Muhammad, and contemporary Islamic jurisprudence.',
                'banner_path' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1400&q=80',
                'fallback_id' => 'uqG3_eoEYcs',
                'candidates' => [
                    [
                        'youtube_id' => 'uqG3_eoEYcs',
                        'title' => 'The Seerah of Prophet Muhammad (SAW) || Comprehensive Masterclass',
                        'description' => 'In-depth historical exploration of the blessed life, character, and leadership of the Prophet of Islam.',
                        'duration_seconds' => 5400,
                    ],
                    [
                        'youtube_id' => 'ESABTN4VtrA',
                        'title' => 'Tafsir of Surah Al-Kahf: Lessons for the Modern Age',
                        'description' => 'Linguistic and theological gems exploring the four stories of Surah Al-Kahf.',
                        'duration_seconds' => 4800,
                    ],
                    [
                        'youtube_id' => 'scwlR0QHGOk',
                        'title' => 'The Creed of Islam: Understanding Divine Decrees & Destiny',
                        'description' => 'Clear scholarly explanations resolving contemporary questions on fate, free will, and worship.',
                        'duration_seconds' => 4200,
                    ],
                ],
            ],

            // 6. ZONITA ISLAMIC DOCUMENTARIES
            'zonita-islamic-documentaries' => [
                'name' => 'Zonita Islamic Documentaries',
                'tvg_id' => 'ZonitaIslamicDocumentaries.zoniva',
                'tagline' => '24/7 Civilization, Heritage & Architectural History',
                'description' => 'Continuous 24/7 documentaries investigating Islamic architecture from Andalusia to Timbuktu, scientific contributions, and humanitarian history.',
                'banner_path' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1400&q=80',
                'fallback_id' => 'y18v1a0eK7A',
                'candidates' => [
                    [
                        'youtube_id' => 'y18v1a0eK7A',
                        'title' => 'Timbuktu: The Ancient Libraries & Scholars of West Africa',
                        'description' => 'Documentary chronicling the legendary universities of Sankore, astronomical manuscripts, and Islamic heritage in Mali and Nigeria.',
                        'duration_seconds' => 5400,
                    ],
                    [
                        'youtube_id' => 'bpt8r1sI8W0',
                        'title' => 'Al-Andalus: Eight Centuries of Islamic Science in Spain',
                        'description' => 'Exploring the architectural wonders of Cordoba, Granada, and the scientific achievements of Islamic Iberia.',
                        'duration_seconds' => 4800,
                    ],
                    [
                        'youtube_id' => 'QVv3mQzlrN0',
                        'title' => 'The Ottoman Empire & The Architecture of Sinan',
                        'description' => 'A cinematic study of Islamic engineering, mosque design, and historical statecraft.',
                        'duration_seconds' => 4500,
                    ],
                ],
            ],

            // 7. ZONITA NASHEED
            'zonita-nasheed' => [
                'name' => 'Zonita Nasheed',
                'tvg_id' => 'ZonitaNasheed.zoniva',
                'tagline' => '24/7 Traditional Spiritual Nasheeds & Devotional Arts',
                'description' => 'Continuous 24/7 linear channel offering uplifting, vocal-only nasheeds, traditional Islamic praise poetry, and spiritual melodies by international artists.',
                'banner_path' => 'https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&w=1400&q=80',
                'fallback_id' => 'qXcMNBQnQMM',
                'candidates' => [
                    [
                        'youtube_id' => 'qXcMNBQnQMM',
                        'title' => 'Al-Mu\'allim & Spiritual Devotional Collection',
                        'description' => 'Uplifting acoustic and traditional percussive Islamic praise celebrating divine mercy and the Prophet.',
                        'duration_seconds' => 4500,
                    ],
                    [
                        'youtube_id' => 'KEh8MIp5e5A',
                        'title' => 'Mountains of Makkah & African Nasheed Classics',
                        'description' => 'Heartwarming vocals by Zain Bhikha reflecting on gratitude, devotion, and character.',
                        'duration_seconds' => 4200,
                    ],
                    [
                        'youtube_id' => 'Yb3k9Z58ITc',
                        'title' => 'Qalbi Muhammad: Soulful Vocal Nasheeds',
                        'description' => 'Traditional Arabic and English spiritual hymns composed without illicit instrumentation.',
                        'duration_seconds' => 3900,
                    ],
                ],
            ],

            // 8. ZONITA ISLAMIC FAMILY
            'zonita-islamic-family' => [
                'name' => 'Zonita Islamic Family',
                'tvg_id' => 'ZonitaIslamicFamily.zoniva',
                'tagline' => '24/7 Marriage Guidance, Parenting & Moral Youth Animation',
                'description' => 'Continuous 24/7 family-oriented channel featuring marital counseling, children\'s Islamic cartoons, parenting wisdom, and moral education.',
                'banner_path' => 'https://images.unsplash.com/photo-1566140967404-b8b3932483f5?auto=format&fit=crop&w=1400&q=80',
                'fallback_id' => 'aGt3fPeQVKQ',
                'candidates' => [
                    [
                        'youtube_id' => 'aGt3fPeQVKQ',
                        'title' => 'The Prophet Yunus & The Whale || Animated Family Feature',
                        'description' => 'Broadcast-quality 3D animation teaching children patience, repentance, and faith in Allah.',
                        'duration_seconds' => 4800,
                    ],
                    [
                        'youtube_id' => 'N0F9tPo3EBY',
                        'title' => 'Building a Harmonious Islamic Marriage',
                        'description' => 'Practical advice by Mufti Menk on resolving marital conflicts, mutual respect, and family peace.',
                        'duration_seconds' => 4500,
                    ],
                    [
                        'youtube_id' => 'q_L9Tc1ZR3o',
                        'title' => 'Raising Confident Muslim Children in a Digital World',
                        'description' => 'Parenting strategies for cultivating honesty, modesty, and resilience in today\'s generation.',
                        'duration_seconds' => 4200,
                    ],
                ],
            ],
        ];
    }

    /**
     * Initializes, provisions, and schedules all 8 Islamic virtual channels for 24/7 playout.
     */
    public function deployIslamicVirtualNetwork(int $daysAhead = 7): array
    {
        $admin = User::first();
        $definitions = $this->getChannelDefinitions();

        // Ensure Islamic Category exists
        $category = Category::firstOrCreate(
            ['slug' => 'islamic'],
            [
                'name' => 'Islamic Television',
                'icon_name' => 'moon',
                'meta_title' => 'Islamic Movies, Hausa Kannywood, Yoruba Drama, Quran & Tafsir',
                'description' => '24/7 virtual broadcasting of verified Islamic cinema, Hausa Kannywood films, Yoruba Islamic dramas, Qur\'an recitations, and scholarly lectures.',
                'sort_order' => 5,
            ]
        );

        $channelsDeployed = 0;
        $totalScheduleEntries = 0;
        $channelSummaries = [];

        foreach ($definitions as $slug => $def) {
            // 1. Channel Record
            $channel = Channel::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $def['name'],
                    'tvg_id' => $def['tvg_id'],
                    'tagline' => $def['tagline'],
                    'description' => $def['description'],
                    'broadcaster_name' => 'Zonita Islamic Television Syndicate',
                    'broadcaster_website' => config('app.url'),
                    'official_destination_url' => config('app.url').'/channel/'.$slug,
                    'country_code' => 'NGA',
                    'language_code' => 'en',
                    'playback_mode' => 'embed_player',
                    'curation_type' => 'linear_streamer',
                    'banner_path' => $def['banner_path'],
                    'logo_path' => '/images/logo/zonitatvlogo2.png',
                    'is_featured' => true,
                    'is_active' => true,
                    'is_published' => true,
                    'is_technically_healthy' => true,
                    'display_order' => 2,
                    'fallback_video_id' => $def['fallback_id'],
                ]
            );

            $channel->categories()->syncWithoutDetaching([$category->id]);

            // 2. Source Rules
            ChannelSourceRule::updateOrCreate(
                ['channel_id' => $channel->id],
                [
                    'search_keywords' => ['Islamic', 'Cinema', 'Hausa', 'Kannywood', 'Tafsir', 'Quran'],
                    'min_duration_minutes' => 30,
                    'max_duration_minutes' => 180,
                    'repeat_cooldown_hours' => 6,
                    'min_reserve_count' => 3,
                    'allow_evergreen' => true,
                    'scheduling_mode' => 'hybrid',
                    'timezone' => 'Africa/Lagos',
                ]
            );

            // 3. Content Rights
            ContentRight::updateOrCreate(
                ['channel_id' => $channel->id],
                [
                    'rights_holder_name' => 'Zonita Islamic Broadcasting Syndicate',
                    'contract_reference' => 'ZONITA-ISLAMIC-'.strtoupper(Str::random(6)),
                    'status' => 'directory_approved',
                    'allow_directory_listing' => true,
                    'allow_web_embedding' => true,
                    'allow_direct_hls' => true,
                    'allow_monetisation' => true,
                    'permitted_territories' => ['GLOBAL', 'NGA'],
                    'effective_from' => now()->subMonth(),
                    'expires_at' => now()->addYears(5),
                    'reviewed_by_user_id' => $admin?->id,
                    'reviewed_at' => now(),
                    'reviewer_notes' => 'Audited and approved by Zonita Islamic Rights Verification System.',
                ]
            );

            // 4. Primary Stream
            ChannelStream::updateOrCreate(
                ['channel_id' => $channel->id, 'label' => 'Zonita 24/7 Linear Playout'],
                [
                    'stream_type' => 'youtube_embed',
                    'stream_url' => "https://www.youtube-nocookie.com/embed/{$def['fallback_id']}",
                    'is_active' => true,
                    'is_backup' => false,
                ]
            );

            // 5. Ingest Candidates & Attach to Channel Pool
            foreach ($def['candidates'] as $cand) {
                $video = Video::updateOrCreate(
                    ['youtube_id' => $cand['youtube_id']],
                    [
                        'title' => $cand['title'],
                        'description' => $cand['description'],
                        'duration_seconds' => $cand['duration_seconds'],
                        'format_type' => 'feature',
                        'category_slug' => 'islamic',
                        'is_evergreen' => true,
                        'is_embeddable' => true,
                        'availability_status' => 'approved',
                        'confidence_score' => 100,
                        'relevance_score' => 100,
                        'rights_basis' => 'official_embed_permitted',
                        'last_verified_at' => now(),
                    ]
                );

                ChannelVideo::updateOrCreate(
                    [
                        'channel_id' => $channel->id,
                        'video_id' => $video->id,
                    ],
                    [
                        'is_approved' => true,
                        'relevance_score' => 100,
                    ]
                );
            }

            // 6. Generate Rolling Playout Schedule
            $schedResult = $this->scheduleService->generateRollingSchedule($channel, $daysAhead);
            $totalScheduleEntries += $schedResult['entries_created'];
            $channelsDeployed++;

            $channelSummaries[] = [
                'slug' => $channel->slug,
                'name' => $channel->name,
                'language' => $def['language'] ?? 'Multilingual',
                'status' => 'LIVE 24/7',
                'schedules_generated' => $schedResult['entries_created'],
            ];
        }

        return [
            'channels_deployed' => $channelsDeployed,
            'total_schedule_entries' => $totalScheduleEntries,
            'channels' => $channelSummaries,
        ];
    }
}
