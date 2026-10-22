<?php

namespace App\Jobs\Zonita;

use App\Models\BroadcastHistory;
use App\Models\Channel;
use App\Models\PlaybackFailure;
use App\Models\ScheduleEntry;
use App\Services\Zonita\ChannelReserveService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateChannelReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?Channel $channel = null,
        public int $lookbackHours = 24
    ) {}

    public function handle(ChannelReserveService $reserveService): array
    {
        $channels = $this->channel
            ? collect([$this->channel])
            : Channel::whereHas('sourceRule')->orWhere('is_active', true)->get();

        $reports = [];

        foreach ($channels as $channel) {
            $reports[$channel->slug] = $this->generateReportForChannel($channel, $reserveService);
        }

        return $reports;
    }

    protected function generateReportForChannel(Channel $channel, ChannelReserveService $reserveService): array
    {
        $since = Carbon::now()->subHours($this->lookbackHours);

        $totalScheduled = ScheduleEntry::where('channel_id', $channel->id)
            ->where('start_time', '>=', $since)
            ->count();

        $completedCount = ScheduleEntry::where('channel_id', $channel->id)
            ->where('start_time', '>=', $since)
            ->where('status', 'completed')
            ->count();

        $failureCount = PlaybackFailure::where('channel_id', $channel->id)
            ->where('created_at', '>=', $since)
            ->count();

        $historySessions = BroadcastHistory::where('channel_id', $channel->id)
            ->where('created_at', '>=', $since)
            ->sum('session_count');

        $reserveStatus = $reserveService->getReserveStatus($channel);

        $uptimePercentage = $totalScheduled > 0
            ? round((($totalScheduled - $failureCount) / $totalScheduled) * 100, 2)
            : 100.0;

        $reportData = [
            'channel_id' => $channel->id,
            'channel_name' => $channel->name,
            'lookback_hours' => $this->lookbackHours,
            'total_scheduled_programmes' => $totalScheduled,
            'completed_programmes' => $completedCount,
            'playback_failures' => $failureCount,
            'total_audience_sessions' => (int) $historySessions,
            'reserve_available' => $reserveStatus['current_reserve_count'],
            'reserve_required' => $reserveStatus['min_required'],
            'uptime_percentage' => max(0, min(100, $uptimePercentage)),
            'generated_at' => now()->toIso8601String(),
        ];

        Log::info("Broadcast Report for [{$channel->slug}]: Uptime: {$reportData['uptime_percentage']}%, Failures: {$failureCount}, Reserve: {$reportData['reserve_available']}/{$reportData['reserve_required']}");

        return $reportData;
    }
}
