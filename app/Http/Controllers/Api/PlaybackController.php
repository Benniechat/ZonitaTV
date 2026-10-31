<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Services\Zonita\PlaybackContinuityService;
use App\Services\Zonita\ProgrammeScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaybackController extends Controller
{
    public function __construct(
        protected PlaybackContinuityService $continuityService,
        protected ProgrammeScheduleService $scheduleService
    ) {}

    /**
     * Client player reports a playback error (e.g. 100, 101, 150, network block).
     * The engine records the incident, updates circuit breaker, and immediately
     * returns a verified replacement from the reserve pool.
     */
    public function reportFailure(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'required|integer|exists:channels,id',
            'video_id' => 'required|string|max:32',
            'error_code' => 'required|string|max:32',
        ]);

        $sessionId = $request->session()->getId();
        $userAgent = $request->header('User-Agent');
        $ipCountry = $request->header('CF-IPCountry', 'NGA');

        $result = $this->continuityService->handlePlayerFailure(
            $validated['channel_id'],
            $validated['video_id'],
            $validated['error_code'],
            $sessionId,
            $userAgent,
            $ipCountry
        );

        return response()->json($result);
    }

    /**
     * Returns JSON EPG dataset for a channel.
     */
    public function getEpg(string $slug): JsonResponse
    {
        $channel = Channel::where('slug', $slug)->firstOrFail();
        $epg = $this->scheduleService->getEpgData($channel);

        return response()->json($epg);
    }

    /**
     * Returns real-time live playback status, current playing video, elapsed seconds,
     * channel telemetry, and next scheduled programme.
     */
    public function getLiveStatus(string $slug): JsonResponse
    {
        $channel = Channel::where('slug', $slug)->firstOrFail();

        $activeEntry = ScheduleEntry::with('video')
            ->where('channel_id', $channel->id)
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now())
            ->where('status', 'playing')
            ->first();

        if (! $activeEntry) {
            $activeEntry = ScheduleEntry::with('video')
                ->where('channel_id', $channel->id)
                ->where('start_time', '<=', now())
                ->where('end_time', '>', now())
                ->first();
        }

        $nextEntry = ScheduleEntry::with('video')
            ->where('channel_id', $channel->id)
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->first();

        $elapsedSeconds = $activeEntry ? abs((int) now()->diffInSeconds($activeEntry->start_time)) : 0;

        return response()->json([
            'is_live' => $activeEntry !== null || (bool) $channel->is_active,
            'channel' => [
                'id' => $channel->id,
                'name' => $channel->name,
                'slug' => $channel->slug,
                'channel_number' => $channel->channel_number,
                'brand_color' => $channel->brand_color,
                'watermark_logo_path' => $channel->watermark_logo_path,
                'is_virtual' => (bool) $channel->is_virtual,
            ],
            'current_entry' => $activeEntry ? [
                'id' => $activeEntry->id,
                'title' => $activeEntry->title,
                'description' => $activeEntry->description,
                'video_id' => $activeEntry->video?->youtube_id ?? (string) $activeEntry->video_id,
                'start_time' => $activeEntry->start_time->toIso8601String(),
                'end_time' => $activeEntry->end_time->toIso8601String(),
                'duration_seconds' => $activeEntry->duration_seconds,
                'elapsed_seconds' => $elapsedSeconds,
                'remaining_seconds' => max(0, $activeEntry->duration_seconds - $elapsedSeconds),
            ] : null,
            'next_programme' => $nextEntry ? [
                'id' => $nextEntry->id,
                'title' => $nextEntry->title,
                'start_time' => $nextEntry->start_time->toIso8601String(),
                'duration_seconds' => $nextEntry->duration_seconds,
            ] : null,
            'timestamp' => now()->timestamp,
        ]);
    }
}
