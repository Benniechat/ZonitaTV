<?php

namespace App\Services\Sports;

use App\Models\BroadcastSession;
use Illuminate\Support\Facades\Log;

class BroadcastRecoveryService
{
    public function __construct(
        protected AutomatedBroadcastController $controller,
        protected PlaybackHealthMonitor $monitor
    ) {}

    /**
     * Inspect all active broadcast sessions and apply automated recovery if failures exceed thresholds.
     *
     * @return array{remediated: int, fallback_triggered: int}
     */
    public function evaluateAndRecover(): array
    {
        $activeSessions = BroadcastSession::whereNotIn('current_state', ['completed', 'failed'])->get();
        $remediated = 0;
        $fallbackTriggered = 0;

        foreach ($activeSessions as $session) {
            $health = $this->monitor->checkSessionHealth($session);

            // If session failed health check
            if ($health['status'] === 'failed') {
                $consecutiveFailures = $session->failures()
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->count();

                if ($consecutiveFailures >= 3 && $session->current_state !== 'fallback') {
                    Log::warning("BroadcastRecoveryService: Session #{$session->id} exceeded failure threshold ({$consecutiveFailures}). Engaging fallback.");
                    $this->controller->triggerEmergencyFallback(
                        $session,
                        "Automatic recovery: {$consecutiveFailures} consecutive probe failures in last 5 minutes"
                    );
                    $fallbackTriggered++;
                }
            } elseif ($health['status'] === 'healthy' && $session->current_state === 'fallback') {
                // Recovered from fallback
                Log::info("BroadcastRecoveryService: Session #{$session->id} stream restored. Resuming on air.");
                $session->transitionTo('on_air', 'Automatic recovery: Primary stream restored and stable');
                $session->failures()->where('resolved', false)->update(['resolved' => true]);
                $remediated++;
            }
        }

        return [
            'remediated' => $remediated,
            'fallback_triggered' => $fallbackTriggered,
        ];
    }
}
