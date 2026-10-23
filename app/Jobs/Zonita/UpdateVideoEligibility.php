<?php

namespace App\Jobs\Zonita;

use App\Models\Video;
use App\Services\Zonita\VideoAvailabilityService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateVideoEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $batchLimit = 50,
        public bool $force = false
    ) {}

    public function handle(VideoAvailabilityService $availabilityService): void
    {
        $staleThreshold = Carbon::now()->subHours(24);

        // Fetch videos requiring reverification or coming off cooldown
        $videos = Video::where(function ($query) use ($staleThreshold) {
            $query->where('availability_status', 'approved')
                ->where(function ($sub) use ($staleThreshold) {
                    $sub->whereNull('last_verified_at')
                        ->orWhere('last_verified_at', '<', $staleThreshold);
                });
        })
            ->orWhere(function ($query) {
                $query->whereNotNull('cooldown_until')
                    ->where('cooldown_until', '<=', Carbon::now());
            })
            ->limit($this->batchLimit)
            ->get();

        Log::info("Starting video eligibility reverification batch of {$videos->count()} videos (forced: ".($this->force ? 'yes' : 'no').')');

        $verifiedCount = 0;
        $failedCount = 0;

        foreach ($videos as $video) {
            $result = $availabilityService->verifyVideo($video, $this->force);

            if (($result['status'] ?? null) === 'approved') {
                $verifiedCount++;
            } else {
                $failedCount++;
                Log::warning("Video [{$video->youtube_id}] marked non-approved during eligibility sweep: ".($result['status'] ?? 'unknown'));
            }
        }

        Log::info("Completed video eligibility sweep: {$verifiedCount} approved, {$failedCount} flagged/unapproved.");
    }
}
