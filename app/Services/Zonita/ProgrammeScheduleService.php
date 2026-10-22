<?php

namespace App\Services\Zonita;

use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Models\Video;
use Carbon\Carbon;

class ProgrammeScheduleService
{
    public function __construct(
        protected ContentRotationService $rotationService,
        protected ChannelReserveService $reserveService
    ) {}

    /**
     * Generates a continuous rolling schedule for a channel.
     */
    public function generateRollingSchedule(Channel $channel, int $daysAhead = 7): array
    {
        $timezone = $channel->sourceRule?->timezone ?? 'Africa/Lagos';
        $now = Carbon::now($timezone);
        $timelineEnd = $now->copy()->addDays($daysAhead)->endOfDay();

        $categorySlug = $channel->categories->first()?->slug;

        // Fetch eligible video candidate pool
        $candidates = Video::eligibleForScheduling()
            ->when($categorySlug, fn ($q) => $q->where('category_slug', $categorySlug))
            ->get();

        if ($candidates->isEmpty()) {
            return [
                'channel' => $channel->slug,
                'entries_created' => 0,
                'status' => 'insufficient_reserve',
            ];
        }

        // Determine where schedule left off
        $lastEntry = ScheduleEntry::where('channel_id', $channel->id)
            ->where('end_time', '>', $now)
            ->orderByDesc('end_time')
            ->first();

        $timelinePointer = $lastEntry ? Carbon::parse($lastEntry->end_time, $timezone) : $now->copy()->startOfHour();
        $order = $lastEntry ? $lastEntry->play_order + 1 : 1;
        $createdCount = 0;

        $rule = $channel->sourceRule;
        $cooldownHours = $rule?->repeat_cooldown_hours ?? 6;

        // In-memory tracking for fast generation
        $recentScheduled = []; // [video_id => Carbon expireTime]
        $lastPublisher = null;
        $entriesToInsert = [];

        while ($timelinePointer->lessThan($timelineEnd)) {
            // Clean expired cooldowns
            $activeRecentIds = [];
            foreach ($recentScheduled as $vId => $expTime) {
                if ($timelinePointer->lessThan($expTime)) {
                    $activeRecentIds[] = $vId;
                }
            }

            // Apply rotation and repetition controls
            $eligible = $this->rotationService->filterEligibleCandidates(
                $candidates,
                $channel,
                $timelinePointer,
                $activeRecentIds,
                $lastPublisher
            );

            // If rotation filtered everything out due to cooldown, fallback to candidates
            if ($eligible->isEmpty()) {
                $eligible = $candidates;
            }

            /** @var Video $selectedVideo */
            $selectedVideo = $eligible->first() ?? $candidates->first();
            $durationSeconds = max(300, $selectedVideo->duration_seconds ?: 1800);
            $startTime = $timelinePointer->copy();
            $endTime = $startTime->copy()->addSeconds($durationSeconds);

            $status = 'scheduled';
            if ($now->between($startTime, $endTime)) {
                $status = 'playing';
            } elseif ($endTime->lessThan($now)) {
                $status = 'completed';
            }

            $entriesToInsert[] = [
                'channel_id' => $channel->id,
                'video_id' => $selectedVideo->id,
                'title' => $selectedVideo->title,
                'description' => $selectedVideo->description,
                'format_type' => $selectedVideo->format_type ?: 'feature',
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'duration_seconds' => $durationSeconds,
                'play_order' => $order++,
                'status' => $status,
                'is_fallback' => false,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            $recentScheduled[$selectedVideo->id] = $endTime->copy()->addHours($cooldownHours);
            $lastPublisher = $selectedVideo->channel_title;

            $createdCount++;
            $timelinePointer = $endTime;

            if (count($entriesToInsert) >= 100) {
                ScheduleEntry::insert($entriesToInsert);
                $entriesToInsert = [];
            }
        }

        if (! empty($entriesToInsert)) {
            ScheduleEntry::insert($entriesToInsert);
        }

        return [
            'channel' => $channel->slug,
            'entries_created' => $createdCount,
            'status' => 'ok',
        ];
    }

    /**
     * Builds the Electronic Programme Guide (EPG) presentation data.
     */
    public function getEpgData(Channel $channel): array
    {
        $timezone = $channel->sourceRule?->timezone ?? 'Africa/Lagos';
        $now = Carbon::now($timezone);

        // Currently Playing
        $current = ScheduleEntry::where('channel_id', $channel->id)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->with('video')
            ->first();

        // If gap, take nearest past or next
        if (! $current) {
            $current = ScheduleEntry::where('channel_id', $channel->id)
                ->where('start_time', '<=', $now)
                ->orderByDesc('start_time')
                ->with('video')
                ->first();
        }

        // Up Next (next 12 entries)
        $upcoming = ScheduleEntry::where('channel_id', $channel->id)
            ->where('start_time', '>', $current ? $current->start_time : $now)
            ->orderBy('start_time')
            ->take(12)
            ->with('video')
            ->get();

        // Today's timeline
        $todaySchedule = ScheduleEntry::where('channel_id', $channel->id)
            ->whereBetween('start_time', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->orderBy('start_time')
            ->with('video')
            ->get();

        // Build player playlist with real seconds offsets
        $playlistQueue = [];
        if ($current && $current->video) {
            $startOffset = max(0, $now->diffInSeconds($current->start_time));
            if ($startOffset >= $current->duration_seconds) {
                $startOffset = $startOffset % max(1, $current->duration_seconds);
            }

            $playlistQueue[] = [
                'schedule_id' => $current->id,
                'video_id' => $current->video->youtube_id,
                'internal_video_id' => $current->video_id,
                'title' => $current->title,
                'description' => $current->description,
                'format' => $current->format_type,
                'duration_seconds' => $current->duration_seconds,
                'start_seconds' => $startOffset,
                'start_formatted' => $current->start_time->setTimezone($timezone)->format('h:i A'),
                'end_formatted' => $current->end_time->setTimezone($timezone)->format('h:i A'),
                'is_current' => true,
            ];
        }

        foreach ($upcoming as $entry) {
            if ($entry->video) {
                $playlistQueue[] = [
                    'schedule_id' => $entry->id,
                    'video_id' => $entry->video->youtube_id,
                    'internal_video_id' => $entry->video_id,
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'format' => $entry->format_type,
                    'duration_seconds' => $entry->duration_seconds,
                    'start_seconds' => 0,
                    'start_formatted' => $entry->start_time->setTimezone($timezone)->format('h:i A'),
                    'end_formatted' => $entry->end_time->setTimezone($timezone)->format('h:i A'),
                    'is_current' => false,
                ];
            }
        }

        return [
            'channel' => $channel,
            'current' => $current,
            'upcoming' => $upcoming,
            'today_schedule' => $todaySchedule,
            'playlist_queue' => $playlistQueue,
            'reserve_status' => $this->reserveService->getReserveStatus($channel),
        ];
    }
}
