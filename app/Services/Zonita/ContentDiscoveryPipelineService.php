<?php

namespace App\Services\Zonita;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelVideo;
use App\Models\ContentProvider;
use App\Models\DiscoveryCandidate;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Log;

class ContentDiscoveryPipelineService
{
    /**
     * Parse YouTube video ID from various URL formats.
     */
    public function parseYouTubeId(string $url): ?string
    {
        $trimmed = trim($url);

        // Standard watch?v=, embed/, youtu.be/, shorts/
        $patterns = [
            '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]+)/i',
            '/youtube\.com\/shorts\/([^"&?\/ ]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $trimmed, $matches)) {
                return $matches[1];
            }
        }

        // If it's already an ID string
        if (preg_match('/^[a-zA-Z0-9_-]{5,32}$/', $trimmed)) {
            return $trimmed;
        }

        return null;
    }

    /**
     * Generate canonical content hash for deduplication.
     */
    public function generateContentHash(string $sourceType, string $sourceIdentifier): string
    {
        return hash('sha256', strtolower("{$sourceType}:{$sourceIdentifier}"));
    }

    /**
     * Ingest a candidate into the discovery staging area with deduplication checks.
     */
    public function ingestCandidate(array $data, ?ContentProvider $provider = null): array
    {
        $sourceType = $data['source_type'] ?? 'youtube_video';
        $sourceUrl = trim($data['source_url'] ?? '');

        // Resolve identifier based on source type
        $sourceIdentifier = null;
        if ($sourceType === 'youtube_video' || str_contains($sourceUrl, 'youtu')) {
            $sourceType = 'youtube_video';
            $sourceIdentifier = $this->parseYouTubeId($data['source_identifier'] ?? $sourceUrl);
        } else {
            $sourceIdentifier = $data['source_identifier'] ?? md5($sourceUrl);
        }

        if (empty($sourceIdentifier)) {
            return [
                'status' => 'error',
                'reason' => 'Unable to resolve unique source identifier from provided URL.',
            ];
        }

        $contentHash = $this->generateContentHash($sourceType, $sourceIdentifier);

        // 1. Check if candidate already staged in discovery workbench
        $existingCandidate = DiscoveryCandidate::where('content_hash', $contentHash)
            ->orWhere(function ($q) use ($sourceType, $sourceIdentifier) {
                $q->where('source_type', $sourceType)
                    ->where('source_identifier', $sourceIdentifier);
            })
            ->first();

        if ($existingCandidate) {
            return [
                'status' => 'duplicate',
                'reason' => "Content item already exists in discovery staging (status: {$existingCandidate->intake_status}).",
                'candidate' => $existingCandidate,
            ];
        }

        // 2. Check if item already exists in the active Video pool
        if ($sourceType === 'youtube_video') {
            $existingVideo = Video::withTrashed()->where('youtube_id', $sourceIdentifier)->first();
            if ($existingVideo) {
                // Check if blacklisted due to copyright takedown or rights rejection
                if (in_array($existingVideo->availability_status, ['disabled', 'removed', 'complaint_received', 'rights_rejected'])) {
                    Log::warning("Discovery intake rejected for {$sourceIdentifier}: Excluded by legal compliance status ({$existingVideo->availability_status})");

                    return [
                        'status' => 'rejected',
                        'reason' => "Intake prohibited: Video has previous legal takedown or rights rejection status ({$existingVideo->availability_status}).",
                    ];
                }

                return [
                    'status' => 'duplicate',
                    'reason' => "Video already exists in active broadcast catalogue (ID: #{$existingVideo->id}).",
                    'video' => $existingVideo,
                ];
            }
        }

        // 3. Fallback metadata normalization
        $title = $data['title'] ?? 'Discovered Broadcast Video';
        $description = $data['description'] ?? null;
        $creatorName = $data['creator_name'] ?? ($provider?->name ?? 'Independent Creator');
        $durationSeconds = (int) ($data['duration_seconds'] ?? 1800);
        $thumbnailUrl = $data['thumbnail_url'] ?? null;

        if (empty($thumbnailUrl) && $sourceType === 'youtube_video') {
            $thumbnailUrl = "https://img.youtube.com/vi/{$sourceIdentifier}/hqdefault.jpg";
        }

        // 4. Create staged candidate
        $candidate = DiscoveryCandidate::create([
            'content_provider_id' => $provider?->id ?? ($data['content_provider_id'] ?? null),
            'channel_id' => $data['channel_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'source_type' => $sourceType,
            'source_url' => $sourceUrl,
            'source_identifier' => $sourceIdentifier,
            'content_hash' => $contentHash,
            'title' => $title,
            'description' => $description,
            'creator_name' => $creatorName,
            'thumbnail_url' => $thumbnailUrl,
            'duration_seconds' => $durationSeconds,
            'aspect_ratio' => $data['aspect_ratio'] ?? '16:9',
            'resolution' => $data['resolution'] ?? '1080p',
            'content_rating' => $data['content_rating'] ?? 'PG',
            'tags' => $data['tags'] ?? [],
            'published_at' => $data['published_at'] ?? now(),
            'rights_basis' => $data['rights_basis'] ?? ($provider?->default_rights_basis ?? 'official_embed_permitted'),
            'intake_status' => 'staged',
        ]);

        return [
            'status' => 'ingested',
            'candidate' => $candidate,
        ];
    }

    /**
     * Promote a staged candidate to active broadcast video catalogue.
     */
    public function promoteCandidate(DiscoveryCandidate $candidate, User $reviewer, ?Channel $channel = null, ?Category $category = null): Video
    {
        $targetChannel = $channel ?: $candidate->channel;
        $targetCategory = $category ?: $candidate->category;

        // Create or reactivate active Video record
        $video = Video::updateOrCreate(
            ['youtube_id' => $candidate->source_identifier],
            [
                'title' => $candidate->title,
                'description' => $candidate->description,
                'channel_title' => $candidate->creator_name,
                'thumbnail_url' => $candidate->thumbnail_url,
                'duration_seconds' => $candidate->duration_seconds,
                'aspect_ratio' => $candidate->aspect_ratio,
                'resolution' => $candidate->resolution,
                'content_rating' => $candidate->content_rating,
                'tags' => $candidate->tags,
                'rights_basis' => $candidate->rights_basis,
                'format_type' => 'feature',
                'category_slug' => $targetCategory?->slug ?? $targetChannel?->categories->first()?->slug,
                'is_embeddable' => true,
                'availability_status' => 'approved',
                'confidence_score' => 100,
                'last_verified_at' => now(),
            ]
        );

        // If target channel specified, attach to channel video rotation
        if ($targetChannel) {
            ChannelVideo::updateOrCreate(
                [
                    'channel_id' => $targetChannel->id,
                    'video_id' => $video->id,
                ],
                [
                    'is_approved' => true,
                    'relevance_score' => 100,
                ]
            );
        }

        // Update discovery candidate status
        $candidate->update([
            'intake_status' => 'approved',
            'approved_video_id' => $video->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $video;
    }

    /**
     * Reject a staged candidate with a documented reason.
     */
    public function rejectCandidate(DiscoveryCandidate $candidate, User $reviewer, string $reason): void
    {
        $candidate->update([
            'intake_status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }
}
