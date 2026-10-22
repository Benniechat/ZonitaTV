<?php

namespace App\Jobs\Zonita;

use App\Models\Channel;
use App\Services\Zonita\YouTubeDiscoveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DiscoverChannelVideos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public Channel $channel,
        public array $candidateList = []
    ) {}

    public function handle(YouTubeDiscoveryService $discoveryService): void
    {
        Log::info("Executing DiscoverChannelVideos job for [{$this->channel->slug}]");

        $result = $discoveryService->discoverForChannel($this->channel, $this->candidateList);

        Log::info("DiscoverChannelVideos completed for [{$this->channel->slug}]: Ingested {$result['ingested']}, Rejected {$result['rejected']}");
    }
}
