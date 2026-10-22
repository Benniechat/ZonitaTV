<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelStream;
use App\Models\ContentRight;
use App\Models\Programme;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LinearChannelEngineService
{
    /**
     * Complete catalogue of 24/7 Linear Channels with curated programmed schedules.
     */
    public function getChannelDefinitions(): array
    {
        return [
            // 1. FOOTBALL & SPORTS
            [
                'name' => 'Football Central 24/7',
                'slug' => 'football-central-24-7',
                'tvg_id' => 'FootballCentral.zoniva',
                'category_slug' => 'sports',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Non-Stop European & African Football Action',
                'description' => 'Continuous 24/7 football channel broadcasting matchday highlights, legendary classic clashes, tactical breakdowns, and player documentaries.',
                'banner_path' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'UEFA Champions League Matchday Highlights',
                        'description' => 'All goals, match reaction, and breathless drama from the latest UCL fixtures.',
                        'duration' => 25,
                        'video_id' => '9E7DmuhBt4M',
                        'presenter' => 'Champions League Desk',
                    ],
                    [
                        'title' => 'Super Eagles: Road to Glory Documentary',
                        'description' => 'The heroic journey of Nigeria national football team through AFCON and world qualifiers.',
                        'duration' => 30,
                        'video_id' => 'a-3c6jnjWpM',
                        'presenter' => 'Zoniva Sports Docs',
                    ],
                    [
                        'title' => 'Premier League Tactical Masterclass: The Art of the Counter',
                        'description' => 'In-depth analytical breakdown dissecting modern high-press systems and set-piece strategies.',
                        'duration' => 20,
                        'video_id' => 'HGlOc25MHh0',
                        'presenter' => 'Tactics Desk Africa',
                    ],
                    [
                        'title' => 'Victor Osimhen & African Stars Abroad: Weekend Review',
                        'description' => 'Tracking every African superstar across Europe: Serie A, Premier League, LaLiga, and Ligue 1.',
                        'duration' => 20,
                        'video_id' => 'reCiyuS1XxQ',
                        'presenter' => 'African Football Report',
                    ],
                    [
                        'title' => 'SuperSport Classic Highlights & Golden Moments',
                        'description' => 'Sensational goals, championship trophies, and legendary moments in African and global football.',
                        'duration' => 25,
                        'video_id' => 'Lpy6S-qDats',
                        'presenter' => 'Classic Archives',
                    ],
                ],
            ],

            // 2. NOLLYWOOD MOVIES
            [
                'name' => 'Nollywood Gold Channel',
                'slug' => 'nollywood-gold',
                'tvg_id' => 'NollywoodGold.zoniva',
                'category_slug' => 'entertainment',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Blockbuster African Cinema Around the Clock',
                'description' => '24/7 premium Nollywood movies, star-studded cinema releases, gripping drama series, and royal village epics.',
                'banner_path' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'The King Throne: Royal Palace Epic',
                        'description' => 'A gripping tale of kingdom betrayal, heritage pride, and ancestral vindication in ancient times.',
                        'duration' => 60,
                        'video_id' => 'JkpD0aflaxs',
                        'presenter' => 'Zoniva Cinema',
                    ],
                    [
                        'title' => 'Lagos Rush Hour: High Stakes Drama',
                        'description' => 'Fast-paced metropolitan crime thriller depicting corporate greed and everyday urban survival.',
                        'duration' => 50,
                        'video_id' => 'BeHanWp9Bcc',
                        'presenter' => 'Nollywood Prime',
                    ],
                    [
                        'title' => 'Heart Of An African Mother',
                        'description' => 'An emotional, tear-jerking family drama starring celebrated veteran Nollywood actors.',
                        'duration' => 45,
                        'video_id' => 'E85KI4GFqP0',
                        'presenter' => 'Classic Nollywood',
                    ],
                    [
                        'title' => 'Love In Island: Modern Romance Feature',
                        'description' => 'Romance and comedy meet in this high-society Lekki love triangle with vibrant twists.',
                        'duration' => 50,
                        'video_id' => 'oYVui7XjRh4',
                        'presenter' => 'Cinema Select',
                    ],
                    [
                        'title' => 'Heritage of the Ancestors: Epic Nollywood Showcase',
                        'description' => 'Traditional African folklore, destiny, and redemption portrayed in stunning cinema format.',
                        'duration' => 55,
                        'video_id' => '-TAW_3666Yw',
                        'presenter' => 'Nollywood Showcase',
                    ],
                ],
            ],

            // 3. GOSPEL MOVIES & CHRISTIAN EDIFICATION
            [
                'name' => 'Faith & Gospel Movies TV',
                'slug' => 'faith-gospel-movies-tv',
                'tvg_id' => 'GospelMovies.zoniva',
                'category_slug' => 'christian-tv',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Transformative Christian Drama & Faith Features',
                'description' => 'Mount Zion classics, anointed faith films, Christian cinema, prayer breakthroughs, and inspiring testimonies playing back-to-back.',
                'banner_path' => 'https://images.unsplash.com/photo-1519491050282-cf00c82424b4?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'Prophetic Breakthrough & Faith Encounter',
                        'description' => 'Transformative word and supernatural empowerment for victorious Christian living.',
                        'duration' => 60,
                        'video_id' => 'XHTgUTQS1l4',
                        'presenter' => 'Faith Cinema Outreach',
                    ],
                    [
                        'title' => 'The Anointing for Distinction',
                        'description' => 'Spiritual principles of divine favor, dedication, and prevailing power in everyday life.',
                        'duration' => 55,
                        'video_id' => 'lsOBC6lFinI',
                        'presenter' => 'Dunamis Faith Broadcast',
                    ],
                    [
                        'title' => 'Prevailing in Spiritual Warfare & Family Breakthrough',
                        'description' => 'A captivating message on spiritual vigilance, Christian integrity, and overcoming obstacles.',
                        'duration' => 45,
                        'video_id' => '32noe8uMX_c',
                        'presenter' => 'Mount Zion Heritage',
                    ],
                    [
                        'title' => 'Supernatural Testimonies & Divine Restoration',
                        'description' => 'Real-life dramatized testimonies of supernatural healing, financial restoration, and deliverance.',
                        'duration' => 40,
                        'video_id' => 'OTylxxz2ZeA',
                        'presenter' => 'Gospel Drama Network',
                    ],
                    [
                        'title' => 'The Atmosphere for Wonders',
                        'description' => 'Prophetic worship, prayers, and deep scripture revelations for continuous spiritual growth.',
                        'duration' => 50,
                        'video_id' => 'rSVEgRWmp3M',
                        'presenter' => 'Kingdom Voice',
                    ],
                ],
            ],

            // 4. AFROBEATS & AFRICAN MUSIC HITS
            [
                'name' => 'Afrobeats Worldwide 24/7',
                'slug' => 'afrobeats-worldwide',
                'tvg_id' => 'AfrobeatsWorldwide.zoniva',
                'category_slug' => 'entertainment',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'The Pulse of African Music & Global Charts',
                'description' => 'Non-stop Afrobeats chart-toppers, Amapiano waves, live acoustic studio sessions, and the hottest music videos from Lagos to Accra and London.',
                'banner_path' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'Afrobeats Global Stage & Festival Anthems',
                        'description' => 'Electrifying stadium performances and viral Afrobeats bangers headlining international charts.',
                        'duration' => 30,
                        'video_id' => 'EzVWx31RCOk',
                        'presenter' => 'Afrobeats Desk',
                    ],
                    [
                        'title' => 'Afrobeats Top 20 Countdown',
                        'description' => 'The definitive ranking of the biggest hits dominating airwaves, streaming platforms, and dance clubs.',
                        'duration' => 30,
                        'video_id' => 'FdthOVcubbU',
                        'presenter' => 'DJ Switch & Chart Desk',
                    ],
                    [
                        'title' => 'Amapiano Groove & African Dancehall Waves',
                        'description' => 'The smoothest South African and West African Amapiano log drums, remixes, and dance floor anthems.',
                        'duration' => 35,
                        'video_id' => '2ixGjOawN98',
                        'presenter' => 'Sound City Vibe',
                    ],
                    [
                        'title' => 'Afrobeats Legends: The African Giant Experience',
                        'description' => 'Visual evolution of Afrobeat from the Afrika Shrine to sold-out stadium world tours.',
                        'duration' => 25,
                        'video_id' => 'ErstFBUQP_k',
                        'presenter' => 'African Rhythms Archive',
                    ],
                    [
                        'title' => 'Acoustic Studio Live: Unplugged Africa',
                        'description' => 'Intimate acoustic sets and raw vocal performances from Africa leading musical talents.',
                        'duration' => 25,
                        'video_id' => 'tbwtVCSF-QU',
                        'presenter' => 'Unplugged Live',
                    ],
                ],
            ],

            // 5. GOSPEL PRAISE & WORSHIP SOUNDS
            [
                'name' => 'Gospel Worship 24/7',
                'slug' => 'gospel-worship-24-7',
                'tvg_id' => 'GospelWorship.zoniva',
                'category_slug' => 'christian-tv',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Continuous Anointed Praise & Intimate Worship',
                'description' => 'Deep spiritual atmosphere 24 hours a day with African gospel anthems, church choral recordings, and prophetic worship encounters.',
                'banner_path' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'Hallelujah Challenge Live: Prophetic Praise',
                        'description' => 'Atmospheric high praise, trumpet symphonies, and spiritual surrender led by Nathaniel Bassey.',
                        'duration' => 45,
                        'video_id' => 'TL-UkMcPQ10',
                        'presenter' => 'Nathaniel Bassey Live',
                    ],
                    [
                        'title' => 'Intimate Devotion & Chants of the Spirit',
                        'description' => 'Deep spirit-filled worship songs and spiritual chants for personal devotion and prayer.',
                        'duration' => 40,
                        'video_id' => '-wuT9VGG1DQ',
                        'presenter' => 'Sanctuary Worship Team',
                    ],
                    [
                        'title' => 'Sinach Live: Way Maker Global Anthems',
                        'description' => 'Worldwide gospel anthems uniting believers across stadiums and nations in joyful praise.',
                        'duration' => 35,
                        'video_id' => 'wGCXBisZDV8',
                        'presenter' => 'Sinach Global Ministry',
                    ],
                    [
                        'title' => 'African High Praise Celebration',
                        'description' => 'High-tempo African praise songs, traditional instruments, and joyful dancing before God.',
                        'duration' => 35,
                        'video_id' => 'IXhpu6SHVKs',
                        'presenter' => 'Gospel Praise Live',
                    ],
                    [
                        'title' => 'Sound of Heaven Choral Extravaganza',
                        'description' => 'Orchestral and choir arrangements of timeless Christian hymns and spiritual anthems.',
                        'duration' => 30,
                        'video_id' => 'p7UjfT61ZS4',
                        'presenter' => 'Heritage Choir',
                    ],
                ],
            ],

            // 6. INDIGENOUS CULTURAL SOUNDS (Yoruba, Hausa, Igbo & East Africa)
            [
                'name' => 'African Heritage & Indigenous Beats',
                'slug' => 'african-heritage-beats',
                'tvg_id' => 'AfricanHeritage.zoniva',
                'category_slug' => 'entertainment',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Yoruba, Hausa, Igbo, Kenyan & Beninoise Cultural Sounds',
                'description' => 'Celebrating the diverse indigenous musical tapestry across Africa: Fuji, Highlife, Ogene, Bongo Flava, and Traditional Sahelian folk music.',
                'banner_path' => 'https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'Fuji & Juju Rhythms: Yoruba Heritage',
                        'description' => 'Talking drum symphonies and philosophical lyrics from legendary master artists.',
                        'duration' => 35,
                        'video_id' => 'VsgjUlqRPSg',
                        'presenter' => 'Cultural Sounds Archive',
                    ],
                    [
                        'title' => 'Eastern Highlife & Ogene Melodies: Igbo Classics',
                        'description' => 'Guitar-driven melodies, vibrant trumpet calls, and rhythmic Ogene percussion.',
                        'duration' => 35,
                        'video_id' => 'vY8V2vpfuCA',
                        'presenter' => 'Eastern Gold Desk',
                    ],
                    [
                        'title' => 'Pan-African Anthems of Hope and Triumph',
                        'description' => 'Soulful choral melodies and inspirational lyrics sung across African tongues.',
                        'duration' => 30,
                        'video_id' => '9dcbDeGrPRQ',
                        'presenter' => 'African Choral Heritage',
                    ],
                    [
                        'title' => 'Praise in the Mother Tongue: Indigenous Voices',
                        'description' => 'Traditional instruments, horn melodies, and deep poetic praise from diverse African states.',
                        'duration' => 35,
                        'video_id' => 'IuW4FLUi3ig',
                        'presenter' => 'Voices of the Continent',
                    ],
                    [
                        'title' => 'Symphony of the Motherland: African Orchestral Suites',
                        'description' => 'Combining African native percussions with modern classical orchestration.',
                        'duration' => 30,
                        'video_id' => 'AFYNB9HZrNY',
                        'presenter' => 'African Philharmonic Society',
                    ],
                ],
            ],

            // 7. KIDS & FAMILY EDUTAINMENT
            [
                'name' => 'Kids Wonder TV',
                'slug' => 'kids-wonder-tv',
                'tvg_id' => 'KidsWonder.zoniva',
                'category_slug' => 'education',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Safe, Fun & Educational Cartoons for Children',
                'description' => '24/7 wholesome educational animations, African folklore stories, phonics, mathematics cartoons, and uplifting nursery rhymes.',
                'banner_path' => 'https://images.unsplash.com/photo-1566140967404-b8b3932483f5?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'Joyful Nursery Rhymes & CoComelon Adventures',
                        'description' => 'Catchy, colorful educational songs teaching the alphabet, numbers, manners, and healthy living.',
                        'duration' => 20,
                        'video_id' => '4WD3nEZKsD8',
                        'presenter' => 'Wonderland Kids',
                    ],
                    [
                        'title' => 'Maths, Phonics & ABCs with JJ and Friends',
                        'description' => 'Fun interactive sing-along melodies making foundational reading and counting easy for young toddlers.',
                        'duration' => 25,
                        'video_id' => 'zwFEN1z8Xag',
                        'presenter' => 'Fun Phonics Lab',
                    ],
                    [
                        'title' => 'Playtime Sing-Along: Family & Animal Songs',
                        'description' => 'Encouraging kindness, helping parents, and discovering animals in our wonderful world.',
                        'duration' => 20,
                        'video_id' => 'WWzralQqx0U',
                        'presenter' => 'Nursery Singalong',
                    ],
                    [
                        'title' => 'Curious Explorers: Good Habits & Sharing',
                        'description' => 'Gentle life lessons on empathy, hygiene, bedtime routines, and friendship.',
                        'duration' => 25,
                        'video_id' => 'xBCVrp90wjY',
                        'presenter' => 'Little Learners Guild',
                    ],
                    [
                        'title' => 'Wonder Lullabies: Soothing Bedtime Cartoons',
                        'description' => 'Calm, gentle music and starry animations to help kids rest peacefully.',
                        'duration' => 20,
                        'video_id' => 'fL2NbHrE7TI',
                        'presenter' => 'Bedtime Wonder Desk',
                    ],
                ],
            ],

            // 8. TECH & INNOVATION PULSE
            [
                'name' => 'Tech Pulse Africa & Global',
                'slug' => 'tech-pulse-tv',
                'tvg_id' => 'TechPulse.zoniva',
                'category_slug' => 'education',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'AI, Startups, Silicon Valley & African Innovation',
                'description' => 'Gadget teardowns, breakthrough AI discoveries, African startup founders, coding tutorials, and frontier tech updates.',
                'banner_path' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'MKBHD: The Future of Consumer Hardware & AI',
                        'description' => 'Top-tier tech reviews, industrial design scrutiny, and cutting-edge gadget benchmarking.',
                        'duration' => 25,
                        'video_id' => 'ESABTN4VtrA',
                        'presenter' => 'Hardware Review Lab',
                    ],
                    [
                        'title' => 'Smartphone Showdown: Cameras, Silicon & Battery Tech',
                        'description' => 'Direct comparisons between the world fastest processors, folding screens, and mobile cameras.',
                        'duration' => 25,
                        'video_id' => 'scwlR0QHGOk',
                        'presenter' => 'Future Tech Desk',
                    ],
                    [
                        'title' => 'Electric Vehicles & Clean Energy Revolution',
                        'description' => 'Testing autonomous driving, solid-state battery breakthroughs, and the EV grid transition.',
                        'duration' => 25,
                        'video_id' => 'QVv3mQzlrN0',
                        'presenter' => 'Mobility Frontier',
                    ],
                    [
                        'title' => 'Artificial Intelligence in 2026: The New Human Age',
                        'description' => 'How multimodal AI models, reasoning agents, and robotics are reshaping work and society.',
                        'duration' => 30,
                        'video_id' => '_0G4OG39XjI',
                        'presenter' => 'AI Today Desk',
                    ],
                    [
                        'title' => 'Next-Gen Wearables & Spatial Computing',
                        'description' => 'Smart glasses, AR headsets, neural interfaces, and health sensor innovations.',
                        'duration' => 20,
                        'video_id' => '3iRUwVzRDZQ',
                        'presenter' => 'Wearables Lab',
                    ],
                ],
            ],

            // 9. TED INSIGHTS & INSPIRATION
            [
                'name' => 'Ideas & Talks 24/7',
                'slug' => 'ideas-and-talks-tv',
                'tvg_id' => 'IdeasAndTalks.zoniva',
                'category_slug' => 'education',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Ideas Worth Spreading: Science, Psychology & Life',
                'description' => 'Thought-provoking keynote speeches, behavioral psychology, mindset philosophy, and visionary lectures from global leaders.',
                'banner_path' => 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'TED Talks: Groundbreaking Ideas Shaping Humanity',
                        'description' => 'Visionary insights on science, human behavior, technology, and global culture.',
                        'duration' => 20,
                        'video_id' => 'vfbNzpd6cIw',
                        'presenter' => 'Ideas Global Stage',
                    ],
                    [
                        'title' => 'TED: The Psychology of High Performance & Purpose',
                        'description' => 'Unlocking cognitive potential, defeating fear, and finding meaning in challenging times.',
                        'duration' => 20,
                        'video_id' => 'UNTZMlgf2To',
                        'presenter' => 'Leadership Forum',
                    ],
                    [
                        'title' => 'Mastering Habits & Defeating Procrastination',
                        'description' => 'Scientific mechanisms of atomic habits, deep focus, and personal productivity systems.',
                        'duration' => 20,
                        'video_id' => 'TRuu-eZsl1c',
                        'presenter' => 'Psychology Desk',
                    ],
                    [
                        'title' => 'The Future of Civilization & Environmental Stewardship',
                        'description' => 'Innovative solutions to climate resilience, food security, and sustainable cities.',
                        'duration' => 20,
                        'video_id' => 'ueOxonBk970',
                        'presenter' => 'Global Solutions Forum',
                    ],
                    [
                        'title' => 'Emotional Intelligence & Building Lifelong Relationships',
                        'description' => 'The power of empathy, active listening, and social bonding in an increasingly digital world.',
                        'duration' => 20,
                        'video_id' => 'ny26teC-4bU',
                        'presenter' => 'Humanity First',
                    ],
                ],
            ],

            // 10. BUSINESS & ENTREPRENEURSHIP MASTERCLASS
            [
                'name' => 'Business Masterclass TV',
                'slug' => 'business-masterclass-tv',
                'tvg_id' => 'BusinessMasterclass.zoniva',
                'category_slug' => 'education',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Wealth Creation, Investing, Seminars & Strategy',
                'description' => 'Step-by-step masterclasses on real estate investing, stock market strategies, supply chain economics, and modern marketing.',
                'banner_path' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'Valuetainment: How to Build Multi-Million Dollar Enterprises',
                        'description' => 'Patrick Bet-David shares core business strategy, negotiation tactics, and leadership rules.',
                        'duration' => 30,
                        'video_id' => '3UgyPEqnOBE',
                        'presenter' => 'Wealth Academy',
                    ],
                    [
                        'title' => 'Principles of Wealth Creation & Compound Investing',
                        'description' => 'Practical asset allocation, risk mitigation, and passive cash-flow frameworks for builders.',
                        'duration' => 30,
                        'video_id' => '-ulnlWH7JqQ',
                        'presenter' => 'Capital Allocation Desk',
                    ],
                    [
                        'title' => 'The Art of Sales, Pitching & Customer Acquisition',
                        'description' => 'Closing enterprise deals, structuring win-win partnerships, and dominating markets.',
                        'duration' => 25,
                        'video_id' => 'nMa-azgWbQo',
                        'presenter' => 'Sales Mastery Lab',
                    ],
                    [
                        'title' => 'Scaling Startups from Seed to Unicorn',
                        'description' => 'Unit economics, talent recruitment, equity structure, and sustainable operational growth.',
                        'duration' => 30,
                        'video_id' => 'vGvx82X8SPc',
                        'presenter' => 'Startup Strategy Hub',
                    ],
                    [
                        'title' => 'The High-Performance Entrepreneur Mindset',
                        'description' => 'Time management, mental resilience, crisis decision-making, and long-term vision.',
                        'duration' => 25,
                        'video_id' => 'Qpuj1nhcxiI',
                        'presenter' => 'Executive Briefing',
                    ],
                ],
            ],

            // 11. WORLD & NATURE DOCUMENTARIES
            [
                'name' => 'Discovery Docs & Earth',
                'slug' => 'discovery-docs-earth',
                'tvg_id' => 'DiscoveryDocs.zoniva',
                'category_slug' => 'education',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Breathtaking Expeditions, Oceans & African Wildlife',
                'description' => 'Investigative history, mega-engineering marvels, deep-sea marine biology, and wildlife migrations captured in stunning high definition.',
                'banner_path' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'BBC Earth: Predators & Kings of the African Savanna',
                        'description' => 'Lions, leopards, and cheetahs navigating hunt strategies across the Maasai Mara.',
                        'duration' => 50,
                        'video_id' => '4IV1tTf0CZs',
                        'presenter' => 'Wild Planet Docs',
                    ],
                    [
                        'title' => 'Deep Ocean Exploration: Alien Worlds in the Abyss',
                        'description' => 'Bioluminescent creatures and uncharted geological trenches thousands of meters below sea level.',
                        'duration' => 45,
                        'video_id' => 'cHqpobJIrKk',
                        'presenter' => 'Oceanographic Lab',
                    ],
                    [
                        'title' => 'The Serengeti Migration: The Journey of Life',
                        'description' => 'Two million herbivores cross perilous crocodile rivers in search of survival and pasture.',
                        'duration' => 45,
                        'video_id' => 'j1YmhOEyb4Y',
                        'presenter' => 'Wild Kingdom Channel',
                    ],
                    [
                        'title' => 'National Geographic: Lost Empires & Tomb Mysteries',
                        'description' => 'Archaeologists uncover monumental temples, lost cities, and ancient technological secrets.',
                        'duration' => 40,
                        'video_id' => 'Jl7PlR0Mwfk',
                        'presenter' => 'Ancient Worlds Archive',
                    ],
                    [
                        'title' => 'Planet Earth Wonders: Mountains, Glaciers & Volcanoes',
                        'description' => 'The breathtaking geological forces that sculpted the peaks and canyons of our planet.',
                        'duration' => 40,
                        'video_id' => 'tn7ybEP6fSU',
                        'presenter' => 'Earth Science Desk',
                    ],
                ],
            ],

            // 12. BOLLYWOOD & K-DRAMA CINEMA
            [
                'name' => 'Bollywood & K-Wave Cinema',
                'slug' => 'bollywood-k-wave',
                'tvg_id' => 'BollywoodKWave.zoniva',
                'category_slug' => 'entertainment',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'High-Energy Bollywood Action & Captivating Korean Dramas',
                'description' => 'Dazzling dance choreographies, romantic Korean series, and breathtaking martial arts features streaming around the clock.',
                'banner_path' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'KBS World Drama: Romance, Mystery & Family Honor',
                        'description' => 'Heartwarming storylines, Seoul high-fashion intrigue, and compelling romantic drama.',
                        'duration' => 45,
                        'video_id' => 'ciI-8n97Sgw',
                        'presenter' => 'K-Wave Studio',
                    ],
                    [
                        'title' => 'Seoul Nights: Metropolitan Suspense Thriller',
                        'description' => 'High-stakes corporate detective investigations unraveling secrets in modern Gangnam.',
                        'duration' => 45,
                        'video_id' => '3G3ZZ3jKQ24',
                        'presenter' => 'K-Drama Prime',
                    ],
                    [
                        'title' => 'Bollywood Blockbuster Action & Rhythm Spectacular',
                        'description' => 'High-octane stunt choreography, memorable songs, and emotional cinematic heroism.',
                        'duration' => 60,
                        'video_id' => '582g4whHt7Y',
                        'presenter' => 'Bollywood Spotlight',
                    ],
                    [
                        'title' => 'Grand Musical Extravaganza: Mumbai Celebrations',
                        'description' => 'Lavish dance set-pieces, festive rhythms, and romance in signature Indian cinema style.',
                        'duration' => 50,
                        'video_id' => '6DCSAFKqrLI',
                        'presenter' => 'Cinema World',
                    ],
                    [
                        'title' => 'Legends of Asian Cinema: Martial Arts & Epics',
                        'description' => 'Unforgettable historical battles, honor codes, and master martial arts cinema.',
                        'duration' => 55,
                        'video_id' => 'zXtTpPTLizQ',
                        'presenter' => 'Asian Cinema Guild',
                    ],
                ],
            ],

            // 13. TRENDING STORIES & CELEBRITY SPOTLIGHT
            [
                'name' => 'Trending Stories & Pop Culture TV',
                'slug' => 'trending-stories-tv',
                'tvg_id' => 'TrendingStories.zoniva',
                'category_slug' => 'entertainment',
                'country_code' => 'NGA',
                'language_code' => 'en',
                'tagline' => 'Viral Moments, Red Carpets & Celebrity Biographies',
                'description' => 'Behind-the-scenes interviews, red carpet fashion reviews, viral internet stories, and deep dives into world cultural icons.',
                'banner_path' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1400&q=80',
                'logo_path' => '/images/logo/zonitatvlogo2.png',
                'programmes' => [
                    [
                        'title' => 'Red Carpet Glamour: Award Season Highlights & Fashions',
                        'description' => 'Fashion critics and hosts review dazzling gowns, celebrity interviews, and gala trophies.',
                        'duration' => 25,
                        'video_id' => 'PoG5Pnk5t2c',
                        'presenter' => 'Style & Fashion Desk',
                    ],
                    [
                        'title' => 'Viral Culture & Global Internet Chronicles',
                        'description' => 'The biggest social media phenomena, creator controversies, and pop-culture trends explained.',
                        'duration' => 25,
                        'video_id' => 'l27kqvm64QY',
                        'presenter' => 'Pop Culture Pulse',
                    ],
                    [
                        'title' => 'K-Wave Phenomenon: Global Idols & Fandom World',
                        'description' => 'Behind the stadium tours, choreography bootcamps, and worldwide chart dominance.',
                        'duration' => 25,
                        'video_id' => 'otdTo6ceHAw',
                        'presenter' => 'Global Idol Watch',
                    ],
                    [
                        'title' => 'The Rise and Reign of African Cinema Stars',
                        'description' => 'Exclusive profiles on the filmmakers and actors placing African cinema on global screens.',
                        'duration' => 25,
                        'video_id' => '_M0R-xxLx3U',
                        'presenter' => 'African Cinema Spotlight',
                    ],
                    [
                        'title' => 'Entertainment Tonight: Exclusive Studio Interviews',
                        'description' => 'Conversations with directors, producers, and musicians shaping global entertainment today.',
                        'duration' => 25,
                        'video_id' => 'QduBhtfh-GA',
                        'presenter' => 'Celebrity Spotlight',
                    ],
                ],
            ],
        ];
    }

    /**
     * Seeds, configures, and synchronizes all linear channels and their 24/7 schedules.
     */
    public function deployLinearChannels(): array
    {
        $admin = User::first();
        $definitions = $this->getChannelDefinitions();
        $createdChannels = 0;
        $createdProgrammes = 0;

        foreach ($definitions as $def) {
            $catSlug = $def['category_slug'];
            $category = Category::where('slug', $catSlug)->first();

            $channel = Channel::updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'name' => $def['name'],
                    'tvg_id' => $def['tvg_id'],
                    'tagline' => $def['tagline'],
                    'description' => $def['description'],
                    'broadcaster_name' => 'Zoniva Media Network',
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
                    'fallback_video_id' => $def['programmes'][0]['video_id'] ?? 'dQw4w9WgXcQ',
                ]
            );

            if ($category) {
                $channel->categories()->syncWithoutDetaching([$category->id]);
            }

            // Rights record
            ContentRight::updateOrCreate(
                ['channel_id' => $channel->id],
                [
                    'rights_holder_name' => 'Zoniva Media Network Linear Playout',
                    'contract_reference' => 'ZONIVA-LINEAR-'.strtoupper(Str::random(6)),
                    'status' => 'directory_approved',
                    'allow_directory_listing' => true,
                    'allow_web_embedding' => true,
                    'allow_direct_hls' => true,
                    'permitted_territories' => ['GLOBAL'],
                    'effective_from' => now()->subMonth(),
                    'expires_at' => now()->addYears(3),
                    'reviewed_by_user_id' => $admin?->id,
                    'reviewed_at' => now(),
                    'reviewer_notes' => 'Linear playout channel configured with automated scheduling.',
                ]
            );

            // ChannelStream placeholder (points to channel linear playout)
            ChannelStream::updateOrCreate(
                ['channel_id' => $channel->id, 'label' => '24/7 Linear Playout'],
                [
                    'stream_type' => 'youtube_embed',
                    'stream_url' => "https://www.youtube-nocookie.com/embed/{$channel->fallback_video_id}",
                    'is_active' => true,
                    'is_backup' => false,
                ]
            );

            // Schedule the programmes continuously across a 48-hour timeline
            $programmes = $def['programmes'];
            $totalCycleMinutes = array_sum(array_column($programmes, 'duration'));
            if ($totalCycleMinutes < 1) {
                $totalCycleMinutes = 60;
            }

            // Start timeline from beginning of today
            $timelinePointer = Carbon::today()->startOfDay();
            $timelineEnd = Carbon::today()->addDays(2)->endOfDay();
            $now = Carbon::now();

            $order = 1;
            while ($timelinePointer->lessThan($timelineEnd)) {
                foreach ($programmes as $progData) {
                    if ($timelinePointer->greaterThanOrEqualTo($timelineEnd)) {
                        break;
                    }

                    $startTime = $timelinePointer->copy();
                    $duration = $progData['duration'];
                    $endTime = $startTime->copy()->addMinutes($duration);

                    $isLiveNow = $now->between($startTime, $endTime);

                    Programme::updateOrCreate(
                        [
                            'channel_id' => $channel->id,
                            'start_time' => $startTime,
                        ],
                        [
                            'category_id' => $category?->id,
                            'title' => $progData['title'],
                            'slug' => Str::slug($progData['title']).'-'.$startTime->format('Ymd-Hi'),
                            'description' => $progData['description'],
                            'presenter_or_artist' => $progData['presenter'] ?? null,
                            'poster_image' => $channel->banner_path,
                            'video_source' => 'youtube',
                            'video_id' => $progData['video_id'],
                            'video_url' => "https://www.youtube.com/watch?v={$progData['video_id']}",
                            'duration_minutes' => $duration,
                            'play_order' => $order++,
                            'end_time' => $endTime,
                            'is_live' => $isLiveNow,
                        ]
                    );

                    $createdProgrammes++;
                    $timelinePointer->addMinutes($duration);
                }
            }

            $createdChannels++;
        }

        return [
            'channels' => $createdChannels,
            'programmes' => $createdProgrammes,
        ];
    }

    /**
     * Resolves currently active programme and next upcoming programmes for a linear channel.
     */
    public function getActivePlayout(Channel $channel): array
    {
        $now = Carbon::now();

        // 1. Check for programme whose scheduled window contains right now
        $current = Programme::where('channel_id', $channel->id)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        // Fallback: If gap in schedule, grab nearest programme
        if (! $current) {
            $current = Programme::where('channel_id', $channel->id)
                ->where('start_time', '<=', $now)
                ->orderByDesc('start_time')
                ->first();
        }

        // 2. Upcoming programmes queue
        $upcoming = Programme::where('channel_id', $channel->id)
            ->where('start_time', '>', $current ? $current->start_time : $now)
            ->orderBy('start_time')
            ->take(12)
            ->get();

        // 3. Compute elapsed seconds within current programme for seamless start-time synchronization
        $startSecondsOffset = 0;
        if ($current && $current->start_time->lessThanOrEqualTo($now)) {
            $startSecondsOffset = $now->diffInSeconds($current->start_time);
            $totalDurationSeconds = $current->duration_minutes * 60;
            if ($startSecondsOffset >= $totalDurationSeconds) {
                $startSecondsOffset = $startSecondsOffset % max(1, $totalDurationSeconds);
            }
        }

        // 4. Construct player playlist array with accurate start offsets
        $playlistQueue = [];
        if ($current) {
            $playlistQueue[] = [
                'id' => $current->id,
                'title' => $current->title,
                'description' => $current->description,
                'presenter' => $current->presenter_or_artist,
                'video_id' => $current->video_id ?: $channel->fallback_video_id,
                'duration_seconds' => $current->duration_minutes * 60,
                'start_seconds' => $startSecondsOffset,
                'start_formatted' => $current->start_time->format('h:i A'),
                'is_current' => true,
            ];
        }

        foreach ($upcoming as $next) {
            $playlistQueue[] = [
                'id' => $next->id,
                'title' => $next->title,
                'description' => $next->description,
                'presenter' => $next->presenter_or_artist,
                'video_id' => $next->video_id ?: $channel->fallback_video_id,
                'duration_seconds' => $next->duration_minutes * 60,
                'start_seconds' => 0,
                'start_formatted' => $next->start_time->format('h:i A'),
                'is_current' => false,
            ];
        }

        return [
            'channel' => $channel,
            'current_programme' => $current,
            'upcoming_programmes' => $upcoming,
            'playlist_queue' => $playlistQueue,
            'is_linear' => true,
        ];
    }
}
