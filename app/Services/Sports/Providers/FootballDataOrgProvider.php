<?php

namespace App\Services\Sports\Providers;

use App\Services\Sports\Contracts\SportsDataProviderInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballDataOrgProvider implements SportsDataProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('sports.providers.football_data.api_key');
        $this->baseUrl = config('sports.providers.football_data.base_url', 'https://api.football-data.org/v4');
    }

    public function getUpcomingFixtures(array $competitionCodes = [], int $daysAhead = 7): array
    {
        if (empty($this->apiKey)) {
            Log::info('FootballDataOrgProvider: No API key configured. Skipping external request.');

            return [];
        }

        $dateFrom = now()->format('Y-m-d');
        $dateTo = now()->addDays($daysAhead)->format('Y-m-d');

        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->timeout(15)->get("{$this->baseUrl}/matches", [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'status' => 'SCHEDULED,TIMED',
            ]);

            if (! $response->successful()) {
                Log::warning('FootballDataOrgProvider API error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return [];
            }

            $data = $response->json();
            $matches = $data['matches'] ?? [];

            $normalized = [];
            foreach ($matches as $m) {
                $normalized[] = $this->normalizeMatch($m);
            }

            return $normalized;
        } catch (\Throwable $e) {
            Log::error('FootballDataOrgProvider exception: '.$e->getMessage());

            return [];
        }
    }

    public function getLiveFixtures(): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->timeout(15)->get("{$this->baseUrl}/matches", [
                'status' => 'IN_PLAY,PAUSED',
            ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $matches = $data['matches'] ?? [];

            $normalized = [];
            foreach ($matches as $m) {
                $normalized[] = $this->normalizeMatch($m);
            }

            return $normalized;
        } catch (\Throwable $e) {
            Log::error('FootballDataOrgProvider live check exception: '.$e->getMessage());

            return [];
        }
    }

    public function getFixtureDetails(string|int $providerFixtureId): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->timeout(15)->get("{$this->baseUrl}/matches/{$providerFixtureId}");

            if (! $response->successful()) {
                return null;
            }

            return $this->normalizeMatch($response->json());
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Normalize raw Football-Data.org API response to canonical schema.
     */
    protected function normalizeMatch(array $m): array
    {
        $utc = Carbon::parse($m['utcDate'] ?? now());

        $status = match ($m['status'] ?? 'SCHEDULED') {
            'IN_PLAY' => 'in_play',
            'PAUSED' => 'paused',
            'FINISHED' => 'finished',
            'POSTPONED' => 'postponed',
            'CANCELLED' => 'cancelled',
            'SUSPENDED' => 'suspended',
            default => 'scheduled',
        };

        return [
            'provider_id' => (string) ($m['id'] ?? uniqid('fd_')),
            'competition' => [
                'code' => $m['competition']['code'] ?? 'OTHER',
                'name' => $m['competition']['name'] ?? 'Competition',
                'emblem_url' => $m['competition']['emblem'] ?? null,
                'country' => $m['area']['name'] ?? null,
            ],
            'home_team' => [
                'name' => $m['homeTeam']['name'] ?? 'Home Team',
                'short_name' => $m['homeTeam']['shortName'] ?? $m['homeTeam']['name'] ?? 'Home',
                'tla' => $m['homeTeam']['tla'] ?? null,
                'logo_url' => $m['homeTeam']['crest'] ?? null,
                'venue' => $m['venue'] ?? null,
            ],
            'away_team' => [
                'name' => $m['awayTeam']['name'] ?? 'Away Team',
                'short_name' => $m['awayTeam']['shortName'] ?? $m['awayTeam']['name'] ?? 'Away',
                'tla' => $m['awayTeam']['tla'] ?? null,
                'logo_url' => $m['awayTeam']['crest'] ?? null,
                'venue' => null,
            ],
            'utc_kickoff' => $utc->format('Y-m-d H:i:s'),
            'status' => $status,
            'matchday' => $m['matchday'] ?? null,
            'round' => $m['stage'] ?? null,
            'home_score' => $m['score']['fullTime']['home'] ?? $m['score']['halfTime']['home'] ?? null,
            'away_score' => $m['score']['fullTime']['away'] ?? $m['score']['halfTime']['away'] ?? null,
            'match_clock' => null,
            'venue' => $m['venue'] ?? null,
            'is_featured' => true,
            'is_live' => in_array($status, ['in_play', 'paused']),
            'raw_metadata' => $m,
        ];
    }
}
