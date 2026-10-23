<?php

namespace App\Jobs\Zonita;

use App\Models\Video;
use App\Services\Zonita\VideoAvailabilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidateCandidateVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(
        public Video $video,
        public bool $force = false
    ) {}

    public function handle(VideoAvailabilityService $availabilityService): void
    {
        Log::info("Validating video eligibility: {$this->video->youtube_id}");

        $result = $availabilityService->verifyVideo($this->video, $this->force);

        Log::info("Validation complete for {$this->video->youtube_id}: status={$result['status']}, playable={$result['is_playable']}");
    }
}
