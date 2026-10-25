<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\GospelFilmProduction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GospelFilmController extends Controller
{
    /**
     * Display the 60 Gospel Film Channels and Production Ministries Directory.
     */
    public function index(Request $request): View
    {
        $query = GospelFilmProduction::query();

        // Search by ministry name, founder, or key title
        if ($request->filled('q')) {
            $search = $request->query('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('founder_or_minister', 'like', "%{$search}%")
                    ->orWhere('headquarters_city', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by Lead Priority
        if ($request->filled('priority')) {
            $query->where('lead_priority', (int) $request->query('priority'));
        }

        // Filter by Licensing Status
        if ($request->filled('status')) {
            $query->where('licensing_status', $request->query('status'));
        }

        // Filter by Language
        if ($request->filled('lang')) {
            $query->where('primary_language', 'like', '%'.$request->query('lang').'%');
        }

        // Sort: Priority 1 first, then alphabetical
        $productions = $query->orderBy('lead_priority', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Dedicated 24/7 Gospel Linear Channels
        $gospelChannels = Channel::whereIn('slug', [
            'african-gospel-cinema-tv',
            'mount-zion-gospel-drama-tv',
            'prem-tv-gospel-movies',
            'faith-gospel-movies-tv',
        ])->where('is_active', true)->get()->keyBy('slug');

        $allGospelChannels = Channel::whereHas('categories', fn ($q) => $q->where('slug', 'christian-tv'))
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');

        $stats = [
            'total_channels' => GospelFilmProduction::count(),
            'priority_1' => GospelFilmProduction::where('lead_priority', 1)->count(),
            'full_length_count' => GospelFilmProduction::where('has_full_length_movies', true)->count(),
            'english_subtitled_count' => GospelFilmProduction::where('has_english_subtitles', true)->count(),
            'active_syndicated' => GospelFilmProduction::whereIn('licensing_status', ['active_broadcasting', 'agreement_signed'])->count(),
            'on_air_count' => $allGospelChannels->count(),
        ];

        return view('gospel.index', compact('productions', 'gospelChannels', 'allGospelChannels', 'stats'));
    }
}
