<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use App\Models\SystemAlert;
use App\Services\AlertNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemAlertsAdminController extends Controller
{
    public function __construct(
        protected AlertNotificationService $alertService
    ) {}

    /**
     * Master System Alerts monitor.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'active');
        $level = $request->input('level');
        $type = $request->input('type');

        $query = SystemAlert::with('channel')->latest('created_at');

        if ($status === 'active') {
            $query->where('is_resolved', false);
        } elseif ($status === 'resolved') {
            $query->where('is_resolved', true);
        }

        if ($level) {
            $query->where('level', $level);
        }

        if ($type) {
            $query->where('alert_type', $type);
        }

        $alerts = $query->paginate(25)->withQueryString();

        $counts = [
            'total' => SystemAlert::count(),
            'active' => SystemAlert::where('is_resolved', false)->count(),
            'critical' => SystemAlert::where('is_resolved', false)->where('level', 'critical')->count(),
            'warning' => SystemAlert::where('is_resolved', false)->where('level', 'warning')->count(),
            'resolved' => SystemAlert::where('is_resolved', true)->count(),
        ];

        return view('admin.alerts.index', compact('alerts', 'counts', 'status', 'level', 'type'));
    }

    /**
     * Mark an alert resolved.
     */
    public function resolve(Request $request, SystemAlert $alert): RedirectResponse
    {
        $this->alertService->resolveAlert($alert);

        return back()->with('success', "Alert #{$alert->id} resolved successfully.");
    }

    /**
     * Mass resolve active alerts.
     */
    public function resolveAll(Request $request): RedirectResponse
    {
        $activeAlerts = SystemAlert::where('is_resolved', false)->get();
        foreach ($activeAlerts as $alert) {
            $this->alertService->resolveAlert($alert);
        }

        return back()->with('success', "All {$activeAlerts->count()} active alerts marked resolved.");
    }

    /**
     * Community Broadcast & WhatsApp Alerts Hub.
     */
    public function community(Request $request): View
    {
        $subscriberCount = NewsletterSubscription::where('is_active', true)->count();
        $recentBroadcasts = SystemAlert::where('alert_type', 'whatsapp_broadcast')->latest('created_at')->take(10)->get();

        return view('admin.alerts.community', compact('subscriberCount', 'recentBroadcasts'));
    }

    /**
     * Dispatch WhatsApp Community Guide Broadcast alert.
     */
    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group' => ['required', 'string', 'in:all,lagos,diaspora,vip_viewers'],
            'custom_message' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->alertService->dispatchWhatsAppGuideAlert(
            $validated['target_group'],
            $validated['custom_message'] ?? null
        );

        return back()->with('success', "Community Guide Alert dispatched to {$result['recipients_count']} WhatsApp subscribers.");
    }
}
