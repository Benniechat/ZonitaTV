<?php

namespace App\Jobs\Zonita;

use App\Models\Channel;
use App\Services\Zonita\ProgrammeScheduleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateChannelSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public function __construct(
        public Channel $channel,
        public int $daysAhead = 7
    ) {}

    public function handle(ProgrammeScheduleService $scheduleService): void
    {
        Log::info("Generating {$this->daysAhead}-day rolling schedule for [{$this->channel->slug}]");

        $result = $scheduleService->generateRollingSchedule($this->channel, $this->daysAhead);

        Log::info("Schedule generation complete for [{$this->channel->slug}]: {$result['entries_created']} entries created.");
    }
}
