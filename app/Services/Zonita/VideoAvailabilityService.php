<?php

namespace App\Services\Zonita;

use App\Models\Video;
use App\Models\VideoVerificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoAvailabilityService
{
    /**
     * Cache duration before re-verifying an approved video.
     */
    protected int $reverifyHours = 24;

    /**
     * Max consecutive failures before circuit breaker trips into cooldown.
     */
    protected int $circuitBreakerThreshold = 3;

    /**
     * Cooldown duration in minutes when circuit breaker trips.
     */
    protected int $cooldownMinutes = 120;

    /**
     * Verifies technical eligibility of a candidate or existing video.
     */
    public function verifyVideo(Video $video, bool $force = false): array
    {
        // Skip if recently verified, approved, and not forced
        if (! $force && $video->availability_status === 'approved' && $video->last_verified_at && $video->last_verified_at->diffInHours(now()) < $this->reverifyHours) {
            return [
                'video_id' => $video->id,
                'status' => $video->availability_status,
                'is_playable' => $video->isPlayable(),
                'cached' => true,
            ];
        }

        $youtubeId = $video->youtube_id;
        $oembedUrl = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$youtubeId}&format=json";

        $httpCode = 0;
        $statusResult = 'discovered';
        $isEmbeddable = false;
        $details = [];

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
            ])->timeout(6)->get($oembedUrl);

            $httpCode = $response->status();

            if ($httpCode === 200) {
                $statusResult = 'approved';
                $isEmbeddable = true;
                $details = $response->json() ?? [];

                // Update title & author if discovered newly
                if (! empty($details['title']) && empty($video->title)) {
                    $video->title = $details['title'];
                }
                if (! empty($details['author_name']) && empty($video->channel_title)) {
                    $video->channel_title = $details['author_name'];
                }

                // Restore confidence on successful verification
                $video->consecutive_failures = 0;
                $video->confidence_score = 100;
                $video->cooldown_until = null;
            } elseif ($httpCode === 401) {
                // 401 Unauthorized = Embedding disabled by owner
                $statusResult = 'embedding_disabled';
                $isEmbeddable = false;
                $video->confidence_score = 0;
            } elseif ($httpCode === 404) {
                // 404 Not Found = Video deleted or removed
                $statusResult = 'deleted_or_missing';
                $isEmbeddable = false;
                $video->confidence_score = 0;
            } else {
                $statusResult = 'temporarily_unavailable';
                $video->confidence_score = max(0, $video->confidence_score - 20);
            }
        } catch (\Exception $e) {
            Log::warning("Verification error for video {$youtubeId}: {$e->getMessage()}");
            $statusResult = 'temporarily_unavailable';
            $details = ['exception' => $e->getMessage()];
            $video->confidence_score = max(0, $video->confidence_score - 10);
        }

        $video->availability_status = $statusResult;
        $video->is_embeddable = $isEmbeddable;
        $video->last_verified_at = now();
        $video->save();

        // Audit log
        VideoVerificationLog::create([
            'video_id' => $video->id,
            'check_type' => 'oembed',
            'http_code' => $httpCode,
            'status_result' => $statusResult,
            'raw_details' => $details,
            'created_at' => now(),
        ]);

        return [
            'video_id' => $video->id,
            'status' => $statusResult,
            'is_playable' => $video->isPlayable(),
            'confidence' => $video->confidence_score,
            'cached' => false,
        ];
    }

    /**
     * Trip circuit breaker when playback failure is reported from viewer client.
     */
    public function recordPlaybackFailure(Video $video, string $errorCode = 'playback_error'): void
    {
        $video->increment('consecutive_failures');
        $video->confidence_score = max(0, $video->confidence_score - 25);

        if ($video->consecutive_failures >= $this->circuitBreakerThreshold) {
            $video->availability_status = 'temporarily_unavailable';
            $video->cooldown_until = now()->addMinutes($this->cooldownMinutes);
            Log::warning("Circuit breaker tripped for video {$video->youtube_id} after {$video->consecutive_failures} failures. Cooldown until {$video->cooldown_until}");
        }

        if (in_array($errorCode, ['101', '150'])) {
            // Owner embedding blocked
            $video->availability_status = 'embedding_disabled';
            $video->is_embeddable = false;
            $video->confidence_score = 0;
        } elseif ($errorCode === '100') {
            // Video deleted / missing
            $video->availability_status = 'deleted_or_missing';
            $video->is_embeddable = false;
            $video->confidence_score = 0;
        }

        $video->save();

        VideoVerificationLog::create([
            'video_id' => $video->id,
            'check_type' => 'player_event',
            'http_code' => null,
            'status_result' => $video->availability_status,
            'raw_details' => ['error_code' => $errorCode, 'consecutive_failures' => $video->consecutive_failures],
            'created_at' => now(),
        ]);
    }
}
