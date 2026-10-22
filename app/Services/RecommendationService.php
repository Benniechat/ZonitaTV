<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Collection;

class RecommendationService
{
    protected RightsEligibilityService $rightsService;

    public function __construct(RightsEligibilityService $rightsService)
    {
        $this->rightsService = $rightsService;
    }

    /**
     * Implements blueprint formula:
     * R = 0.40I + 0.25V + 0.15F + 0.10L + 0.10N
     */
    public function rankChannelsForUser(?User $user, int $limit = 12, string $clientCountry = 'NGA'): Collection
    {
        $candidates = Channel::with(['categories', 'latestRights'])
            ->where('is_active', true)
            ->where('is_published', true)
            ->get()
            ->filter(fn ($channel) => $this->rightsService->canList($channel, $clientCountry));

        if (! $user || ! $user->preferences) {
            // Unauthenticated Guest: Rank by Featured flag, Display order, and Healthy status
            return $candidates->sortByDesc(function ($c) {
                return ($c->is_featured ? 50 : 0) + ($c->is_technically_healthy ? 20 : 0) + $c->display_order;
            })->take($limit);
        }

        $prefs = $user->preferences;
        $userCatIds = $prefs->preferred_categories ?? [];
        $userLangs = $prefs->preferred_languages ?? ['en'];
        $favouriteChannelIds = $user->favourites()->pluck('channel_id')->toArray();

        return $candidates->map(function ($channel) use ($userCatIds, $userLangs, $favouriteChannelIds) {
            $catIds = $channel->categories->pluck('id')->toArray();

            // I: Explicit Interest match (0-100)
            $overlap = count(array_intersect($userCatIds, $catIds));
            $interestScore = ! empty($userCatIds) ? min(100, ($overlap / max(1, count($userCatIds))) * 100) : 50;

            // V: Viewing interaction score (0-100)
            $viewingScore = 50;

            // F: Favourites match (100 if saved, 0 otherwise)
            $favScore = in_array($channel->id, $favouriteChannelIds) ? 100 : 0;

            // L: Language compatibility (0-100)
            $langScore = in_array($channel->language_code, $userLangs) ? 100 : 25;

            // N: Content freshness & health (0-100)
            $freshnessScore = $channel->is_technically_healthy ? 90 : 30;

            // Formula: R = 0.40I + 0.25V + 0.15F + 0.10L + 0.10N
            $score = (0.40 * $interestScore)
                   + (0.25 * $viewingScore)
                   + (0.15 * $favScore)
                   + (0.10 * $langScore)
                   + (0.10 * $freshnessScore);

            $channel->recommendation_score = round($score, 1);

            return $channel;
        })
            ->sortByDesc('recommendation_score')
            ->take($limit);
    }
}
