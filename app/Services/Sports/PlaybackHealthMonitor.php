<?php

namespace App\Services\Sports;

use App\Models\BroadcastSession;
use Exception;
use Illuminate\Support\Facades\Http;

class PlaybackHealthMonitor
{
    /**
     * Check health of all currently active broadcast sessions.
     *
     * @return array<string, mixed>
     */
    public function checkAllActiveSessions(): array
    {
        $activeSessions = BroadcastSession::with(['fixture.sportyBroadcast', 'failures'])
            ->whereNotIn('current_state', ['completed', 'failed'])
            ->get();

        $results = [
            'checked' => $activeSessions->count(),
            'healthy' => 0,
            'degraded' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($activeSessions as $session) {
            $status = $this->checkSessionHealth($session);
            $results['details'][] = $status;

            if ($status['status'] === 'healthy') {
                $results['healthy']++;
            } elseif ($status['status'] === 'degraded') {
                $results['degraded']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Inspect health of a specific broadcast session.
     *
     * @return array<string, mixed>
     */
    public function checkSessionHealth(BroadcastSession $session): array
    {
        $broadcast = $session->fixture?->sportyBroadcast;

        // If in Mode A (External Link), health is based on official destination availability
        if ($session->operating_mode === 'mode_a_link') {
            $isDestinationHealthy = $this->pingUrl($broadcast?->official_url ?? 'https://www.sporty.tv');

            return [
                'session_id' => $session->id,
                'fixture_id' => $session->fixture_id,
                'mode' => 'mode_a_link',
                'status' => $isDestinationHealthy ? 'healthy' : 'degraded',
                'failures_count' => $session->failures()->count(),
            ];
        }

        // If in Mode B (Embedded Player) or Mode C (Rebroadcast)
        $endpoint = $broadcast?->feed_endpoint ?? $broadcast?->embed_url;
        if (! $endpoint) {
            $session->recordFailure('missing_endpoint', 'Broadcast session missing live feed endpoint');

            return [
                'session_id' => $session->id,
                'fixture_id' => $session->fixture_id,
                'mode' => $session->operating_mode,
                'status' => 'failed',
                'error' => 'Missing endpoint',
                'failures_count' => $session->failures()->count(),
            ];
        }

        $isReachable = $this->pingUrl($endpoint);

        if (! $isReachable) {
            $session->recordFailure('feed_unreachable', "Stream endpoint '{$endpoint}' was unreachable during health probe");

            return [
                'session_id' => $session->id,
                'fixture_id' => $session->fixture_id,
                'mode' => $session->operating_mode,
                'status' => 'failed',
                'error' => 'Endpoint probe failed',
                'failures_count' => $session->failures()->count(),
            ];
        }

        return [
            'session_id' => $session->id,
            'fixture_id' => $session->fixture_id,
            'mode' => $session->operating_mode,
            'status' => 'healthy',
            'failures_count' => $session->failures()->count(),
        ];
    }

    /**
     * Check if a URL endpoint is reachable via lightweight HEAD or GET probe.
     */
    protected function pingUrl(string $url): bool
    {
        try {
            $response = Http::timeout(2)
                ->withoutVerifying()
                ->head($url);

            return $response->status() < 500;
        } catch (Exception $e) {
            return false;
        }
    }
}
