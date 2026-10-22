<?php

namespace App\Services\Zonita;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Str;

class ChannelRelevanceService
{
    /**
     * Evaluates candidate video relevance against a channel's editorial and source rules.
     */
    public function scoreCandidate(Video $video, Channel $channel): array
    {
        $rule = $channel->sourceRule;
        $title = Str::lower($video->title);
        $desc = Str::lower($video->description ?? '');
        $channelTitle = Str::lower($video->channel_title ?? '');
        $contentCorpus = "{$title} {$desc} {$channelTitle}";

        // 1. Excluded Keywords Check (Hard Reject)
        if ($rule && ! empty($rule->excluded_keywords)) {
            foreach ($rule->excluded_keywords as $forbidden) {
                if (! empty($forbidden) && Str::contains($contentCorpus, Str::lower($forbidden))) {
                    return [
                        'is_relevant' => false,
                        'relevance_score' => 0,
                        'reason' => "Contains excluded keyword: '{$forbidden}'",
                    ];
                }
            }
        }

        // 2. Duration Bounds Check
        $minSeconds = ($rule?->min_duration_minutes ?? 3) * 60;
        $maxSeconds = ($rule?->max_duration_minutes ?? 120) * 60;
        if ($video->duration_seconds > 0) {
            if ($video->duration_seconds < $minSeconds) {
                return [
                    'is_relevant' => false,
                    'relevance_score' => 10,
                    'reason' => "Duration below minimum ({$video->duration_seconds}s < {$minSeconds}s)",
                ];
            }
            if ($video->duration_seconds > $maxSeconds) {
                return [
                    'is_relevant' => false,
                    'relevance_score' => 15,
                    'reason' => "Duration above maximum ({$video->duration_seconds}s > {$maxSeconds}s)",
                ];
            }
        }

        // 3. Keyword Relevance Score
        $score = 50; // Base score
        $matchedKeywords = 0;

        if ($rule && ! empty($rule->search_keywords)) {
            foreach ($rule->search_keywords as $keyword) {
                if (! empty($keyword) && Str::contains($contentCorpus, Str::lower($keyword))) {
                    $matchedKeywords++;
                    $score += 15;
                }
            }
        }

        // Approved publisher boost
        if ($rule && ! empty($rule->approved_publishers)) {
            foreach ($rule->approved_publishers as $pub) {
                if (! empty($pub) && Str::contains($channelTitle, Str::lower($pub))) {
                    $score += 25;
                    break;
                }
            }
        }

        $finalScore = min(100, $score);
        $isRelevant = $finalScore >= 50;

        return [
            'is_relevant' => $isRelevant,
            'relevance_score' => $finalScore,
            'matched_keywords_count' => $matchedKeywords,
            'reason' => $isRelevant ? 'Approved match' : 'Insufficient relevance score',
        ];
    }
}
