<?php

namespace App\Services\Sports;

use App\Models\SportsCompetition;
use App\Models\SportsFixture;
use App\Models\SportsTeam;
use App\Services\Sports\Contracts\SportsDataProviderInterface;
use App\Services\Sports\Providers\FootballDataOrgProvider;
use App\Services\Sports\Providers\MockSportsDataProvider;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FixtureDiscoveryService
{
    protected SportsDataProviderInterface $provider;

    public function __construct(?SportsDataProviderInterface $provider = null)
    {
        if ($provider) {
            $this->provider = $provider;
        } else {
            $configured = config('sports.providers.default', 'mock');
            if ($configured === 'football_data' && config('sports.providers.football_data.api_key')) {
                $this->provider = app(FootballDataOrgProvider::class);
            } else {
                $this->provider = app(MockSportsDataProvider::class);
            }
        }
    }

    /**
     * Discover and sync upcoming and live fixtures into database.
     *
     * @param int $daysAhead
     * @return array<string, int>
     */
    public function syncFixtures(int $daysAhead = 7): array
    {
        $discoveredCount = 0;
        $updatedCount = 0;

        // 1. Fetch upcoming matches
        $upcoming = $this->provider->getUpcomingFixtures(daysAhead: $daysAhead);
        foreach ($upcoming as $item) {
            $result = $this->persistNormalizedFixture($item);
            if ($result['action'] === 'created') {
                $discoveredCount++;
            } elseif ($result['action'] === 'updated') {
                $updatedCount++;
            }
        }

        // 2. Fetch live matches
        $live = $this->provider->getLiveFixtures();
        $liveCount = 0;
        foreach ($live as $item) {
            $result = $this->persistNormalizedFixture($item);
            $liveCount++;
        }

        return [
            'discovered' => $discoveredCount,
            'updated' => $updatedCount,
            'live_in_play' => $liveCount,
        ];
    }

    /**
     * Persist normalized fixture with strict deduplication.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function persistNormalizedFixture(array $data): array
    {
        // 1. Resolve Competition
        $compData = $data['competition'] ?? [];
        $compSlug = Str::slug($compData['name'] ?? 'sports-competition');
        $competition = SportsCompetition::firstOrCreate(
            ['slug' => $compSlug],
            [
                'name' => $compData['name'] ?? 'Competition',
                'code' => $compData['code'] ?? strtoupper(substr($compSlug, 0, 4)),
                'country' => $compData['country'] ?? null,
                'emblem_url' => $compData['emblem_url'] ?? null,
                'provider' => 'sports_engine',
                'provider_id' => $compData['code'] ?? null,
                'is_active' => true,
            ]
        );

        // 2. Resolve Home Team
        $homeData = $data['home_team'] ?? [];
        $homeSlug = Str::slug($homeData['name'] ?? 'home-team');
        $homeTeam = SportsTeam::firstOrCreate(
            ['slug' => $homeSlug],
            [
                'name' => $homeData['name'] ?? 'Home Team',
                'short_name' => $homeData['short_name'] ?? $homeData['name'] ?? 'Home',
                'tla' => $homeData['tla'] ?? strtoupper(substr($homeSlug, 0, 3)),
                'logo_url' => $homeData['logo_url'] ?? null,
                'venue_name' => $homeData['venue'] ?? null,
                'country' => $homeData['country'] ?? null,
                'provider' => 'sports_engine',
            ]
        );

        // 3. Resolve Away Team
        $awayData = $data['away_team'] ?? [];
        $awaySlug = Str::slug($awayData['name'] ?? 'away-team');
        $awayTeam = SportsTeam::firstOrCreate(
            ['slug' => $awaySlug],
            [
                'name' => $awayData['name'] ?? 'Away Team',
                'short_name' => $awayData['short_name'] ?? $awayData['name'] ?? 'Away',
                'tla' => $awayData['tla'] ?? strtoupper(substr($awaySlug, 0, 3)),
                'logo_url' => $awayData['logo_url'] ?? null,
                'venue_name' => $awayData['venue'] ?? null,
                'country' => $awayData['country'] ?? null,
                'provider' => 'sports_engine',
            ]
        );

        // 4. Calculate Timezones (UTC & Africa/Lagos WAT)
        $utcKickoff = Carbon::parse($data['utc_kickoff']);
        $lagosTimezone = config('sports.timezone', 'Africa/Lagos');
        $lagosKickoff = (clone $utcKickoff)->setTimezone($lagosTimezone);

        // 5. Deduplication Lookup:
        // Strategy A: Direct Provider ID match
        $providerId = (string) ($data['provider_id'] ?? '');
        $fixture = null;
        if ($providerId !== '') {
            $fixture = SportsFixture::where('provider_id', $providerId)->first();
        }

        // Strategy B: Secondary matching by Competition + Home + Away + Same Date
        if (! $fixture) {
            $fixture = SportsFixture::where('competition_id', $competition->id)
                ->where('home_team_id', $homeTeam->id)
                ->where('away_team_id', $awayTeam->id)
                ->whereDate('utc_kickoff', $utcKickoff->toDateString())
                ->first();
        }

        $attributes = [
            'competition_id' => $competition->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'provider' => 'sports_engine',
            'provider_id' => $providerId ?: null,
            'utc_kickoff' => $utcKickoff,
            'lagos_kickoff' => $lagosKickoff,
            'status' => $data['status'] ?? 'scheduled',
            'matchday' => $data['matchday'] ?? null,
            'round' => $data['round'] ?? null,
            'home_score' => $data['home_score'] ?? null,
            'away_score' => $data['away_score'] ?? null,
            'match_clock' => $data['match_clock'] ?? null,
            'venue' => $data['venue'] ?? $homeTeam->venue_name,
            'is_featured' => $data['is_featured'] ?? false,
            'is_live' => $data['is_live'] ?? false,
            'last_synced_at' => now(),
            'raw_metadata' => $data['raw_metadata'] ?? null,
        ];

        if ($fixture) {
            $fixture->update($attributes);
            return ['action' => 'updated', 'fixture' => $fixture];
        }

        $fixture = SportsFixture::create($attributes);
        return ['action' => 'created', 'fixture' => $fixture];
    }
}
