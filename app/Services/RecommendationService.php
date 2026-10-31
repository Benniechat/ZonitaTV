<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Programme;
use App\Models\ScheduleEntry;
use App\Models\User;
use App\Models\ViewingEvent;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function __construct(
        protected RightsEligibilityService $rightsService
    ) {}

    /**
     * Implements weighted recommendation formula:
     * R = 0.40I + 0.25V + 0.15F + 0.10L + 0.10N
     *
     * I = Explicit Interest match (category preferences overlap)
     * V = Viewing interaction history (playback and guide engagement)
     * F = Favourites match (channel saved in user collection)
     * L = Language compatibility
     * N = Content freshness and technical stream health
     */
    public function rankChannelsForUser(?User $user, int $limit = 12, string $clientCountry = 'NGA'): Collection
    {
        $candidates = Channel::with(['categories', 'latestRights', 'primaryStream'])
            ->where('is_active', true)
            ->where('is_published', true)
            ->get()
            ->filter(fn ($channel) => $this->rightsService->canList($channel, $clientCountry));

        if (! $user || ! $user->preferences) {
            // Unauthenticated Guest: Rank by Featured flag, Technical health, and Display order
            return $candidates->map(function ($c) use ($clientCountry) {
                $score = ($c->is_featured ? 50.0 : 20.0)
                    + ($c->is_technically_healthy ? 25.0 : 5.0)
                    + min(25.0, ((int) $c->display_order) * 1.5);

                $c->recommendation_score = round($score, 1);

                if ($c->is_featured) {
                    $c->recommendation_tag = "Editor's Choice";
                    $c->recommendation_reason = 'Featured premium station on Zoniva TV';
                } elseif ($c->is_technically_healthy) {
                    $c->recommendation_tag = "Trending in {$clientCountry}";
                    $c->recommendation_reason = 'High-definition verified live stream';
                } else {
                    $c->recommendation_tag = 'Popular Station';
                    $c->recommendation_reason = 'Broadcasting 24/7 across Nigeria';
                }

                return $c;
            })->sortByDesc('recommendation_score')->values()->take($limit);
        }

        $prefs = $user->preferences;
        $userCatIds = $prefs->preferred_categories ?? [];
        $userLangs = $prefs->preferred_languages ?? ['en'];
        $favouriteChannelIds = $user->favourites()->pluck('channel_id')->toArray();

        // Compute viewing history heuristic from ViewingEvent
        $userViewingCounts = ViewingEvent::where('user_id', $user->id)
            ->selectRaw('channel_id, count(*) as total_events')
            ->groupBy('channel_id')
            ->pluck('total_events', 'channel_id')
            ->toArray();

        $hasViewingHistory = ! empty($userViewingCounts);
        $maxViewingEvents = $hasViewingHistory ? max($userViewingCounts) : 1;

        return $candidates->map(function ($channel) use (
            $userCatIds,
            $userLangs,
            $favouriteChannelIds,
            $userViewingCounts,
            $hasViewingHistory,
            $maxViewingEvents
        ) {
            $catIds = $channel->categories->pluck('id')->toArray();

            // I: Explicit Interest match (0-100)
            $commonCats = array_intersect($userCatIds, $catIds);
            $overlap = count($commonCats);
            $interestScore = ! empty($userCatIds) ? min(100.0, ($overlap / max(1, count($userCatIds))) * 100.0) : 50.0;

            // V: Viewing interaction score (0-100)
            $eventCount = $userViewingCounts[$channel->id] ?? 0;
            if ($hasViewingHistory) {
                $viewingScore = $eventCount > 0
                    ? min(100.0, round(($eventCount / max(1, $maxViewingEvents)) * 100.0))
                    : 20.0;
            } else {
                $viewingScore = 50.0; // neutral prior
            }

            // F: Favourites match (100 if saved, 0 otherwise)
            $isFav = in_array($channel->id, $favouriteChannelIds);
            $favScore = $isFav ? 100.0 : 0.0;

            // L: Language compatibility (0-100)
            $langScore = in_array($channel->language_code, $userLangs) ? 100.0 : 25.0;

            // N: Content freshness & health (0-100)
            $freshnessScore = $channel->is_technically_healthy ? 90.0 : 30.0;

            // Formula: R = 0.40I + 0.25V + 0.15F + 0.10L + 0.10N
            $score = (0.40 * $interestScore)
                   + (0.25 * $viewingScore)
                   + (0.15 * $favScore)
                   + (0.10 * $langScore)
                   + (0.10 * $freshnessScore);

            $channel->recommendation_score = round($score, 1);

            // Explainable recommendation reason & tag
            if ($isFav) {
                $channel->recommendation_tag = 'Saved Favourite';
                $channel->recommendation_reason = 'From your saved channel collection';
            } elseif ($eventCount > 0 && $viewingScore >= 50.0) {
                $channel->recommendation_tag = 'Frequently Watched';
                $channel->recommendation_reason = 'Based on your recent viewing interactions';
            } elseif ($overlap > 0) {
                $matchedCat = $channel->categories->firstWhere(fn ($c) => in_array($c->id, $userCatIds));
                $catName = $matchedCat?->name ?? 'Your Genres';
                $channel->recommendation_tag = "Top Pick for {$catName}";
                $channel->recommendation_reason = "Matches your interest in {$catName}";
            } elseif ($channel->is_featured) {
                $channel->recommendation_tag = 'Featured';
                $channel->recommendation_reason = 'Curated spotlight station on Zoniva TV';
            } else {
                $channel->recommendation_tag = 'Trending';
                $channel->recommendation_reason = 'Popular station with verified broadcast health';
            }

            return $channel;
        })
            ->sortByDesc('recommendation_score')
            ->values()
            ->take($limit);
    }

    /**
     * Similar Channel Matching Engine:
     * Calculates category overlap, language similarity, curation format, and technical stream health.
     */
    public function getSimilarChannels(Channel $channel, int $limit = 6, string $clientCountry = 'NGA'): Collection
    {
        $targetCatIds = $channel->categories->pluck('id')->toArray();
        $targetLang = $channel->language_code;
        $targetCuration = $channel->curation_type;
        $targetIsVirtual = (bool) $channel->is_virtual;

        $candidates = Channel::with(['categories', 'latestRights', 'primaryStream'])
            ->where('id', '!=', $channel->id)
            ->where('is_active', true)
            ->where('is_published', true)
            ->get()
            ->filter(fn ($c) => $this->rightsService->canList($c, $clientCountry));

        return $candidates->map(function ($candidate) use ($channel, $targetCatIds, $targetLang, $targetCuration, $targetIsVirtual) {
            $candidateCatIds = $candidate->categories->pluck('id')->toArray();
            $sharedCats = array_intersect($targetCatIds, $candidateCatIds);
            $sharedCount = count($sharedCats);

            // Category similarity (0 - 50 points)
            $catScore = ! empty($targetCatIds)
                ? round(($sharedCount / max(1, count($targetCatIds))) * 50.0)
                : 20.0;

            // Language similarity (0 - 25 points)
            $langScore = ($candidate->language_code === $targetLang) ? 25.0 : 5.0;

            // Format & curation similarity (0 - 15 points)
            $formatScore = 0.0;
            if ($candidate->curation_type === $targetCuration) {
                $formatScore += 8.0;
            }
            if ((bool) $candidate->is_virtual === $targetIsVirtual) {
                $formatScore += 7.0;
            }

            // Stream health bonus (0 - 10 points)
            $healthScore = $candidate->is_technically_healthy ? 10.0 : 2.0;

            $totalSimilarity = round($catScore + $langScore + $formatScore + $healthScore, 1);
            $candidate->similarity_score = $totalSimilarity;

            // Explainable similarity reason
            if ($sharedCount > 0) {
                $sharedCatNames = $channel->categories->whereIn('id', $sharedCats)->pluck('name')->implode(', ');
                $candidate->similarity_reason = "Similar {$sharedCatNames} channel";
            } elseif ($candidate->language_code === $targetLang) {
                $candidate->similarity_reason = "Shares {$targetLang} language broadcast";
            } else {
                $candidate->similarity_reason = 'Curated station recommendation';
            }

            return $candidate;
        })
            ->sortByDesc('similarity_score')
            ->values()
            ->take($limit);
    }

    /**
     * Eagerly attach live on-air and upcoming programmes to a collection of channels.
     */
    public function attachLiveProgrammes(Collection $channels): Collection
    {
        if ($channels->isEmpty()) {
            return $channels;
        }

        $now = now('UTC');
        $channelIds = $channels->pluck('id')->toArray();

        // 1. Fetch current on-air schedule entries
        $currentEntries = ScheduleEntry::whereIn('channel_id', $channelIds)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->with('video')
            ->get()
            ->keyBy('channel_id');

        // 2. Fetch upcoming schedule entries
        $upcomingEntries = ScheduleEntry::whereIn('channel_id', $channelIds)
            ->where('start_time', '>=', $now)
            ->where('start_time', '<=', $now->copy()->addHours(12))
            ->orderBy('start_time')
            ->get()
            ->groupBy('channel_id');

        // 3. Fallback to Programme model if no schedule entries
        $programmes = Programme::whereIn('channel_id', $channelIds)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->get()
            ->keyBy('channel_id');

        return $channels->map(function ($channel) use ($currentEntries, $upcomingEntries, $programmes) {
            $current = $currentEntries->get($channel->id);
            if ($current) {
                $channel->current_programme = (object) [
                    'id' => $current->id,
                    'title' => $current->title ?? $current->video?->title ?? 'Live Broadcast',
                    'start_time' => $current->start_time,
                    'end_time' => $current->end_time,
                    'description' => $current->video?->description,
                ];
            } elseif ($prog = $programmes->get($channel->id)) {
                $channel->current_programme = (object) [
                    'id' => $prog->id,
                    'title' => $prog->title,
                    'start_time' => $prog->start_time,
                    'end_time' => $prog->end_time,
                    'description' => $prog->description,
                ];
            } else {
                $channel->current_programme = (object) [
                    'id' => null,
                    'title' => "{$channel->name} Live Playout",
                    'start_time' => now()->startOfHour(),
                    'end_time' => now()->addHour()->startOfHour(),
                    'description' => '24/7 continuous broadcast',
                ];
            }

            $upcomingList = $upcomingEntries->get($channel->id);
            if ($upcomingList && $upcomingList->isNotEmpty()) {
                $next = $upcomingList->first();
                $channel->upcoming_programme = (object) [
                    'id' => $next->id,
                    'title' => $next->title ?? $next->video?->title ?? 'Upcoming Show',
                    'start_time' => $next->start_time,
                    'end_time' => $next->end_time,
                ];
            } else {
                $channel->upcoming_programme = null;
            }

            return $channel;
        });
    }

    /**
     * Build rich personalized watch guide dataset.
     */
    public function getPersonalizedWatchGuide(?User $user, int $limit = 12, string $clientCountry = 'NGA'): array
    {
        $rankedChannels = $this->rankChannelsForUser($user, $limit, $clientCountry);
        $rankedWithPrograms = $this->attachLiveProgrammes($rankedChannels);

        $favourites = collect();
        $recentChannels = collect();
        $topCategoryName = null;

        if ($user) {
            $userFavs = $user->favouriteChannels()->with('categories', 'primaryStream')->get();
            $favourites = $this->attachLiveProgrammes($userFavs);

            $recentChannelIds = ViewingEvent::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(20)
                ->pluck('channel_id')
                ->unique()
                ->take(6);

            if ($recentChannelIds->isNotEmpty()) {
                $recents = Channel::whereIn('id', $recentChannelIds)->with('categories', 'primaryStream')->get();
                $recentChannels = $this->attachLiveProgrammes($recents);
            }

            if ($user->preferences && ! empty($user->preferences->preferred_categories)) {
                $firstCat = Category::find($user->preferences->preferred_categories[0]);
                $topCategoryName = $firstCat?->name;
            }
        }

        // Build top category recommendations for discovery
        $topCategories = Category::withCount('channels')->orderByDesc('channels_count')->take(4)->get();
        $categoryPicks = [];
        foreach ($topCategories as $cat) {
            $catChannels = Channel::whereHas('categories', fn ($q) => $q->where('categories.id', $cat->id))
                ->where('is_active', true)
                ->where('is_published', true)
                ->take(4)
                ->get();

            if ($catChannels->isNotEmpty()) {
                $categoryPicks[] = [
                    'category' => $cat,
                    'channels' => $this->attachLiveProgrammes($catChannels),
                ];
            }
        }

        return [
            'recommended_channels' => $rankedWithPrograms,
            'favourites' => $favourites,
            'recent_history' => $recentChannels,
            'category_picks' => $categoryPicks,
            'stats' => [
                'total_recommended' => $rankedWithPrograms->count(),
                'saved_favourites_count' => $favourites->count(),
                'top_matching_category' => $topCategoryName ?? 'All Genres',
            ],
        ];
    }
}
