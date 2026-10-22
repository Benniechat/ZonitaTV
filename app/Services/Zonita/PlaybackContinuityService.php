<?php

namespace App\Services\Zonita;

use App\Models\Channel;
use App\Models\PlaybackFailure;
use App\Models\ScheduleEntry;
use App\Models\Video;
use Illuminate\Support\Facades\Log;

class PlaybackContinuityService
{
    public function __construct(
        protected VideoAvailabilityService $availabilityService,
        protected ChannelReserveService $reserveService,
        protected ProgrammeScheduleService $scheduleService
    ) {}

    /**
     * Handles client-reported player failure (e.g. error code 100, 101, 150, or network fault).
     */
    public function handlePlayerFailure(
        int $channelId,
        string $youtubeId,
        string $errorCode,
        ?string $sessionId = null,
        ?string $userAgent = null,
        ?string $ipCountry = null
    ): array {
        $channel = Channel::findOrFail($channelId);
        $video = Video::where('youtube_id', $youtubeId)->first();

        Log::warning("Zonita Playback Failure detected on [{$channel->slug}] for video [{$youtubeId}] - Error code: {$errorCode}");

        if ($video) {
            // 1. Record in Circuit Breaker
            $this->availabilityService->recordPlaybackFailure($video, $errorCode);
        }

        // 2. Locate immediate verified replacement from reserve
        $fallbackVideo = $this->reserveService->getFallbackVideo($channel, $video?->id);

        // 3. Log playback failure incident
        PlaybackFailure::create([
            'video_id' => $video?->id ?? 1,
            'channel_id' => $channel->id,
            'error_code' => $errorCode,
            'session_id' => $sessionId,
            'user_agent' => $userAgent,
            'ip_country' => $ipCountry,
            'recovered_with_video_id' => $fallbackVideo?->id,
            'created_at' => now(),
        ]);

        // 4. Update the active schedule entry if matched
        if ($video) {
            $activeEntry = ScheduleEntry::where('channel_id', $channel->id)
                ->where('video_id', $video->id)
                ->where('status', 'playing')
                ->first();

            if ($activeEntry && $fallbackVideo) {
                $activeEntry->update([
                    'status' => 'failed',
                ]);

                // Create replacement entry
                ScheduleEntry::create([
                    'channel_id' => $channel->id,
                    'video_id' => $fallbackVideo->id,
                    'title' => '[Recovered] '.$fallbackVideo->title,
                    'description' => $fallbackVideo->description,
                    'format_type' => $fallbackVideo->format_type,
                    'start_time' => now(),
                    'end_time' => now()->addSeconds($fallbackVideo->duration_seconds),
                    'duration_seconds' => $fallbackVideo->duration_seconds,
                    'play_order' => $activeEntry->play_order + 1,
                    'status' => 'playing',
                    'is_fallback' => true,
                ]);
            }
        }

        if (! $fallbackVideo) {
            return [
                'status' => 'standby',
                'message' => 'No active replacement available. Entering branded standby state.',
                'channel_slug' => $channel->slug,
            ];
        }

        return [
            'status' => 'recovered',
            'replacement' => [
                'video_id' => $fallbackVideo->youtube_id,
                'title' => $fallbackVideo->title,
                'description' => $fallbackVideo->description,
                'duration_seconds' => $fallbackVideo->duration_seconds,
                'start_seconds' => 0,
            ],
            'channel_slug' => $channel->slug,
        ];
    }
}
