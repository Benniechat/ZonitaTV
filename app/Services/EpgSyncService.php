<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Programme;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class EpgSyncService
{
    /**
     * Download and parse an XMLTV EPG feed from a remote URL
     */
    public function syncFromUrl(string $xmltvUrl): array
    {
        $response = Http::timeout(60)
            ->withoutVerifying()
            ->withHeaders(['User-Agent' => 'ZonitaTV-EpgSync/1.0'])
            ->get($xmltvUrl);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to fetch EPG from {$xmltvUrl}. Status: {$response->status()}");
        }

        return $this->parseXmltv($response->body());
    }

    /**
     * Parse raw XMLTV string and sync into the programmes table
     */
    public function parseXmltv(string $xmlContent): array
    {
        $syncedCount = 0;
        $skippedCount = 0;

        try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (Throwable $e) {
            throw new \RuntimeException('Invalid XMLTV format: '.$e->getMessage());
        }

        // Cache channels by tvg_id and slug for fast lookup
        $channels = Channel::whereNotNull('tvg_id')
            ->orWhereNotNull('slug')
            ->get();

        $byTvgId = $channels->whereNotNull('tvg_id')->keyBy('tvg_id');
        $bySlug = $channels->keyBy('slug');

        $now = now();

        foreach ($xml->programme as $progNode) {
            $epgChannelId = (string) $progNode['channel'];
            $startStr = (string) $progNode['start'];
            $stopStr = (string) $progNode['stop'];

            $channel = $byTvgId->get($epgChannelId) ?? $bySlug->get(Str::slug($epgChannelId));

            if (! $channel) {
                $skippedCount++;

                continue;
            }

            $title = trim((string) ($progNode->title ?? 'Scheduled Broadcast'));
            $desc = trim((string) ($progNode->desc ?? ''));

            $startTime = $this->parseXmltvDate($startStr);
            $endTime = $this->parseXmltvDate($stopStr);

            if (! $startTime || ! $endTime) {
                $skippedCount++;

                continue;
            }

            $durationMinutes = max(15, (int) round($startTime->diffInMinutes($endTime)));
            $isLive = $now->between($startTime, $endTime);

            Programme::updateOrCreate(
                [
                    'channel_id' => $channel->id,
                    'start_time' => $startTime,
                ],
                [
                    'category_id' => $channel->categories->first()?->id,
                    'title' => $title,
                    'slug' => Str::slug($title.'-'.$channel->id.'-'.$startTime->timestamp),
                    'description' => $desc ?: "Live broadcast and electronic guide programming for {$channel->name}.",
                    'end_time' => $endTime,
                    'duration_minutes' => $durationMinutes,
                    'is_live' => $isLive,
                ]
            );

            $syncedCount++;
        }

        return [
            'synced' => $syncedCount,
            'skipped' => $skippedCount,
        ];
    }

    /**
     * Parse XMLTV timestamp: YYYYMMDDHHMMSS +/-ZZZZ
     */
    public function parseXmltvDate(string $rawDate): ?Carbon
    {
        $rawDate = trim($rawDate);
        if (empty($rawDate)) {
            return null;
        }

        try {
            // Check for timezone offset e.g. "20261018080000 +0100"
            if (preg_match('/^(\d{14})(?:\s*([+-]\d{4}))?$/', $rawDate, $m)) {
                $base = $m[1];
                $tz = $m[2] ?? '+0000';

                return Carbon::createFromFormat('YmdHis O', $base.' '.$tz)->setTimezone(config('app.timezone', 'UTC'));
            }

            return Carbon::parse($rawDate);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Dynamically generates rolling 24-hour programme guides for channels
     * so that all channels always have an accurate, active schedule.
     */
    public function generateDynamicSchedulesForAllChannels(): int
    {
        $channels = Channel::where('is_active', true)->where('is_published', true)->get();
        $totalCreated = 0;

        foreach ($channels as $channel) {
            $totalCreated += $this->generateSchedulesForChannel($channel);
        }

        return $totalCreated;
    }

    /**
     * Generate continuous blocks of programming for a single channel for today and tomorrow
     */
    public function generateSchedulesForChannel(Channel $channel): int
    {
        $category = $channel->categories->first();
        $catName = $category?->name ?? 'General';
        $now = now();

        $scheduleTemplates = match ($category?->slug) {
            'sports' => [
                ['title' => 'Morning Sports Digest & Fixture Preview', 'hours' => 2, 'desc' => 'Comprehensive look at weekend fixtures, tactical talking points, and team lineups.'],
                ['title' => 'Football Tactical Breakdown Live', 'hours' => 2, 'desc' => 'Pundit panel analysing recent European and African league clashes.'],
                ['title' => 'World of Champions Football Hub', 'hours' => 3, 'desc' => 'Live continuous match commentary, goal highlights, and league table standings.'],
                ['title' => 'Prime Time Match Analysis & Post-Game', 'hours' => 2, 'desc' => 'Post-match reactions, manager press conferences, and player ratings.'],
                ['title' => 'Late Night Football Highlights Replay', 'hours' => 3, 'desc' => 'Catch up on all the goals and game-defining moments from today.'],
            ],
            'news' => [
                ['title' => 'Zonita Morning Briefing & Headlines', 'hours' => 2, 'desc' => 'Top Nigerian, African and world news stories kickstarting the morning.'],
                ['title' => 'National Press Review & Discourse', 'hours' => 2, 'desc' => 'Daily analysis of national newspapers, policy decisions, and community reports.'],
                ['title' => 'Midday Live Newsroom Bulletin', 'hours' => 2, 'desc' => 'Rolling news coverage with live reports from correspondents nationwide.'],
                ['title' => 'Prime Time News at 7', 'hours' => 2, 'desc' => 'In-depth investigative reports, political analysis, and special broadcasts.'],
                ['title' => 'World News Tonight & Economic Wrap', 'hours' => 4, 'desc' => 'Global market updates, African trade insights, and diplomatic summaries.'],
            ],
            'christian-tv' => [
                ['title' => 'Morning Devotion & Gospel Prayer Hour', 'hours' => 2, 'desc' => 'Start the day with praise, Scripture meditation, and faith declarations.'],
                ['title' => 'Word of Faith & Biblical Exposition', 'hours' => 2, 'desc' => 'Deep verse-by-verse teaching of Scripture by verified Christian ministries.'],
                ['title' => 'Worship Unplugged: Live Praise Encounters', 'hours' => 2, 'desc' => 'Continuous gospel worship and choral praise sessions.'],
                ['title' => 'Evening Revival Sermon & Intercession', 'hours' => 3, 'desc' => 'Keynote sermon series, testimonies of transformation, and healing prayers.'],
                ['title' => 'Night Watch Prayer & Quiet Reflections', 'hours' => 3, 'desc' => 'Peaceful Christian meditations and nocturnal prayer broadcasts.'],
            ],
            'education' => [
                ['title' => 'STEM Discovery & Young Inventors', 'hours' => 2, 'desc' => 'Educational science, coding, and engineering explorations for students.'],
                ['title' => 'African Heritage & Historical Journeys', 'hours' => 2, 'desc' => 'Documentary series examining African civilisations, art, and independence.'],
                ['title' => 'The Living Planet: Nature & Wildlife', 'hours' => 2, 'desc' => 'Fascinating environmental investigations and wildlife habitats.'],
                ['title' => 'Tech Horizons & Global Innovations', 'hours' => 2, 'desc' => 'How artificial intelligence and green energy are transforming industry.'],
                ['title' => 'Late Documentaries: Deep Space & Astronomy', 'hours' => 4, 'desc' => 'Exploration of galaxies, planetary science, and cosmology.'],
            ],
            default => [
                ['title' => 'Sunrise Morning Show', 'hours' => 2, 'desc' => 'Lifestyle, music, and lighthearted discussion for the morning audience.'],
                ['title' => 'Cultural Showcase & Music Videos', 'hours' => 3, 'desc' => 'Top trending Afrobeat tracks, music videos, and artist interviews.'],
                ['title' => 'Cinema Hour: Nollywood Feature Film', 'hours' => 3, 'desc' => 'Award-winning Nollywood feature drama and storytelling.'],
                ['title' => 'Prime Entertainment Tonight', 'hours' => 2, 'desc' => 'Celebrity interviews, fashion highlights, and red carpet coverage.'],
                ['title' => 'Midnight Groove', 'hours' => 2, 'desc' => 'Smooth nocturnal soundtrack and cultural retrospectives.'],
            ]
        };

        $created = 0;
        $dayStart = $now->copy()->startOfDay();

        // Populate a 48-hour continuous cycle (today + tomorrow)
        $cursor = $dayStart->copy();
        $limitTime = $dayStart->copy()->addHours(48);

        while ($cursor->lessThan($limitTime)) {
            foreach ($scheduleTemplates as $tpl) {
                if ($cursor->greaterThanOrEqualTo($limitTime)) {
                    break;
                }

                $startTime = $cursor->copy();
                $endTime = $cursor->copy()->addHours($tpl['hours']);
                $duration = $tpl['hours'] * 60;
                $isLive = $now->between($startTime, $endTime);

                Programme::updateOrCreate(
                    [
                        'channel_id' => $channel->id,
                        'start_time' => $startTime,
                    ],
                    [
                        'category_id' => $category?->id,
                        'title' => "{$channel->name}: {$tpl['title']}",
                        'slug' => Str::slug("{$channel->name}-{$tpl['title']}-".$startTime->timestamp),
                        'description' => $tpl['desc'],
                        'end_time' => $endTime,
                        'duration_minutes' => $duration,
                        'is_live' => $isLive,
                    ]
                );

                $created++;
                $cursor = $endTime;
            }
        }

        return $created;
    }
}
