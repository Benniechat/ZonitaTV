<?php

namespace App\Services\Zonita;

use App\Models\BroadcastHistory;
use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ContinuousPlayoutService
{
    public function __construct(
        protected PlaybackContinuityService $continuityService,
        protected ProgrammeScheduleService $scheduleService,
        protected ChannelReserveService $reserveService
    ) {}

    /**
     * Advances and verifies continuous playout for a single channel.
     *
     * @return array<string, mixed>
     */
    public function advanceChannelPlayout(Channel $channel, ?Carbon $currentTime = null, bool $autoReplenish = true): array
    {
        $timezone = $channel->sourceRule?->timezone ?? config('app.timezone', 'UTC');
        $now = $currentTime ? $currentTime->copy() : now();

        $completedCount = 0;
        $activatedCount = 0;
        $recovered = false;
        $gapFilled = false;
        $replenished = false;

        // 1. Mark completed entries whose scheduled end_time has passed
        $pastEntries = ScheduleEntry::where('channel_id', $channel->id)
            ->where('end_time', '<=', $now)
            ->whereIn('status', ['playing', 'scheduled'])
            ->get();

        foreach ($pastEntries as $pastEntry) {
            $pastEntry->update(['status' => 'completed']);
            $completedCount++;

            // Record in BroadcastHistory for telemetry & auditing
            BroadcastHistory::firstOrCreate(
                [
                    'channel_id' => $channel->id,
                    'video_id' => $pastEntry->video_id,
                    'scheduled_start_at' => $pastEntry->start_time,
                ],
                [
                    'scheduled_end_at' => $pastEntry->end_time,
                    'actual_start_at' => $pastEntry->start_time,
                    'actual_end_at' => $pastEntry->end_time,
                    'status' => 'completed',
                    'created_at' => now(),
                ]
            );
        }

        // 2. Identify the active entry covering current wall-clock time
        $activeEntry = ScheduleEntry::with('video')
            ->where('channel_id', $channel->id)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        if ($activeEntry) {
            // Activate if not already playing
            if ($activeEntry->status !== 'playing') {
                $activeEntry->update(['status' => 'playing']);
                $activatedCount++;
            }

            // Health watchdog: Verify video embeddability and availability
            $video = $activeEntry->video;
            $isUnplayable = ! $video ||
                ! $video->is_embeddable ||
                in_array($video->availability_status, ['disabled', 'removed', 'complaint_received', 'rights_rejected', 'embedding_disabled', 'deleted_or_missing']);

            if ($isUnplayable) {
                Log::warning("Continuous playout watchdog detected unplayable video [{$video?->youtube_id}] on channel [{$channel->slug}]. Triggering continuity recovery.");
                $recoveryResult = $this->continuityService->handlePlayerFailure(
                    $channel->id,
                    $video?->youtube_id ?? (string) $activeEntry->video_id,
                    'auto_watchdog_unplayable'
                );

                if (($recoveryResult['status'] ?? '') === 'recovered') {
                    $recovered = true;
                    // Refresh active entry to pick up the recovered schedule entry
                    $activeEntry = ScheduleEntry::with('video')
                        ->where('channel_id', $channel->id)
                        ->where('status', 'playing')
                        ->where('start_time', '<=', $now)
                        ->where('end_time', '>', $now)
                        ->first();
                }
            }
        } else {
            // 3. Gap detected in playout window! Auto-fill gap to prevent black screen
            Log::warning("Continuous playout watchdog detected gap on channel [{$channel->slug}] at [{$now->toIso8601String()}]. Auto-filling gap.");
            $fallbackVideo = $this->reserveService->getFallbackVideo($channel);

            if (! $fallbackVideo && $channel->fallback_video_id) {
                $fallbackVideo = Video::find($channel->fallback_video_id);
            }
            if (! $fallbackVideo && $channel->station_ident_video_id) {
                $fallbackVideo = Video::find($channel->station_ident_video_id);
            }

            if ($fallbackVideo) {
                $duration = $fallbackVideo->duration_seconds > 0 ? $fallbackVideo->duration_seconds : 1800;
                $activeEntry = ScheduleEntry::create([
                    'channel_id' => $channel->id,
                    'video_id' => $fallbackVideo->id,
                    'title' => '[Emergency Playout] '.$fallbackVideo->title,
                    'description' => $fallbackVideo->description,
                    'format_type' => $fallbackVideo->format_type ?? 'filler',
                    'start_time' => $now,
                    'end_time' => $now->copy()->addSeconds($duration),
                    'duration_seconds' => $duration,
                    'play_order' => 9999,
                    'status' => 'playing',
                    'is_fallback' => true,
                ]);
                $gapFilled = true;
                $activatedCount++;
            }
        }

        // 4. Queue Horizon & Replenishment
        if ($autoReplenish) {
            $maxFutureEnd = ScheduleEntry::where('channel_id', $channel->id)
                ->where('end_time', '>', $now)
                ->max('end_time');

            $horizonHours = $maxFutureEnd
                ? Carbon::parse($maxFutureEnd, $timezone)->diffInHours($now)
                : 0;

            if ($horizonHours < 48) {
                $this->scheduleService->generateRollingSchedule($channel, 7);
                $replenished = true;
            }
        }

        return [
            'channel_id' => $channel->id,
            'channel_slug' => $channel->slug,
            'completed_count' => $completedCount,
            'activated_count' => $activatedCount,
            'active_entry' => $activeEntry ? [
                'id' => $activeEntry->id,
                'title' => $activeEntry->title,
                'video_id' => $activeEntry->video?->youtube_id ?? (string) $activeEntry->video_id,
                'status' => $activeEntry->status,
                'is_fallback' => (bool) $activeEntry->is_fallback,
            ] : null,
            'recovered' => $recovered,
            'gap_filled' => $gapFilled,
            'replenished' => $replenished,
        ];
    }

    /**
     * Advances playout progression across all active channels.
     *
     * @return array<string, mixed>
     */
    public function advanceAllChannels(?Carbon $currentTime = null, bool $autoReplenish = true): array
    {
        $channels = Channel::where('is_active', true)
            ->where(function ($q) {
                $q->where('is_virtual', true)
                    ->orWhere('playback_mode', 'zoniva_original')
                    ->orWhereHas('scheduleEntries');
            })
            ->get();

        $totalCompleted = 0;
        $totalActivated = 0;
        $totalRecovered = 0;
        $totalGapsFilled = 0;
        $totalReplenished = 0;
        $channelDetails = [];

        foreach ($channels as $channel) {
            $result = $this->advanceChannelPlayout($channel, $currentTime, $autoReplenish);
            $totalCompleted += $result['completed_count'];
            $totalActivated += $result['activated_count'];
            if ($result['recovered']) {
                $totalRecovered++;
            }
            if ($result['gap_filled']) {
                $totalGapsFilled++;
            }
            if ($result['replenished']) {
                $totalReplenished++;
            }
            $channelDetails[] = $result;
        }

        return [
            'channels_processed' => $channels->count(),
            'total_completed' => $totalCompleted,
            'total_activated' => $totalActivated,
            'total_recovered' => $totalRecovered,
            'total_gaps_filled' => $totalGapsFilled,
            'total_replenished' => $totalReplenished,
            'channels' => $channelDetails,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * System playout health heartbeat telemetry.
     *
     * @return array<string, mixed>
     */
    public function getPlayoutHealthHeartbeat(): array
    {
        $now = now();

        $activeChannels = Channel::where('is_active', true)->where('is_virtual', true)->count();
        $channelsWithOnAirEntry = Channel::where('is_active', true)
            ->where('is_virtual', true)
            ->whereHas('scheduleEntries', function ($q) use ($now) {
                $q->where('start_time', '<=', $now)
                    ->where('end_time', '>', $now)
                    ->where('status', 'playing');
            })
            ->count();

        $totalFailuresLast24h = BroadcastHistory::where('created_at', '>=', now()->subDay())
            ->whereIn('status', ['interrupted', 'failed'])
            ->count();

        $totalBroadcastsCompleted = BroadcastHistory::where('status', 'completed')->count();

        return [
            'status' => ($activeChannels > 0 && $channelsWithOnAirEntry === $activeChannels) ? 'healthy' : 'degraded',
            'active_channels_count' => $activeChannels,
            'on_air_channels_count' => $channelsWithOnAirEntry,
            'channels_with_gaps_count' => max(0, $activeChannels - $channelsWithOnAirEntry),
            'failures_last_24h' => $totalFailuresLast24h,
            'total_broadcasts_completed' => $totalBroadcastsCompleted,
            'heartbeat_at' => $now->toIso8601String(),
        ];
    }
}
