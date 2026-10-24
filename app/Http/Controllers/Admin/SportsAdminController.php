<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastFailure;
use App\Models\BroadcastSession;
use App\Models\SportsFixture;
use App\Services\Sports\AutomatedBroadcastController;
use App\Services\Sports\BroadcastVerificationService;
use App\Services\Sports\FixtureDiscoveryService;
use App\Services\Sports\MatchSchedulingService;
use App\Services\Sports\PlaybackHealthMonitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SportsAdminController extends Controller
{
    public function __construct(
        protected AutomatedBroadcastController $broadcaster,
        protected PlaybackHealthMonitor $healthMonitor,
        protected FixtureDiscoveryService $discovery,
        protected BroadcastVerificationService $verifier,
        protected MatchSchedulingService $scheduler
    ) {}

    /**
     * Display the Sports Master Control Room (MCR) dashboard.
     */
    public function index(): View
    {
        $activeSessions = BroadcastSession::with(['fixture.homeTeam', 'fixture.awayTeam', 'fixture.competition', 'fixture.sportyBroadcast'])
            ->whereNotIn('current_state', ['completed', 'failed'])
            ->latest('created_at')
            ->get();

        $backendStatus = $this->broadcaster->getBackend()->getPlayoutStatus();
        $healthResults = $this->healthMonitor->checkAllActiveSessions();

        $recentFixtures = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->orderBy('utc_kickoff', 'asc')
            ->where('utc_kickoff', '>=', now()->subHours(6))
            ->take(20)
            ->get();

        $recentFailures = BroadcastFailure::with(['session.fixture'])
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_fixtures' => SportsFixture::count(),
            'live_fixtures' => SportsFixture::live()->count(),
            'upcoming_fixtures' => SportsFixture::upcoming()->count(),
            'confirmed_sportytv' => SportsFixture::withSportyTV()->count(),
            'active_sessions' => $activeSessions->count(),
            'backend_connected' => $backendStatus['connected'] ?? false,
        ];

        return view('admin.sports.index', compact(
            'activeSessions',
            'backendStatus',
            'healthResults',
            'recentFixtures',
            'recentFailures',
            'stats'
        ));
    }

    /**
     * Manually trigger fixture discovery across all competitions.
     */
    public function syncFixtures(): RedirectResponse
    {
        $result = $this->discovery->syncAllCompetitions();

        return redirect()->route('admin.sports.index')
            ->with('success', "Discovered {$result['new_fixtures']} new fixtures and updated {$result['updated_fixtures']} across active competitions.");
    }

    /**
     * Manually trigger SportyTV broadcast verification.
     */
    public function verifyBroadcasts(): RedirectResponse
    {
        $result = $this->verifier->verifyUpcomingFixtures();

        return redirect()->route('admin.sports.index')
            ->with('success', "SportyTV verification complete: {$result['confirmed']} confirmed broadcasts, {$result['mode_c_permitted']} Mode C rebroadcasts permitted.");
    }

    /**
     * Synchronize upcoming fixtures with the 24/7 linear channel timetable.
     */
    public function syncSchedule(): RedirectResponse
    {
        $result = $this->scheduler->syncFixtureSchedule();

        return redirect()->route('admin.sports.index')
            ->with('success', "EPG synchronization complete: {$result['scheduled']} fixtures scheduled, {$result['updated']} updated.");
    }

    /**
     * Trigger immediate emergency fallback on an active session.
     */
    public function emergencyFallback(BroadcastSession $session): RedirectResponse
    {
        $this->broadcaster->triggerEmergencyFallback($session, 'Emergency fallback manually triggered from Admin Control Hub');

        return redirect()->route('admin.sports.index')
            ->with('success', "Emergency fallback activated for Session #{$session->id}. Backend switched to holding loop.");
    }

    /**
     * Emergency kill-switch: Immediately revoke Mode C / Mode B and force Mode A (External Link).
     */
    public function forceModeA(BroadcastSession $session): RedirectResponse
    {
        $session->update(['operating_mode' => 'mode_a_link']);
        $session->fixture?->sportyBroadcast?->update(['operating_mode' => 'mode_a_link']);
        $session->transitionTo('fallback', 'Emergency override: Force Mode A (Official Link) engaged. Retransmission terminated.');
        $this->broadcaster->getBackend()->switchToFallback('Admin forced Mode A');

        return redirect()->route('admin.sports.index')
            ->with('success', "Session #{$session->id} forced to Mode A (Official Link). Retransmission stopped.");
    }

    /**
     * Toggle fixture broadcast operating mode.
     */
    public function toggleMode(Request $request, SportsFixture $fixture): RedirectResponse
    {
        $broadcast = $fixture->sportyBroadcast;
        if (! $broadcast) {
            return redirect()->route('admin.sports.index')->with('error', 'Fixture has no broadcast entry.');
        }

        $newMode = $request->input('mode', 'mode_a_link');
        if ($newMode === 'mode_c_rebroadcast' && ! $broadcast->isAuthorisedForRebroadcasting()) {
            return redirect()->route('admin.sports.index')->with('error', 'Cannot enable Mode C without an active Rights Ledger authorization.');
        }

        $broadcast->update(['operating_mode' => $newMode]);

        return redirect()->route('admin.sports.index')
            ->with('success', "Fixture #{$fixture->id} mode updated to '{$newMode}'.");
    }
}
