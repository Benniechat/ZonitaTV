<?php

namespace App\Jobs\Zonita;

use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Models\SystemAlert;
use App\Services\Zonita\ChannelReserveService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MonitorScheduleHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?Channel $channel = null,
        public int $minFutureHours = 24,
        public int $minReserveCount = 5
    ) {}

    public function handle(ChannelReserveService $reserveService): void
    {
        $channels = $this->channel
            ? collect([$this->channel])
            : Channel::whereHas('sourceRule')->orWhere('is_active', true)->get();

        foreach ($channels as $channel) {
            $this->inspectChannelHealth($channel, $reserveService);
        }
    }

    protected function inspectChannelHealth(Channel $channel, ChannelReserveService $reserveService): void
    {
        $timezone = $channel->sourceRule?->timezone ?? 'Africa/Lagos';
        $now = Carbon::now($timezone);
        $thresholdTime = $now->copy()->addHours($this->minFutureHours);

        // 1. Check Future Schedule
        $maxScheduledEnd = ScheduleEntry::where('channel_id', $channel->id)
            ->where('end_time', '>', $now)
            ->max('end_time');

        $isScheduleLow = ! $maxScheduledEnd || Carbon::parse($maxScheduledEnd, $timezone)->lessThan($thresholdTime);

        if ($isScheduleLow) {
            Log::warning("Low schedule threshold detected on channel [{$channel->slug}]. Future end: " . ($maxScheduledEnd ?? 'none'));

            $existingAlert = SystemAlert::where('channel_id', $channel->id)
                ->where('alert_type', 'low_schedule')
                ->where('is_resolved', false)
                ->first();

            if (! $existingAlert) {
                SystemAlert::create([
                    'channel_id' => $channel->id,
                    'level' => 'warning',
                    'alert_type' => 'low_schedule',
                    'message' => "Channel [{$channel->name}] has less than {$this->minFutureHours}h of scheduled programming remaining.",
                    'is_resolved' => false,
                    'created_at' => now(),
                ]);
            }

            // Trigger replenishing job
            RefreshChannelQueue::dispatch($channel, $this->minFutureHours * 2);
        } else {
            // Auto-resolve any prior low_schedule alerts
            SystemAlert::where('channel_id', $channel->id)
                ->where('alert_type', 'low_schedule')
                ->where('is_resolved', false)
                ->update([
                    'is_resolved' => true,
                    'resolved_at' => now(),
                ]);
        }

        // 2. Check Reserve Health
        $reserveStatus = $reserveService->getReserveStatus($channel);

        if (! $reserveStatus['is_sufficient']) {
            Log::error("Reserve pool depleted for channel [{$channel->slug}]: {$reserveStatus['current_reserve_count']}/{$reserveStatus['min_required']} available.");

            $existingReserveAlert = SystemAlert::where('channel_id', $channel->id)
                ->where('alert_type', 'low_reserve')
                ->where('is_resolved', false)
                ->first();

            if (! $existingReserveAlert) {
                $alert = SystemAlert::create([
                    'channel_id' => $channel->id,
                    'level' => 'critical',
                    'alert_type' => 'low_reserve',
                    'message' => "Channel [{$channel->name}] reserve pool depleted ({$reserveStatus['current_reserve_count']}/{$reserveStatus['min_required']} available).",
                    'is_resolved' => false,
                    'created_at' => now(),
                ]);

                NotifyAdministrators::dispatch($alert);
            }
        } else {
            SystemAlert::where('channel_id', $channel->id)
                ->where('alert_type', 'low_reserve')
                ->where('is_resolved', false)
                ->update([
                    'is_resolved' => true,
                    'resolved_at' => now(),
                ]);
        }
    }
}
