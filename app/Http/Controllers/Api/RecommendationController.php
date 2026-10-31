<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(
        protected RecommendationService $recommendationService
    ) {}

    /**
     * Get personalized channel recommendations for viewer or guest.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min(50, max(1, (int) $request->input('limit', 12)));
        $country = (string) $request->input('country', 'NGA');

        $user = $request->user();
        $channels = $this->recommendationService->rankChannelsForUser($user, $limit, $country);
        $channelsWithPrograms = $this->recommendationService->attachLiveProgrammes($channels);

        $data = $channelsWithPrograms->map(function ($channel) {
            return [
                'id' => $channel->id,
                'name' => $channel->name,
                'slug' => $channel->slug,
                'channel_number' => $channel->channel_number,
                'logo_url' => $channel->logo_url,
                'language_code' => $channel->language_code,
                'is_featured' => (bool) $channel->is_featured,
                'is_virtual' => (bool) $channel->is_virtual,
                'recommendation_score' => $channel->recommendation_score,
                'recommendation_tag' => $channel->recommendation_tag,
                'recommendation_reason' => $channel->recommendation_reason,
                'categories' => $channel->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ]),
                'current_programme' => $channel->current_programme ?? null,
                'upcoming_programme' => $channel->upcoming_programme ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * Get similar channels for a target channel.
     */
    public function similar(Request $request, string $slug): JsonResponse
    {
        $channel = Channel::where('slug', $slug)->firstOrFail();
        $limit = min(20, max(1, (int) $request->input('limit', 6)));
        $country = (string) $request->input('country', 'NGA');

        $similar = $this->recommendationService->getSimilarChannels($channel, $limit, $country);
        $similarWithPrograms = $this->recommendationService->attachLiveProgrammes($similar);

        $data = $similarWithPrograms->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'channel_number' => $c->channel_number,
                'logo_url' => $c->logo_url,
                'similarity_score' => $c->similarity_score,
                'similarity_reason' => $c->similarity_reason,
                'categories' => $c->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ]),
                'current_programme' => $c->current_programme ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'channel' => [
                'id' => $channel->id,
                'name' => $channel->name,
                'slug' => $channel->slug,
            ],
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * Get full personalized watch guide payload.
     */
    public function guide(Request $request): JsonResponse
    {
        $limit = min(30, max(1, (int) $request->input('limit', 12)));
        $country = (string) $request->input('country', 'NGA');

        $user = $request->user();
        $guide = $this->recommendationService->getPersonalizedWatchGuide($user, $limit, $country);

        return response()->json([
            'success' => true,
            'data' => $guide,
        ]);
    }
}
