<?php

namespace App\Services\Sports;

use App\Models\BroadcastSession;
use App\Models\FixtureBroadcast;
use App\Models\SportsFixture;
use App\Services\Sports\Adapters\MockBroadcastAdapter;
use App\Services\Sports\Adapters\OBSBroadcastAdapter;
use App\Services\Sports\Adapters\VMixBroadcastAdapter;
use App\Services\Sports\Contracts\BroadcastBackendInterface;
use Illuminate\Support\Facades\Log;

class AutomatedBroadcastController
{
    protected BroadcastBackendInterface $backend;

    public function __construct(?BroadcastBackendInterface $backend = null)
    {
        if ($backend) {
            $this->backend = $backend;
        } else {
            $type = config('sports.obs.enabled') ? 'obs' : (config('sports.vmix.enabled') ? 'vmix' : 'mock');
            $this->backend = match ($type) {
                'obs' => new OBSBroadcastAdapter,
                'vmix' => new VMixBroadcastAdapter,
                default => new MockBroadcastAdapter,
            };
        }
    }

    public function getBackend(): BroadcastBackendInterface
    {
        return $this->backend;
    }

    public function setBackend(BroadcastBackendInterface $backend): self
    {
        $this->backend = $backend;

        return $this;
    }

    /**
     * Get or create the active broadcast session for a fixture.
     */
    public function getOrCreateSession(SportsFixture $fixture, FixtureBroadcast $broadcast): BroadcastSession
    {
        return BroadcastSession::firstOrCreate(
            [
                'fixture_id' => $fixture->id,
            ],
            [
                'title' => ($fixture->homeTeam?->name ?? 'Home').' vs '.($fixture->awayTeam?->name ?? 'Away'),
                'operating_mode' => $broadcast->operating_mode,
                'backend' => class_basename($this->backend),
                'current_state' => 'idle',
                'actual_start_at' => now(),
            ]
        );
    }

    /**
     * Prepare broadcast transmission (15 mins before kickoff).
     */
    public function prepareBroadcast(SportsFixture $fixture): ?BroadcastSession
    {
        $broadcast = $fixture->sportyBroadcast;
        if (! $broadcast || ! $broadcast->isConfirmed()) {
            Log::warning("AutomatedBroadcastController: Fixture #{$fixture->id} has no confirmed broadcast.");

            return null;
        }

        // Mode C Rebroadcast authorization gate
        if ($broadcast->operating_mode === 'mode_c_rebroadcast' && ! $broadcast->isAuthorisedForRebroadcasting()) {
            Log::warning("AutomatedBroadcastController: Fixture #{$fixture->id} attempted Mode C without rights. Downgrading to Mode A.");
            $broadcast->update(['operating_mode' => 'mode_a_link']);
        }

        $session = $this->getOrCreateSession($fixture, $broadcast);
        $session->transitionTo('preparing', 'Pre-match buffer initiated, preparing backend feed');

        if ($broadcast->operating_mode === 'mode_c_rebroadcast') {
            $this->backend->prepareSource($fixture, $broadcast);
            $this->backend->switchScene(config('sports.scenes.pre_match', 'PreMatchScene'));
            $this->backend->injectGraphics('match_countdown', [
                'home' => $fixture->homeTeam?->name,
                'away' => $fixture->awayTeam?->name,
                'kickoff' => $fixture->lagos_formatted,
            ]);
        }

        $session->transitionTo('pre_show', 'Pre-match scene and countdown graphics live');

        return $session;
    }

    /**
     * Go live on air with the match feed.
     */
    public function goLive(BroadcastSession $session): bool
    {
        $fixture = $session->fixture;
        $broadcast = $fixture?->sportyBroadcast;

        if (! $fixture || ! $broadcast) {
            return false;
        }

        if ($broadcast->operating_mode === 'mode_c_rebroadcast') {
            $success = $this->backend->startPlayout($fixture, $broadcast);
            if (! $success) {
                $session->recordFailure('backend_playout_failed', 'Failed to start playout on broadcast encoder');
                $session->transitionTo('fallback', 'Backend failed, switched to holding fallback');

                return false;
            }
        }

        $session->transitionTo('on_air', 'Live match transmission active');
        $fixture->update(['status' => 'live']);

        return true;
    }

    /**
     * Switch to halftime analysis / graphics.
     */
    public function enterHalftime(BroadcastSession $session): bool
    {
        $session->transitionTo('half_time', 'First half ended, displaying halftime analysis');

        if ($session->operating_mode === 'mode_c_rebroadcast') {
            return $this->backend->switchScene(config('sports.scenes.half_time', 'HalfTimeScene'));
        }

        return true;
    }

    /**
     * Conclude match broadcast and smoothly return to 24/7 evergreen rotation.
     */
    public function concludeBroadcast(BroadcastSession $session): bool
    {
        $session->transitionTo('post_show', 'Match ended, post-match wrap-up sequence');

        if ($session->operating_mode === 'mode_c_rebroadcast') {
            $this->backend->switchScene(config('sports.scenes.post_match', 'PostMatchScene'));
            sleep(1);
            $this->backend->stopPlayout();
        }

        $session->update([
            'ended_at' => now(),
        ]);

        $session->transitionTo('completed', 'Broadcast completed, reverted to 24/7 linear channel schedule');
        $session->fixture?->update(['status' => 'finished']);

        return true;
    }

    /**
     * Emergency fallback activation.
     */
    public function triggerEmergencyFallback(BroadcastSession $session, string $reason): bool
    {
        $session->recordFailure('emergency_manual_fallback', $reason);
        $session->transitionTo('fallback', "Emergency fallback activated: {$reason}");

        return $this->backend->switchToFallback($reason);
    }
}
