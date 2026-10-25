<?php

namespace App\Services\Zonita;

use App\Models\Channel;
use App\Models\ChannelVideo;
use App\Models\Video;
use Illuminate\Support\Facades\Log;

class YouTubeDiscoveryService
{
    public function __construct(
        protected VideoAvailabilityService $availabilityService,
        protected ChannelRelevanceService $relevanceService
    ) {}

    /**
     * Ingests or verifies a candidate video into the channel's candidate pool.
     */
    public function ingestCandidate(array $candidateData, Channel $channel): ?Video
    {
        $youtubeId = $candidateData['youtube_id'] ?? null;
        if (! $youtubeId) {
            return null;
        }

        $categorySlug = $channel->categories->first()?->slug;

        // 1. Duplicate check / fetch existing
        $video = Video::firstOrNew(['youtube_id' => $youtubeId]);

        // Never re-discover content that has been disabled, removed, or received a takedown complaint
        if ($video->exists && in_array($video->availability_status, ['disabled', 'removed', 'complaint_received', 'rights_rejected'])) {
            Log::info("Discovery rejected for {$youtubeId}: excluded by legal compliance/takedown status ({$video->availability_status})");

            return null;
        }

        $video->title = $candidateData['title'] ?? ($video->title ?? 'Untitled Broadcast');
        $video->description = $candidateData['description'] ?? $video->description;
        $video->channel_title = $candidateData['channel_title'] ?? $video->channel_title;
        $video->duration_seconds = $candidateData['duration_seconds'] ?? ($video->duration_seconds ?: 1800);
        $video->category_slug = $categorySlug ?? $video->category_slug;
        $video->format_type = $candidateData['format_type'] ?? ($video->format_type ?? 'feature');
        $video->is_evergreen = $candidateData['is_evergreen'] ?? ($video->is_evergreen ?? false);
        $video->rights_basis = $candidateData['rights_basis'] ?? 'official_embed_permitted';
        $video->tags = $candidateData['tags'] ?? ($video->tags ?? []);

        // 2. Relevance Scoring
        $relevance = $this->relevanceService->scoreCandidate($video, $channel);
        if (! $relevance['is_relevant']) {
            Log::info("Candidate {$youtubeId} rejected for {$channel->slug}: {$relevance['reason']}");
            $video->availability_status = 'rejected_irrelevant';
            $video->save();

            return null;
        }

        $video->relevance_score = $relevance['relevance_score'];
        $video->save();

        // 3. Technical Verification (pre-scheduling check)
        $verification = $this->availabilityService->verifyVideo($video);
        if (! $verification['is_playable']) {
            Log::info("Candidate {$youtubeId} rejected for {$channel->slug}: unplayable ({$verification['status']})");

            return null;
        }

        // 4. Attach to Channel Video Candidate Pool
        ChannelVideo::updateOrCreate(
            [
                'channel_id' => $channel->id,
                'video_id' => $video->id,
            ],
            [
                'is_approved' => true,
                'relevance_score' => $video->relevance_score,
            ]
        );

        return $video;
    }

    /**
     * Executes content discovery for a channel based on its rules.
     */
    public function discoverForChannel(Channel $channel, array $candidateList = []): array
    {
        $ingested = 0;
        $rejected = 0;

        foreach ($candidateList as $item) {
            $video = $this->ingestCandidate($item, $channel);
            if ($video && $video->isPlayable()) {
                $ingested++;
            } else {
                $rejected++;
            }
        }

        return [
            'channel' => $channel->slug,
            'ingested' => $ingested,
            'rejected' => $rejected,
        ];
    }
}
