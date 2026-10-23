<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Programme;
use App\Models\ViewingEvent;
use App\Services\ChannelCatalogueService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected ChannelCatalogueService $catalogueService;

    protected RecommendationService $recommendationService;

    public function __construct(
        ChannelCatalogueService $catalogueService,
        RecommendationService $recommendationService
    ) {
        $this->catalogueService = $catalogueService;
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request)
    {
        $country = $request->header('CF-IPCountry', 'NGA');
        $now = now();

        // 1. Hero Billboard
        $heroChannel = Channel::where('slug', 'the-matchroom-africa')->first()
            ?? Channel::where('is_featured', true)->first();

        // 2. Live & Trending Now (Programs marked is_live or active)
        $liveTrending = Programme::with(['channel', 'category'])
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->take(5)
            ->get();

        // Calculate time remaining for live items
        $liveTrending->transform(function ($prog) use ($now) {
            $diffMins = max(10, $now->diffInMinutes($prog->end_time, false));
            $hours = intdiv($diffMins, 60);
            $mins = $diffMins % 60;
            $prog->time_left_str = ($hours > 0 ? "{$hours}h " : '')."{$mins}m left";

            return $prog;
        });

        // 3. Popular Channels
        $popularChannels = Channel::with(['categories'])
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('slug', '!=', 'the-matchroom-africa')
            ->take(6)
            ->get();

        // 4. Section 1 (2-Col): Football & Sports Showcase
        $footballSports = [
            [
                'title' => 'Match Analysis',
                'category' => 'Sports',
                'time_str' => 'Today • 7:00 PM',
                'image' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=400&q=80',
                'slug' => 'sports-central',
            ],
            [
                'title' => 'Sports Highlights',
                'category' => 'Sports',
                'time_str' => 'Today • 9:30 PM',
                'image' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=400&q=80',
                'slug' => 'sports-central',
            ],
            [
                'title' => 'The Football Show',
                'category' => 'Sports',
                'time_str' => 'Tomorrow • 6:00 PM',
                'image' => 'https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=400&q=80',
                'slug' => 'the-matchroom-africa',
            ],
            [
                'title' => 'Athlete Stories',
                'category' => 'Sports',
                'time_str' => 'Tomorrow • 8:00 PM',
                'image' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=400&q=80',
                'slug' => 'sports-central',
            ],
        ];

        // 5. Section 1 (2-Col): News Around the World Showcase
        $newsWorld = [
            [
                'title' => 'World Brief',
                'category' => 'News',
                'time_str' => 'Today • 6:30 PM',
                'image' => 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=400&q=80',
                'slug' => 'global-news',
            ],
            [
                'title' => 'Africa Focus',
                'category' => 'News',
                'time_str' => 'Today • 8:00 PM',
                'image' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=400&q=80',
                'slug' => 'channels-tv',
            ],
            [
                'title' => 'The Daily Report',
                'category' => 'News',
                'time_str' => 'Tomorrow • 7:00 PM',
                'image' => 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=400&q=80',
                'slug' => 'tvc-news-nigeria',
            ],
            [
                'title' => 'Global Perspectives',
                'category' => 'News',
                'time_str' => 'Tomorrow • 9:00 PM',
                'image' => 'https://images.unsplash.com/photo-1526470608268-f674ce90ebd4?auto=format&fit=crop&w=400&q=80',
                'slug' => 'nta-news-24',
            ],
        ];

        // 6. Section 2 (2-Col): Faith & Inspiration Showcase
        $faithInspiration = [
            [
                'title' => 'Sunday Service',
                'category' => 'Christian',
                'time_str' => 'Today • 10:00 AM',
                'image' => 'https://images.unsplash.com/photo-1519491050282-cf00c82424b4?auto=format&fit=crop&w=400&q=80',
                'slug' => 'faith-channel',
            ],
            [
                'title' => 'Bible Teachings',
                'category' => 'Christian',
                'time_str' => 'Today • 1:00 PM',
                'image' => 'https://images.unsplash.com/photo-1490730141103-6cac27aaab94?auto=format&fit=crop&w=400&q=80',
                'slug' => 'dove-tv',
            ],
            [
                'title' => 'Faith in Action',
                'category' => 'Christian',
                'time_str' => 'Today • 7:00 PM',
                'image' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&w=400&q=80',
                'slug' => 'emmanuel-tv',
            ],
            [
                'title' => 'Worship Moments',
                'category' => 'Christian',
                'time_str' => 'Tomorrow • 6:00 PM',
                'image' => 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=400&q=80',
                'slug' => 'faith-channel',
            ],
        ];

        // 7. Section 2 (2-Col): Learning & Discovery Showcase
        $learningDiscovery = [
            [
                'title' => 'Science Today',
                'category' => 'Education',
                'time_str' => 'Today • 11:00 AM',
                'image' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=400&q=80',
                'slug' => 'knowledge-tv',
            ],
            [
                'title' => 'History Uncovered',
                'category' => 'Education',
                'time_str' => 'Today • 4:00 PM',
                'image' => 'https://images.unsplash.com/photo-1461360370896-922624d12aa1?auto=format&fit=crop&w=400&q=80',
                'slug' => 'world-documentaries',
            ],
            [
                'title' => 'Tech & Innovation',
                'category' => 'Education',
                'time_str' => 'Today • 7:30 PM',
                'image' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?auto=format&fit=crop&w=400&q=80',
                'slug' => 'dw-documentary-africa',
            ],
            [
                'title' => 'Better Living',
                'category' => 'Education',
                'time_str' => 'Tomorrow • 8:00 PM',
                'image' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=400&q=80',
                'slug' => 'knowledge-tv',
            ],
        ];

        // 8. Right Sidebar: Tonight on Zoniva (Schedule Rows)
        $tonightSchedule = [
            [
                'time' => '6:00 PM',
                'title' => 'The Matchroom Africa',
                'category' => 'Sports',
                'image' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=200&q=80',
                'slug' => 'the-matchroom-africa',
            ],
            [
                'time' => '7:00 PM',
                'title' => 'Global News 24',
                'category' => 'News',
                'image' => 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=200&q=80',
                'slug' => 'global-news',
            ],
            [
                'time' => '8:00 PM',
                'title' => 'The Weekly Show',
                'category' => 'Entertainment',
                'image' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=200&q=80',
                'slug' => 'culture-plus',
            ],
            [
                'time' => '9:00 PM',
                'title' => 'African Voices',
                'category' => 'Documentaries',
                'image' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=200&q=80',
                'slug' => 'world-documentaries',
            ],
            [
                'time' => '10:00 PM',
                'title' => 'Faith & Life',
                'category' => 'Christian',
                'image' => 'https://images.unsplash.com/photo-1519491050282-cf00c82424b4?auto=format&fit=crop&w=200&q=80',
                'slug' => 'faith-channel',
            ],
        ];

        // 9. Right Sidebar: Your Viewing Guide (Recommended for You)
        $recommendedForYou = [
            [
                'title' => 'Football Analysis',
                'category' => 'Sports',
                'time' => '7:00 PM',
                'image' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=150&q=80',
                'slug' => 'sports-central',
            ],
            [
                'title' => 'World News Tonight',
                'category' => 'News',
                'time' => '8:30 PM',
                'image' => 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=150&q=80',
                'slug' => 'global-news',
            ],
            [
                'title' => 'Faith & Family',
                'category' => 'Christian',
                'time' => '9:00 PM',
                'image' => 'https://images.unsplash.com/photo-1519491050282-cf00c82424b4?auto=format&fit=crop&w=150&q=80',
                'slug' => 'faith-channel',
            ],
        ];

        $categories = Category::where('is_nav_visible', true)->orderBy('sort_order')->get();

        return view('home', compact(
            'heroChannel',
            'liveTrending',
            'popularChannels',
            'footballSports',
            'newsWorld',
            'faithInspiration',
            'learningDiscovery',
            'tonightSchedule',
            'recommendedForYou',
            'categories'
        ));
    }

    public function whatsappRedirect(Request $request)
    {
        try {
            $channelId = Channel::first()?->id;
            if ($channelId) {
                ViewingEvent::create([
                    'user_id' => $request->user()?->id,
                    'channel_id' => $channelId,
                    'session_id' => $request->session()->getId(),
                    'event_type' => 'destination_click',
                    'ip_country' => $request->header('CF-IPCountry', 'NGA'),
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Telemetry failure should never block external redirect
        }

        $officialWhatsAppChannelUrl = config('services.whatsapp.channel_url', 'https://whatsapp.com/channel/0029VbDeFOLCHDycVG6zZz3z');

        return redirect()->away($officialWhatsAppChannelUrl);
    }
}
