<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Zonita\UpdateVideoEligibility;
use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\PlaybackFailure;
use App\Models\ScheduleEntry;
use App\Models\SystemAlert;
use App\Models\Video;
use App\Services\Zonita\ChannelReserveService;
use App\Services\Zonita\ProgrammeScheduleService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastingAdminController extends Controller
{
    public function __construct(
        protected ChannelReserveService $reserveService,
        protected ProgrammeScheduleService $scheduleService
    ) {}

    /**
     * Dashboard overview of Zonita virtual broadcasting network.
     */
    public function index(): View
    {
        $virtualChannels = Channel::whereHas('sourceRule')
            ->with(['sourceRule', 'categories'])
            ->get()
            ->map(function ($channel) {
                $now = Carbon::now($channel->sourceRule?->timezone ?? 'Africa/Lagos');

                $activeEntry = ScheduleEntry::where('channel_id', $channel->id)
                    ->where('start_time', '<=', $now)
                    ->where('end_time', '>', $now)
                    ->with('video')
                    ->first();

                $futureEntriesCount = ScheduleEntry::where('channel_id', $channel->id)
                    ->where('end_time', '>', $now)
                    ->count();

                $maxScheduledEnd = ScheduleEntry::where('channel_id', $channel->id)
                    ->where('end_time', '>', $now)
                    ->max('end_time');

                $futureHours = $maxScheduledEnd
                    ? round($now->diffInMinutes(Carbon::parse($maxScheduledEnd, $now->timezone)) / 60, 1)
                    : 0;

                $reserveStatus = $this->reserveService->getReserveStatus($channel);

                return [
                    'model' => $channel,
                    'active_entry' => $activeEntry,
                    'future_count' => $futureEntriesCount,
                    'future_hours' => $futureHours,
                    'reserve_available' => $reserveStatus['current_reserve_count'],
                    'reserve_required' => $reserveStatus['min_required'],
                    'reserve_ok' => $reserveStatus['is_sufficient'],
                ];
            });

        $stats = [
            'total_channels' => $virtualChannels->count(),
            'total_videos' => Video::count(),
            'approved_videos' => Video::where('availability_status', 'approved')->count(),
            'total_scheduled_entries' => ScheduleEntry::count(),
            'unresolved_alerts' => SystemAlert::where('is_resolved', false)->count(),
            'total_failures' => PlaybackFailure::count(),
        ];

        $unresolvedAlerts = SystemAlert::with('channel')
            ->where('is_resolved', false)
            ->latest('id')
            ->take(10)
            ->get();

        $recentFailures = PlaybackFailure::with(['channel', 'video', 'replacementVideo'])
            ->latest('id')
            ->take(10)
            ->get();

        return view('admin.broadcasting.index', compact('virtualChannels', 'stats', 'unresolvedAlerts', 'recentFailures'));
    }

    /**
     * Regenerates the rolling schedule for a specific channel.
     */
    public function regenerateSchedule(Channel $channel): RedirectResponse
    {
        $this->scheduleService->generateRollingSchedule($channel, 7);

        return redirect()->route('admin.broadcasting.index')
            ->with('success', "7-Day rolling schedule regenerated for [{$channel->name}].");
    }

    /**
     * Dispatches video eligibility re-verification sweep.
     */
    public function reverifyVideos(): RedirectResponse
    {
        UpdateVideoEligibility::dispatch(batchLimit: 50, force: true);

        return redirect()->route('admin.broadcasting.index')
            ->with('success', 'Dispatched background verification sweep for candidate video catalogue.');
    }

    /**
     * Resolves a system alert.
     */
    public function resolveAlert(SystemAlert $alert): RedirectResponse
    {
        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'System alert marked as resolved.');
    }

    /**
     * Edit channel source & programming rules.
     */
    public function editRule(Channel $channel): View
    {
        $rule = $channel->sourceRule ?? new ChannelSourceRule(['channel_id' => $channel->id]);

        return view('admin.broadcasting.rules', compact('channel', 'rule'));
    }

    /**
     * Update channel source & programming rules.
     */
    public function updateRule(Request $request, Channel $channel): RedirectResponse
    {
        $validated = $request->validate([
            'search_keywords' => 'required|string',
            'excluded_keywords' => 'nullable|string',
            'approved_publishers' => 'nullable|string',
            'min_duration_minutes' => 'required|integer|min:1',
            'max_duration_minutes' => 'required|integer|min:2|gte:min_duration_minutes',
            'repeat_cooldown_hours' => 'required|integer|min:1',
            'min_reserve_count' => 'required|integer|min:1',
            'scheduling_mode' => 'required|in:continuous,blocks,hybrid',
            'priority_level' => 'nullable|in:premier_heavy,balanced,deep_catalog',
            'interstitial_ident_interval' => 'nullable|integer|min:1',
            'timezone' => 'required|string',
        ]);

        $keywords = array_filter(array_map('trim', explode(',', $validated['search_keywords'])));
        $excluded = ! empty($validated['excluded_keywords'])
            ? array_filter(array_map('trim', explode(',', $validated['excluded_keywords'])))
            : [];
        $publishers = ! empty($validated['approved_publishers'])
            ? array_filter(array_map('trim', explode(',', $validated['approved_publishers'])))
            : [];

        ChannelSourceRule::updateOrCreate(
            ['channel_id' => $channel->id],
            [
                'search_keywords' => $keywords,
                'excluded_keywords' => $excluded,
                'approved_publishers' => $publishers,
                'min_duration_minutes' => $validated['min_duration_minutes'],
                'max_duration_minutes' => $validated['max_duration_minutes'],
                'repeat_cooldown_hours' => $validated['repeat_cooldown_hours'],
                'min_reserve_count' => $validated['min_reserve_count'],
                'scheduling_mode' => $validated['scheduling_mode'],
                'priority_level' => $validated['priority_level'] ?? 'balanced',
                'interstitial_ident_interval' => $validated['interstitial_ident_interval'] ?? 4,
                'timezone' => $validated['timezone'],
            ]
        );

        return redirect()->route('admin.broadcasting.index')
            ->with('success', "Programming and scheduling rules updated for [{$channel->name}].");
    }
}
