<?php

namespace App\Services\Zonita;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Programme;
use App\Models\ScheduleEntry;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProgrammeScheduleService
{
    public function __construct(
        protected ContentRotationService $rotationService,
        protected ChannelReserveService $reserveService
    ) {}

    /**
     * Generates a continuous rolling schedule for a channel (1 to 30 days horizon).
     */
    public function generateRollingSchedule(Channel $channel, int $daysAhead = 7): array
    {
        $daysAhead = max(1, min(30, $daysAhead));
        $now = Carbon::now('UTC');
        $timelineEnd = $now->copy()->addDays($daysAhead)->endOfDay();

        $categorySlug = $channel->categories->first()?->slug;

        // Fetch eligible video candidate pool (prefer channel's curated pool)
        $channelVideoIds = $channel->channelVideos()->pluck('video_id');
        $candidates = collect();

        if ($channelVideoIds->isNotEmpty()) {
            $candidates = Video::eligibleForScheduling()
                ->whereIn('id', $channelVideoIds)
                ->get();
        }

        if ($candidates->isEmpty()) {
            $candidates = Video::eligibleForScheduling()
                ->when($categorySlug, fn ($q) => $q->where('category_slug', $categorySlug))
                ->get();
        }

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

        $timelinePointer = $lastEntry ? Carbon::parse($lastEntry->end_time)->utc() : $now->copy()->subHours(2);
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
                'start_time' => $startTime->utc()->toDateTimeString(),
                'end_time' => $endTime->utc()->toDateTimeString(),
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

        // Keep core Programme records synced so that /guide, /my-guide, and homepage widgets reflect real-time live playout
        $this->syncProgrammesForChannel($channel);

        return [
            'channel' => $channel->slug,
            'entries_created' => $createdCount,
            'status' => 'ok',
        ];
    }

    /**
     * Resolves schedule overlaps, aligns timeline sequence, and fills unintended gaps.
     */
    public function resolveScheduleConflicts(Channel $channel, ?Carbon $fromTime = null, ?Carbon $toTime = null): array
    {
        $from = $fromTime ?: now('UTC')->subHours(2);
        $to = $toTime ?: now('UTC')->addDays(30);

        $entries = ScheduleEntry::where('channel_id', $channel->id)
            ->where('end_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        if ($entries->isEmpty()) {
            return [
                'overlaps_resolved' => 0,
                'gaps_filled' => 0,
                'entries_realigned' => 0,
            ];
        }

        $overlapsResolved = 0;
        $gapsFilled = 0;
        $entriesRealigned = 0;
        $previousEnd = null;
        $currentOrder = 1;

        foreach ($entries as $entry) {
            $start = Carbon::parse($entry->start_time)->utc();
            $duration = max(60, $entry->duration_seconds ?: 1800);

            if ($previousEnd !== null) {
                // Case 1: Overlap (current starts before previous ends)
                if ($start->lessThan($previousEnd)) {
                    $start = $previousEnd->copy();
                    $entry->start_time = $start->toDateTimeString();
                    $entry->end_time = $start->copy()->addSeconds($duration)->toDateTimeString();
                    $entry->duration_seconds = $duration;
                    $entry->play_order = $currentOrder++;
                    $entry->save();
                    $overlapsResolved++;
                    $entriesRealigned++;
                }
                // Case 2: Gap (current starts more than 60s after previous ends)
                elseif ($start->greaterThan($previousEnd) && $previousEnd->diffInSeconds($start) > 60) {
                    $gapSeconds = $previousEnd->diffInSeconds($start);
                    ScheduleEntry::create([
                        'channel_id' => $channel->id,
                        'video_id' => null,
                        'title' => 'Zonita TV Station Interstitial & Continuity',
                        'description' => 'Automated linear continuity gap filler.',
                        'format_type' => 'interstitial',
                        'start_time' => $previousEnd->toDateTimeString(),
                        'end_time' => $start->toDateTimeString(),
                        'duration_seconds' => $gapSeconds,
                        'play_order' => $currentOrder++,
                        'status' => 'scheduled',
                        'is_fallback' => true,
                    ]);
                    $gapsFilled++;
                    $currentOrder++;
                } else {
                    // Slight gap under 60s: align seamlessly
                    if (! $start->equalTo($previousEnd)) {
                        $start = $previousEnd->copy();
                        $entry->start_time = $start->toDateTimeString();
                        $entry->end_time = $start->copy()->addSeconds($duration)->toDateTimeString();
                        $entry->save();
                        $entriesRealigned++;
                    }
                    $currentOrder++;
                }
            } else {
                $currentOrder++;
            }

            $previousEnd = Carbon::parse($entry->end_time)->utc();
        }

        $this->syncProgrammesForChannel($channel);

        return [
            'overlaps_resolved' => $overlapsResolved,
            'gaps_filled' => $gapsFilled,
            'entries_realigned' => $entriesRealigned,
        ];
    }

    /**
     * Automatic rolling schedule replenishment for all active channels.
     */
    public function replenishAllChannels(int $minHoursAhead = 48, int $targetDays = 7): array
    {
        $channels = Channel::where('is_active', true)->get();
        $replenished = [];
        $now = now('UTC');

        foreach ($channels as $channel) {
            $maxEnd = ScheduleEntry::where('channel_id', $channel->id)
                ->where('end_time', '>', $now)
                ->max('end_time');

            $hoursAhead = $maxEnd
                ? round($now->diffInMinutes(Carbon::parse($maxEnd, 'UTC')) / 60, 1)
                : 0;

            if ($hoursAhead < $minHoursAhead) {
                $result = $this->generateRollingSchedule($channel, $targetDays);
                $conflictResult = $this->resolveScheduleConflicts($channel);
                $replenished[] = [
                    'channel' => $channel->slug,
                    'previous_hours' => $hoursAhead,
                    'entries_created' => $result['entries_created'] ?? 0,
                    'conflicts' => $conflictResult,
                ];
            }
        }

        return $replenished;
    }

    /**
     * Synchronizes upcoming ScheduleEntry records into core Programme models for EPG and homepage visibility.
     */
    public function syncProgrammesForChannel(Channel $channel): int
    {
        $now = now();
        $entries = ScheduleEntry::where('channel_id', $channel->id)
            ->where('end_time', '>=', $now->copy()->subHours(2))
            ->where('start_time', '<=', $now->copy()->addDays(7))
            ->orderBy('start_time')
            ->get();

        if ($entries->isEmpty()) {
            return 0;
        }

        $category = $channel->categories->first() ?? Category::where('slug', $channel->category_slug)->first();
        $categoryId = $category?->id;
        $fallbackVideoId = $channel->fallback_video_id;
        $synced = 0;

        foreach ($entries as $entry) {
            $video = $entry->video;
            $videoId = $video?->youtube_id ?: $fallbackVideoId;
            $startTime = Carbon::parse($entry->start_time);
            $endTime = Carbon::parse($entry->end_time);
            $durationMinutes = max(1, (int) round($entry->duration_seconds / 60));
            $isLive = $now->between($startTime, $endTime);

            Programme::updateOrCreate(
                [
                    'channel_id' => $channel->id,
                    'start_time' => $startTime,
                ],
                [
                    'category_id' => $categoryId,
                    'title' => $entry->title,
                    'slug' => Str::slug($entry->title).'-'.$channel->id.'-'.$startTime->format('YmdHi'),
                    'description' => $entry->description ?: ($video?->description ?: $channel->description),
                    'presenter_or_artist' => $channel->broadcaster_name,
                    'poster_image' => $channel->banner_path,
                    'video_source' => 'youtube',
                    'video_id' => $videoId,
                    'video_url' => $videoId ? "https://www.youtube.com/watch?v={$videoId}" : null,
                    'duration_minutes' => $durationMinutes,
                    'play_order' => $entry->play_order,
                    'end_time' => $endTime,
                    'is_live' => $isLive,
                ]
            );

            $synced++;
        }

        return $synced;
    }

    /**
     * Bulk synchronizes all channels that have linear schedule entries into Programme records.
     */
    public function syncAllChannelsToProgrammes(): int
    {
        $channels = Channel::whereHas('scheduleEntries')->get();
        $total = 0;
        foreach ($channels as $channel) {
            $total += $this->syncProgrammesForChannel($channel);
        }

        return $total;
    }

    /**
     * Builds the Electronic Programme Guide (EPG) presentation data.
     */
    public function getEpgData(Channel $channel): array
    {
        $timezone = $channel->sourceRule?->timezone ?? 'Africa/Lagos';
        $now = now('UTC');

        // Auto-replenish schedule if upcoming entries run low to prevent looping or stale schedules
        $futureEntriesCount = ScheduleEntry::where('channel_id', $channel->id)
            ->where('end_time', '>', $now)
            ->count();

        if ($futureEntriesCount < 10 && $channel->channelVideos()->count() >= 2) {
            $this->generateRollingSchedule($channel, 7);
        }

        // Currently Playing
        $current = ScheduleEntry::where('channel_id', $channel->id)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->with('video')
            ->first();

        // If no active slot found (e.g. slight schedule gap), pick the next starting entry or latest
        if (! $current) {
            $current = ScheduleEntry::where('channel_id', $channel->id)
                ->where('start_time', '>=', $now)
                ->orderBy('start_time')
                ->with('video')
                ->first();

            if (! $current) {
                $current = ScheduleEntry::where('channel_id', $channel->id)
                    ->orderByDesc('start_time')
                    ->with('video')
                    ->first();
            }
        }

        // Up Next (generous 24 upcoming entries to guarantee continuous non-stop linear playback)
        $upcoming = ScheduleEntry::where('channel_id', $channel->id)
            ->where('start_time', '>', $current ? $current->start_time : $now)
            ->orderBy('start_time')
            ->take(24)
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
