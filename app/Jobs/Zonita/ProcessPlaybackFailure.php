<?php

namespace App\Jobs\Zonita;

use App\Models\PlaybackFailure;
use App\Models\SystemAlert;
use App\Services\Zonita\PlaybackContinuityService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPlaybackFailure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $channelId,
        public string $youtubeId,
        public string $errorCode,
        public ?string $sessionId = null,
        public ?string $userAgent = null,
        public ?string $ipCountry = null
    ) {}

    public function handle(PlaybackContinuityService $continuityService): void
    {
        // 1. Execute immediate continuity recovery & circuit breaker recording
        $continuityService->handlePlayerFailure(
            channelId: $this->channelId,
            youtubeId: $this->youtubeId,
            errorCode: $this->errorCode,
            sessionId: $this->sessionId,
            userAgent: $this->userAgent,
            ipCountry: $this->ipCountry
        );

        // 2. Telemetry analysis: check failure rate in the past 15 minutes
        $fifteenMinutesAgo = Carbon::now()->subMinutes(15);
        $recentFailuresCount = PlaybackFailure::where('channel_id', $this->channelId)
            ->where('created_at', '>=', $fifteenMinutesAgo)
            ->count();

        if ($recentFailuresCount >= 5) {
            Log::alert("High playback failure frequency detected on Channel ID [{$this->channelId}]: {$recentFailuresCount} incidents in 15 minutes.");

            $existingAlert = SystemAlert::where('channel_id', $this->channelId)
                ->where('alert_type', 'stream_degraded')
                ->where('is_resolved', false)
                ->first();

            if (! $existingAlert) {
                $alert = SystemAlert::create([
                    'channel_id' => $this->channelId,
                    'level' => 'critical',
                    'alert_type' => 'stream_degraded',
                    'message' => "Channel experiencing degraded playout: {$recentFailuresCount} playback failures within 15 minutes.",
                    'is_resolved' => false,
                    'created_at' => now(),
                ]);

                NotifyAdministrators::dispatch($alert);
            }
        }
    }
}
