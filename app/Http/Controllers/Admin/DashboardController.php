<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ContentRight;
use App\Models\NewsletterSubscription;
use App\Models\ProblemReport;
use App\Models\ViewingEvent;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'total_channels' => Channel::count(),
            'published_channels' => Channel::where('is_published', true)->count(),
            'pending_rights_review' => ContentRight::where('status', 'pending_review')->count(),
            'expired_rights' => ContentRight::where(function ($q) {
                $q->where('status', 'expired')
                    ->orWhere(fn ($sq) => $sq->whereNotNull('expires_at')->where('expires_at', '<', now()));
            })->count(),
            'unhealthy_streams' => Channel::where('is_technically_healthy', false)->count(),
            'subscribers_count' => NewsletterSubscription::where('is_active', true)->count(),
            'open_reports' => ProblemReport::where('status', 'new')->count(),
            'total_page_views' => ViewingEvent::where('event_type', 'page_view')->count(),
        ];

        $recentReports = ProblemReport::with('channel')->latest()->take(5)->get();
        $recentChannels = Channel::with(['categories', 'latestRights'])->latest()->take(5)->get();

        return view('admin.dashboard', compact('metrics', 'recentReports', 'recentChannels'));
    }
}
