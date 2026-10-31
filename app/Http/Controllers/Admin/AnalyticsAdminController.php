<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastHistory;
use App\Models\Channel;
use App\Models\ViewingEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsAdminController extends Controller
{
    /**
     * Viewership analytics & engagement dashboard.
     */
    public function index(Request $request): View
    {
        $period = $request->input('period', '7d');
        $channelFilter = $request->input('channel_id');

        $startDate = match ($period) {
            'today' => now()->startOfDay(),
            '7d' => now()->subDays(7)->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            'all' => null,
            default => now()->subDays(7)->startOfDay(),
        };

        // Base query for viewing events in period
        $eventsQuery = ViewingEvent::query();
        if ($startDate) {
            $eventsQuery->where('created_at', '>=', $startDate);
        }
        if ($channelFilter) {
            $eventsQuery->where('channel_id', $channelFilter);
        }

        // Summary KPI Metrics
        $totalEvents = (clone $eventsQuery)->count();
        $playbackStarts = (clone $eventsQuery)->where('event_type', 'playback_started')->count();
        $pageViews = (clone $eventsQuery)->where('event_type', 'page_view')->count();
        $guideViews = (clone $eventsQuery)->where('event_type', 'guide_view')->count();
        $favouriteToggles = (clone $eventsQuery)->where('event_type', 'favourite_toggle')->count();
        $uniqueSessions = (clone $eventsQuery)->distinct('session_id')->count('session_id');
        $uniqueUsers = (clone $eventsQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id');

        // Top Performing Channels
        $topChannelsData = (clone $eventsQuery)
            ->where('event_type', 'playback_started')
            ->selectRaw('channel_id, count(*) as playback_count')
            ->groupBy('channel_id')
            ->orderByDesc('playback_count')
            ->take(8)
            ->get();

        $channelIds = $topChannelsData->pluck('channel_id')->toArray();
        $channelsMap = Channel::whereIn('id', $channelIds)->with('categories')->get()->keyBy('id');

        $topChannels = $topChannelsData->map(function ($row) use ($channelsMap, $playbackStarts) {
            $channel = $channelsMap->get($row->channel_id);
            $share = $playbackStarts > 0 ? round(($row->playback_count / $playbackStarts) * 100, 1) : 0;

            return [
                'channel' => $channel,
                'channel_name' => $channel?->name ?? 'Channel #'.$row->channel_id,
                'channel_number' => $channel?->channel_number ?? str_pad((string) $row->channel_id, 2, '0', STR_PAD_LEFT),
                'playback_count' => $row->playback_count,
                'share_percentage' => $share,
                'category' => $channel?->categories->first()?->name ?? 'General',
            ];
        });

        // Geographic Distribution by Country
        $geoDistribution = (clone $eventsQuery)
            ->selectRaw('ip_country, count(*) as event_count')
            ->groupBy('ip_country')
            ->orderByDesc('event_count')
            ->take(6)
            ->get()
            ->map(function ($row) use ($totalEvents) {
                $percentage = $totalEvents > 0 ? round(($row->event_count / $totalEvents) * 100, 1) : 0;

                return [
                    'country_code' => $row->ip_country ?: 'NGA',
                    'count' => $row->event_count,
                    'percentage' => $percentage,
                ];
            });

        // Broadcast Playout Telemetry Summary
        $broadcastQuery = BroadcastHistory::query();
        if ($startDate) {
            $broadcastQuery->where('scheduled_start_at', '>=', $startDate);
        }
        $totalBroadcasts = (clone $broadcastQuery)->count();
        $completedBroadcasts = (clone $broadcastQuery)->where('status', 'completed')->count();
        $totalBroadcastSessions = (clone $broadcastQuery)->sum('session_count');

        // Recent live activity feed
        $recentEvents = (clone $eventsQuery)
            ->with(['channel', 'user'])
            ->latest('created_at')
            ->take(12)
            ->get();

        $allChannels = Channel::orderBy('name')->get(['id', 'name', 'channel_number']);

        return view('admin.analytics.index', compact(
            'period',
            'channelFilter',
            'totalEvents',
            'playbackStarts',
            'pageViews',
            'guideViews',
            'favouriteToggles',
            'uniqueSessions',
            'uniqueUsers',
            'topChannels',
            'geoDistribution',
            'totalBroadcasts',
            'completedBroadcasts',
            'totalBroadcastSessions',
            'recentEvents',
            'allChannels'
        ));
    }

    /**
     * Export analytics data payload as JSON for reporting.
     */
    public function export(Request $request): JsonResponse
    {
        $period = $request->input('period', '30d');
        $startDate = match ($period) {
            'today' => now()->startOfDay(),
            '7d' => now()->subDays(7)->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            default => now()->subDays(30)->startOfDay(),
        };

        $totalEvents = ViewingEvent::where('created_at', '>=', $startDate)->count();
        $playbackStarts = ViewingEvent::where('created_at', '>=', $startDate)->where('event_type', 'playback_started')->count();

        $topChannels = ViewingEvent::where('created_at', '>=', $startDate)
            ->where('event_type', 'playback_started')
            ->selectRaw('channel_id, count(*) as playbacks')
            ->groupBy('channel_id')
            ->orderByDesc('playbacks')
            ->take(15)
            ->get();

        $countries = ViewingEvent::where('created_at', '>=', $startDate)
            ->selectRaw('ip_country, count(*) as events')
            ->groupBy('ip_country')
            ->orderByDesc('events')
            ->get();

        return response()->json([
            'report' => 'Zoniva TV Audience Analytics',
            'period' => $period,
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'total_events' => $totalEvents,
                'playback_starts' => $playbackStarts,
            ],
            'top_channels' => $topChannels,
            'countries' => $countries,
        ]);
    }
}
