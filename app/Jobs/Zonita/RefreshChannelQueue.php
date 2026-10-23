<?php

namespace App\Jobs\Zonita;

use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Services\Zonita\ProgrammeScheduleService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshChannelQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Channel $channel,
        public int $minFutureHours = 48
    ) {}

    public function handle(ProgrammeScheduleService $scheduleService): void
    {
        $timezone = $this->channel->sourceRule?->timezone ?? 'Africa/Lagos';
        $now = Carbon::now($timezone);
        $thresholdTime = $now->copy()->addHours($this->minFutureHours);

        $maxScheduledEnd = ScheduleEntry::where('channel_id', $this->channel->id)
            ->where('end_time', '>', $now)
            ->max('end_time');

        $needsReplenishment = ! $maxScheduledEnd || Carbon::parse($maxScheduledEnd, $timezone)->lessThan($thresholdTime);

        if ($needsReplenishment) {
            Log::info("Channel queue replenishment triggered for [{$this->channel->slug}]. Max future end: ".($maxScheduledEnd ?? 'none'));
            $scheduleService->generateRollingSchedule($this->channel, 7);
        }
    }
}
