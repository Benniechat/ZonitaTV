<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ContentRight;
use App\Models\NewsletterSubscription;
use App\Models\ProblemReport;
use App\Models\ScheduleEntry;
use App\Models\ViewingEvent;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();
        $totalChannels = Channel::count();
        $healthyChannels = Channel::where('is_technically_healthy', true)->count();
        $unhealthyChannels = Channel::where('is_technically_healthy', false)->count();

        $uptimePercentage = $totalChannels > 0
            ? round(($healthyChannels / $totalChannels) * 100, 1)
            : 100.0;

        $liveBroadcastsCount = ScheduleEntry::where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->count();

        $pendingRights = ContentRight::where('status', 'pending_review')->count();

        $expiredRights = ContentRight::where(function ($q) use ($now) {
            $q->where('status', 'expired')
                ->orWhere(fn ($sq) => $sq->whereNotNull('expires_at')->where('expires_at', '<', $now));
        })->count();

        $compliantChannelsCount = Channel::where(function ($q) use ($now) {
            $q->where('is_virtual', true)
                ->orWhere('playback_mode', 'zoniva_original')
                ->orWhereHas('rights', function ($rq) use ($now) {
                    $rq->where('status', 'cleared')
                        ->where(function ($sq) use ($now) {
                            $sq->whereNull('expires_at')
                                ->orWhere('expires_at', '>', $now);
                        });
                });
        })->count();

        $complianceScore = $totalChannels > 0
            ? round(($compliantChannelsCount / $totalChannels) * 100, 1)
            : 100.0;

        $playbackStartsToday = ViewingEvent::where('event_type', 'playback_started')
            ->where('created_at', '>=', $now->copy()->startOfDay())
            ->count();

        $totalViewsToday = ViewingEvent::where('created_at', '>=', $now->copy()->startOfDay())->count();

        $metrics = [
            'total_channels' => $totalChannels,
            'published_channels' => Channel::where('is_published', true)->count(),
            'virtual_channels' => Channel::where('is_virtual', true)->count(),
            'healthy_channels' => $healthyChannels,
            'unhealthy_streams' => $unhealthyChannels,
            'uptime_percentage' => $uptimePercentage,
            'active_live_broadcasts' => $liveBroadcastsCount,
            'pending_rights_review' => $pendingRights,
            'expired_rights' => $expiredRights,
            'compliance_score' => $complianceScore,
            'playback_starts_today' => $playbackStartsToday,
            'total_views_today' => $totalViewsToday,
            'total_page_views' => ViewingEvent::where('event_type', 'page_view')->count(),
            'subscribers_count' => NewsletterSubscription::where('is_active', true)->count(),
            'open_reports' => ProblemReport::where('status', 'new')->count(),
        ];

        $recentReports = ProblemReport::with('channel')->latest()->take(5)->get();
        $recentChannels = Channel::with(['categories', 'latestRights'])->latest()->take(6)->get();

        $onAirSchedules = ScheduleEntry::where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->with(['channel', 'video'])
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('metrics', 'recentReports', 'recentChannels', 'onAirSchedules'));
    }
}
