<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ComedyChannel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComedyController extends Controller
{
    /**
     * Display the Zonita Comedy Entertainment Hub & Directory.
     */
    public function index(Request $request): View
    {
        $query = ComedyChannel::query();

        // 1. Text Search
        if ($request->filled('q')) {
            $search = (string) $request->query('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('creator_or_brand', 'like', "%{$search}%")
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

        // Order: Priority 1 first, then name
        $creators = $query->orderBy('lead_priority', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(18)
            ->withQueryString();

        // The 10 Dedicated Zonita Virtual Comedy Channels
        $virtualSlugs = [
            'zonita-comedy-central',
            'zonita-standup-comedy',
            'zonita-comedy-classics',
            'zonita-family-laughs',
            'zonita-animated-comedy',
            'zonita-street-comedy',
            'zonita-relationship-comedy',
            'zonita-naija-sitcoms',
            'zonita-church-social-comedy',
            'zonita-african-comedy',
        ];

        $virtualChannels = Channel::whereIn('slug', $virtualSlugs)
            ->with(['primaryStream', 'scheduleEntries' => function ($q) {
                $q->where('start_time', '<=', now('UTC'))
                    ->where('end_time', '>=', now('UTC'));
            }])
            ->get()
            ->sortBy(function ($ch) use ($virtualSlugs) {
                $pos = array_search($ch->slug, $virtualSlugs, true);

                return $pos !== false ? $pos : 99;
            });

        // Hub Statistics
        $stats = [
            'total_creators' => ComedyChannel::count(),
            'standup_count' => ComedyChannel::where('channel_category', 'standup_comedy')->count(),
            'skit_count' => ComedyChannel::where('channel_category', 'skit_creators')->count(),
            'animation_studios_count' => ComedyChannel::whereIn('channel_category', ['animated_comedy', 'production_studios'])->count(),
            'top_priority_count' => ComedyChannel::where('lead_priority', 1)->count(),
            'est_catalogue_total' => ComedyChannel::sum('catalogue_size_est'),
            'virtual_channels_count' => $virtualChannels->count(),
        ];

        return view('comedy.index', compact('creators', 'virtualChannels', 'stats'));
    }
}
