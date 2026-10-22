<?php

namespace App\Services\Zonita;

use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ContentRotationService
{
    /**
     * Filters candidate videos to prevent unwanted repetition according to channel rules.
     */
    public function filterEligibleCandidates(
        Collection $candidateVideos,
        Channel $channel,
        Carbon $scheduledStartTime,
        ?array $inMemoryRecentIds = null,
        ?string $inMemoryLastPublisher = null
    ): Collection {
        $rule = $channel->sourceRule;
        $cooldownHours = $rule?->repeat_cooldown_hours ?? 6;
        $cutoffTime = $scheduledStartTime->copy()->subHours($cooldownHours);

        if ($inMemoryRecentIds !== null) {
            $recentlyScheduledVideoIds = $inMemoryRecentIds;
            $lastPublisher = $inMemoryLastPublisher;
        } else {
            // IDs of videos scheduled within the cooldown window
            $recentlyScheduledVideoIds = ScheduleEntry::where('channel_id', $channel->id)
                ->where('start_time', '>=', $cutoffTime)
                ->where('start_time', '<=', $scheduledStartTime)
                ->whereIn('status', ['scheduled', 'playing', 'completed'])
                ->pluck('video_id')
                ->toArray();

            // Previous video's publisher to avoid consecutive identical publishers
            $lastEntry = ScheduleEntry::where('channel_id', $channel->id)
                ->where('start_time', '<', $scheduledStartTime)
                ->orderByDesc('start_time')
                ->with('video')
                ->first();

            $lastPublisher = $lastEntry?->video?->channel_title;
        }

        return $candidateVideos->filter(function (Video $video) use ($recentlyScheduledVideoIds, $lastPublisher) {
            // 1. Must not have played within the cooldown window
            if (in_array($video->id, $recentlyScheduledVideoIds)) {
                return false;
            }

            // 2. Deprioritize if exact same publisher as previous video (unless catalogue size is small)
            if ($lastPublisher && $video->channel_title === $lastPublisher) {
                // Keep if few candidates, but down-rank
                $video->relevance_score = max(10, $video->relevance_score - 20);
            }

            return true;
        })->sortByDesc(function (Video $video) {
            // Prioritize fresh over evergreen when both available, and sort by relevance
            $freshnessBoost = $video->is_evergreen ? 0 : 15;

            return $video->relevance_score + $freshnessBoost + ($video->confidence_score / 2);
        });
    }
}
