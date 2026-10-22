<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
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
}
