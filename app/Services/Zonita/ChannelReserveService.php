<?php

namespace App\Services\Zonita;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;

class ChannelReserveService
{
    /**
     * Checks if channel has sufficient verified reserve programmes.
     */
    public function getReserveStatus(Channel $channel): array
    {
        $minRequired = $channel->sourceRule?->min_reserve_count ?? 5;
        $categorySlug = $channel->categories->first()?->slug;

        $availableReserveCount = Video::eligibleForScheduling()
            ->where(function ($q) use ($categorySlug) {
                if ($categorySlug) {
                    $q->where('category_slug', $categorySlug);
                }
            })
            ->count();

        return [
            'channel_id' => $channel->id,
            'channel_slug' => $channel->slug,
            'min_required' => $minRequired,
            'current_reserve_count' => $availableReserveCount,
            'is_sufficient' => $availableReserveCount >= $minRequired,
        ];
    }

    /**
     * Finds the best immediate verified fallback video for a failed programme.
     */
    public function getFallbackVideo(Channel $channel, ?int $failedVideoId = null): ?Video
    {
        $categorySlug = $channel->categories->first()?->slug;

        // 1. Try an evergreen video in the same category
        $fallback = Video::eligibleForScheduling()
            ->when($failedVideoId, fn ($q) => $q->where('id', '!=', $failedVideoId))
            ->when($categorySlug, fn ($q) => $q->where('category_slug', $categorySlug))
            ->where('is_evergreen', true)
            ->orderByDesc('confidence_score')
            ->first();

        // 2. Fallback to any eligible approved video in this category
        if (! $fallback && $categorySlug) {
            $fallback = Video::eligibleForScheduling()
                ->when($failedVideoId, fn ($q) => $q->where('id', '!=', $failedVideoId))
                ->where('category_slug', $categorySlug)
                ->orderByDesc('confidence_score')
                ->first();
        }

        // 3. Fallback to channel's designated fallback_video_id
        if (! $fallback && $channel->fallback_video_id) {
            $fallback = Video::where(function ($q) use ($channel) {
                $q->where('id', $channel->fallback_video_id)
                    ->orWhere('youtube_id', $channel->fallback_video_id);
            })
                ->where('is_embeddable', true)
                ->first();
        }

        return $fallback;
    }

    /**
     * Returns all verified reserve candidates for a channel.
     */
    public function getReservePool(Channel $channel, int $limit = 10): Collection
    {
        $categorySlug = $channel->categories->first()?->slug;

        return Video::eligibleForScheduling()
            ->when($categorySlug, fn ($q) => $q->where('category_slug', $categorySlug))
            ->orderByDesc('is_evergreen')
            ->orderByDesc('confidence_score')
            ->take($limit)
            ->get();
    }
}
