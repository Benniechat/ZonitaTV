<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\NollywoodChannel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NollywoodController extends Controller
{
    /**
     * Display the 60 Nollywood Channels, Filmmakers, and Movie Aggregators Directory.
     */
    public function index(Request $request): View
    {
        $query = NollywoodChannel::query();

        // 1. Text Search
        if ($request->filled('q')) {
            $search = (string) $request->query('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('creator_or_lead', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('primary_language', 'like', "%{$search}%");
            });
        }

        // 2. Filter by Category
        if ($request->filled('category')) {
            $query->where('channel_category', $request->query('category'));
        }

        // 3. Filter by Lead Priority
        if ($request->filled('priority')) {
            $query->where('lead_priority', (int) $request->query('priority'));
        }

        // 4. Filter by Language
        if ($request->filled('lang')) {
            $query->where('primary_language', 'like', '%' . $request->query('lang') . '%');
        }

        // 5. Filter by Licensing Status
        if ($request->filled('status')) {
            $query->where('licensing_status', $request->query('status'));
        }

        // Order: Priority 1 first, then name
        $channels = $query->orderBy('lead_priority', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Featured 24/7 Nollywood Broadcast Channels
        $featuredSlugs = [
            'ruth-kadiri-247',
            'uchenna-mbunabo-tv',
            'maurice-sam-tv',
            'omoni-oboli-tv',
            'bimbo-ademoye-tv',
            'apatatv-plus',
            'libra-tv',
            'ibakatv-nollywood',
            'faan-tv-funke-akindele',
            'ndani-tv',
            'royal-arts-tv',
        ];

        $featuredChannels = Channel::whereIn('slug', $featuredSlugs)
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');

        $allNollywoodChannels = Channel::whereHas('categories', fn ($q) => $q->whereIn('slug', ['movies', 'entertainment']))
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');

        $stats = [
            'total_channels' => NollywoodChannel::count(),
            'priority_1' => NollywoodChannel::where('lead_priority', 1)->count(),
            'yoruba_count' => NollywoodChannel::where('channel_category', 'yoruba_movies')->count(),
            'aggregators_count' => NollywoodChannel::where('channel_category', 'aggregator_library')->count(),
            'on_air_count' => $allNollywoodChannels->count(),
        ];

        return view('nollywood.index', compact('channels', 'featuredChannels', 'allNollywoodChannels', 'stats'));
    }
}
