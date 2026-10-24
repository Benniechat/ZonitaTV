<?php

namespace App\Services\Sports;

use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Models\SportsFixture;
use Illuminate\Support\Facades\Log;

class MatchSchedulingService
{
    /**
     * Target 24/7 sports linear channel.
     */
    protected ?Channel $channel = null;

    public function __construct()
    {
        $slug = config('sports.channel_slug', 'zonita-football-24-7');
        $this->channel = Channel::where('slug', $slug)->first();
    }

    /**
     * Get or set the target channel.
     */
    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    /**
     * Synchronize upcoming verified sports fixtures into the 24/7 channel's schedule entries.
     *
     * @return array{scheduled: int, updated: int}
     */
    public function syncFixtureSchedule(): array
    {
        if (! $this->channel) {
            Log::warning('MatchSchedulingService: Target sports channel not found.');

            return ['scheduled' => 0, 'updated' => 0];
        }

        $now = now();
        $windowEnd = $now->copy()->addHours(36);

        // Fetch fixtures in the 36-hour schedule window
        $fixtures = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->where('utc_kickoff', '>=', $now->copy()->subHours(2))
            ->where('utc_kickoff', '<=', $windowEnd)
            ->whereIn('status', ['scheduled', 'timed', 'live', 'in_play', 'paused'])
            ->orderBy('utc_kickoff')
            ->get();

        $scheduledCount = 0;
        $updatedCount = 0;

        foreach ($fixtures as $fixture) {
            $result = $this->scheduleFixture($fixture);
            if ($result === 'created') {
                $scheduledCount++;
            } elseif ($result === 'updated') {
                $updatedCount++;
            }
        }

        return ['scheduled' => $scheduledCount, 'updated' => $updatedCount];
    }

    /**
     * Schedule a single sports fixture into the linear channel EPG / timeline.
     */
    public function scheduleFixture(SportsFixture $fixture): string
    {
        if (! $this->channel) {
            return 'skipped';
        }

        $preBuffer = config('sports.buffers.pre_match_minutes', 15);
        $postBuffer = config('sports.buffers.post_match_minutes', 30);
        $matchDuration = 105; // Standard 90 mins + 15 min halftime

        $startTime = $fixture->utc_kickoff->copy()->subMinutes($preBuffer);
        $endTime = $fixture->utc_kickoff->copy()->addMinutes($matchDuration + $postBuffer);
        $totalDurationSeconds = (int) $startTime->diffInSeconds($endTime);

        $title = sprintf(
            'LIVE: %s vs %s (%s)',
            $fixture->homeTeam?->short_name ?? $fixture->homeTeam?->name ?? 'Home',
            $fixture->awayTeam?->short_name ?? $fixture->awayTeam?->name ?? 'Away',
            $fixture->competition?->name ?? 'Match'
        );

        $description = sprintf(
            '%s fixture broadcast. Kickoff: %s WAT. Venue: %s. Verified Destination: %s.',
            $fixture->competition?->name ?? 'Football',
            $fixture->lagos_kickoff->format('H:i'),
            $fixture->venue ?? 'Main Stadium',
            $fixture->sportyBroadcast?->isConfirmed() ? 'SportyTV' : 'Zonita Sports'
        );

        // Find existing schedule entry or create one
        $existing = ScheduleEntry::where('channel_id', $this->channel->id)
            ->where(function ($query) use ($fixture, $startTime) {
                $query->where('title', 'like', "%{$fixture->homeTeam?->name}%")
                    ->orWhereBetween('start_time', [$startTime->copy()->subMinutes(15), $startTime->copy()->addMinutes(15)]);
            })
            ->first();

        if ($existing) {
            $existing->update([
                'title' => $title,
                'description' => $description,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_seconds' => $totalDurationSeconds,
                'status' => $fixture->is_live_now ? 'live' : 'scheduled',
            ]);

            return 'updated';
        }

        ScheduleEntry::create([
            'channel_id' => $this->channel->id,
            'video_id' => null,
            'title' => $title,
            'description' => $description,
            'format_type' => 'live_sports',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_seconds' => $totalDurationSeconds,
            'play_order' => 1,
            'status' => $fixture->is_live_now ? 'live' : 'scheduled',
            'is_fallback' => false,
        ]);

        return 'created';
    }

    /**
     * Determine if a live match is currently occupying the linear transmission window.
     */
    public function getCurrentlyActiveMatch(): ?SportsFixture
    {
        $now = now();

        return SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->where(function ($query) use ($now) {
                // Either explicitly live in metadata
                $query->whereIn('status', ['live', 'in_play', 'paused'])
                    // Or within scheduled kickoff window: 15 mins before to 135 mins after
                    ->orWhere(function ($q) use ($now) {
                        $q->where('utc_kickoff', '<=', $now->copy()->addMinutes(15))
                            ->where('utc_kickoff', '>=', $now->copy()->subMinutes(135))
                            ->whereNotIn('status', ['finished', 'cancelled', 'postponed']);
                    });
            })
            ->orderBy('utc_kickoff')
            ->first();
    }

    /**
     * Returns whether the channel is currently in an active live sports window.
     */
    public function isLiveWindowActive(): bool
    {
        return $this->getCurrentlyActiveMatch() !== null;
    }
}
