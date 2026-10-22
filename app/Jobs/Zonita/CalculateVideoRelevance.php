<?php

namespace App\Jobs\Zonita;

use App\Models\Channel;
use App\Models\ChannelVideo;
use App\Models\Video;
use App\Services\Zonita\ChannelRelevanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateVideoRelevance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Video $video,
        public Channel $channel
    ) {}

    public function handle(ChannelRelevanceService $relevanceService): void
    {
        $evaluation = $relevanceService->scoreCandidate($this->video, $this->channel);

        $this->video->relevance_score = $evaluation['relevance_score'];
        $this->video->save();

        ChannelVideo::updateOrCreate(
            [
                'channel_id' => $this->channel->id,
                'video_id' => $this->video->id,
            ],
            [
                'relevance_score' => $evaluation['relevance_score'],
                'is_approved' => $evaluation['is_relevant'],
                'is_reserve' => $this->video->is_evergreen,
            ]
        );
    }
}
