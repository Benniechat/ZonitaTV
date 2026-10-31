<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ContentComplaint;
use App\Models\ContentRight;
use App\Models\ProblemReport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RightsAuditController extends Controller
{
    /**
     * Compliance & Rights Audit log viewer.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status');

        $now = now();
        $totalChannels = Channel::count();
        $totalRights = ContentRight::count();

        // Expiring cohorts
        $expiring7Days = ContentRight::where('status', 'cleared')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$now, $now->copy()->addDays(7)])
            ->count();

        $expiring30Days = ContentRight::where('status', 'cleared')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$now, $now->copy()->addDays(30)])
            ->count();

        $expiredCount = ContentRight::where(function ($q) use ($now) {
            $q->where('status', 'expired')
                ->orWhere(fn ($sq) => $sq->whereNotNull('expires_at')->where('expires_at', '<=', $now));
        })->count();

        $clearedCount = ContentRight::where('status', 'cleared')
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })->count();

        // Compliance rate: percentage of channels with cleared active rights or original statutory mode
        $compliantChannelsCount = Channel::where(function ($q) {
            $q->where('is_virtual', true)
                ->orWhere('playback_mode', 'zoniva_original')
                ->orWhereHas('rights', function ($rq) {
                    $rq->where('status', 'cleared')
                        ->where(function ($sq) {
                            $sq->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
        })->count();

        $complianceRate = $totalChannels > 0
            ? round(($compliantChannelsCount / $totalChannels) * 100, 1)
            : 100.0;

        // Licensing Types Distribution
        $licenseTypes = ContentRight::selectRaw('classification, count(*) as count')
            ->groupBy('classification')
            ->pluck('count', 'classification')
            ->toArray();

        // Filtered Rights Query
        $rightsQuery = ContentRight::with('channel')->latest('effective_from');

        if ($statusFilter === 'expiring_soon') {
            $rightsQuery->where('status', 'cleared')
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [$now, $now->copy()->addDays(30)]);
        } elseif ($statusFilter === 'expired') {
            $rightsQuery->where(function ($q) use ($now) {
                $q->where('status', 'expired')
                    ->orWhere(fn ($sq) => $sq->whereNotNull('expires_at')->where('expires_at', '<=', $now));
            });
        } elseif ($statusFilter === 'cleared') {
            $rightsQuery->where('status', 'cleared')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now));
        } elseif ($statusFilter === 'pending') {
            $rightsQuery->where('status', 'pending_review');
        }

        $rights = $rightsQuery->paginate(20)->withQueryString();

        // Enforcement & Takedown Audit Log
        $recentComplaints = ContentComplaint::with(['channel', 'video'])
            ->latest()
            ->take(6)
            ->get();

        $recentProblemReports = ProblemReport::with('channel')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.compliance.index', compact(
            'statusFilter',
            'totalChannels',
            'totalRights',
            'clearedCount',
            'expiring7Days',
            'expiring30Days',
            'expiredCount',
            'compliantChannelsCount',
            'complianceRate',
            'licenseTypes',
            'rights',
            'recentComplaints',
            'recentProblemReports'
        ));
    }

    /**
     * Remediate licensing rights (extend or clear).
     */
    public function remediate(Request $request, ContentRight $right): RedirectResponse
    {
        $action = $request->input('action', 'extend_1_year');

        match ($action) {
            'extend_1_year' => $right->update([
                'status' => 'cleared',
                'expires_at' => ($right->expires_at && Carbon::parse($right->expires_at)->isFuture())
                    ? Carbon::parse($right->expires_at)->addYear()
                    : now()->addYear(),
            ]),
            'mark_perpetual' => $right->update([
                'status' => 'cleared',
                'expires_at' => null,
            ]),
            'revoke' => $right->update([
                'status' => 'expired',
                'expires_at' => now(),
            ]),
            default => null,
        };

        return back()->with('success', "Licensing right for {$right->channel->name} successfully remediated ({$action}).");
    }
}
