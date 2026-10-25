<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\IslamicContentProvider;
use App\Models\ScheduleEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IslamicChannelController extends Controller
{
    /**
     * Display the Islamic Broadcasting Hub, 24/7 Virtual Channels, and Verified Provider Directory.
     */
    public function index(Request $request): View
    {
        $query = IslamicContentProvider::query();

        // 1. Text Search
        if ($request->filled('q')) {
            $search = (string) $request->query('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('youtube_channel_name', 'like', "%{$search}%")
                    ->orWhere('youtube_handle', 'like', "%{$search}%")
                    ->orWhere('headquarters_city', 'like', "%{$search}%")
                    ->orWhere('primary_language', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // 2. Filter by Provider Type
        if ($request->filled('type')) {
            $query->where('provider_type', $request->query('type'));
        }

        // 3. Filter by Target Channel
        if ($request->filled('channel')) {
            $targetChannel = (string) $request->query('channel');
            $query->whereJsonContains('target_channels', $targetChannel);
        }

        // 4. Filter by Language
        if ($request->filled('lang')) {
            $query->where('primary_language', 'like', '%'.$request->query('lang').'%');
        }

        // 5. Filter by Licensing Status
        if ($request->filled('status')) {
            $query->where('licensing_status', $request->query('status'));
        }

        // 6. Filter by Source Classification
        if ($request->filled('classification')) {
            $query->where('source_classification', $request->query('classification'));
        }

        // Pagination
        $providers = $query->orderBy('confidence_score', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(12)
            ->withQueryString();

        // All active Islamic Channels (Core virtual networks + verified provider broadcast channels)
        $islamicChannels = Channel::whereHas('categories', fn ($q) => $q->where('slug', 'islamic'))
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->keyBy('slug');

        $islamicSlugs = [
            'zonita-islamic-cinema',
            'zonita-arewa-movies',
            'zonita-yoruba-islamic',
            'zonita-quran',
            'zonita-islamic-knowledge',
            'zonita-islamic-documentaries',
            'zonita-nasheed',
            'zonita-islamic-family',
        ];

        // Fetch Now Playing and Next Up for each channel
        $now = now('Africa/Lagos');
        $channelSchedules = [];
        foreach ($islamicChannels as $slug => $channel) {
            $current = ScheduleEntry::where('channel_id', $channel->id)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->first();

            $next = ScheduleEntry::where('channel_id', $channel->id)
                ->where('start_time', '>', $now)
                ->orderBy('start_time', 'asc')
                ->first();

            $channelSchedules[$slug] = [
                'current' => $current,
                'next' => $next,
            ];
        }

        $stats = [
            'total_providers' => IslamicContentProvider::count(),
            'verified_owners' => IslamicContentProvider::where('verification_status', 'VERIFIED_OWNER')->count(),
            'active_broadcasting' => IslamicContentProvider::whereIn('licensing_status', ['ACTIVE_BROADCASTING', 'AGREEMENT_SIGNED'])->count(),
            'virtual_channels_count' => $islamicChannels->count(),
            'total_upcoming_schedules' => ScheduleEntry::whereIn('channel_id', $islamicChannels->pluck('id'))->where('end_time', '>', $now)->count(),
        ];

        return view('islamic.index', compact('providers', 'islamicChannels', 'channelSchedules', 'stats'));
    }
}
