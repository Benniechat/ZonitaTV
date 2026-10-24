<?php

namespace App\Services\Sports\Providers;

use App\Services\Sports\Contracts\SportsDataProviderInterface;

class MockSportsDataProvider implements SportsDataProviderInterface
{
    /**
     * Generate authentic mock upcoming football fixtures.
     */
    public function getUpcomingFixtures(array $competitionCodes = [], int $daysAhead = 7): array
    {
        $now = now();

        $fixtures = [
            [
                'provider_id' => 'mock-pl-001',
                'competition' => [
                    'code' => 'PL',
                    'name' => 'Premier League',
                    'country' => 'England',
                    'emblem_url' => 'https://crests.football-data.org/PL.png',
                ],
                'home_team' => [
                    'name' => 'Arsenal FC',
                    'short_name' => 'Arsenal',
                    'tla' => 'ARS',
                    'logo_url' => 'https://crests.football-data.org/57.png',
                    'venue' => 'Emirates Stadium',
                    'country' => 'England',
                ],
                'away_team' => [
                    'name' => 'Chelsea FC',
                    'short_name' => 'Chelsea',
                    'tla' => 'CHE',
                    'logo_url' => 'https://crests.football-data.org/61.png',
                    'venue' => 'Stamford Bridge',
                    'country' => 'England',
                ],
                'utc_kickoff' => (clone $now)->addHours(2)->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
                'matchday' => 12,
                'round' => 'Regular Season',
                'venue' => 'Emirates Stadium, London',
                'is_featured' => true,
            ],
            [
                'provider_id' => 'mock-pl-002',
                'competition' => [
                    'code' => 'PL',
                    'name' => 'Premier League',
                    'country' => 'England',
                    'emblem_url' => 'https://crests.football-data.org/PL.png',
                ],
                'home_team' => [
                    'name' => 'Manchester City FC',
                    'short_name' => 'Man City',
                    'tla' => 'MCI',
                    'logo_url' => 'https://crests.football-data.org/65.png',
                    'venue' => 'Etihad Stadium',
                    'country' => 'England',
                ],
                'away_team' => [
                    'name' => 'Liverpool FC',
                    'short_name' => 'Liverpool',
                    'tla' => 'LIV',
                    'logo_url' => 'https://crests.football-data.org/64.png',
                    'venue' => 'Anfield',
                    'country' => 'England',
                ],
                'utc_kickoff' => (clone $now)->addHours(5)->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
                'matchday' => 12,
                'round' => 'Regular Season',
                'venue' => 'Etihad Stadium, Manchester',
                'is_featured' => true,
            ],
            [
                'provider_id' => 'mock-cl-003',
                'competition' => [
                    'code' => 'CL',
                    'name' => 'UEFA Champions League',
                    'country' => 'Europe',
                    'emblem_url' => 'https://crests.football-data.org/CL.png',
                ],
                'home_team' => [
                    'name' => 'Real Madrid CF',
                    'short_name' => 'Real Madrid',
                    'tla' => 'RMA',
                    'logo_url' => 'https://crests.football-data.org/86.png',
                    'venue' => 'Santiago Bernabéu',
                    'country' => 'Spain',
                ],
                'away_team' => [
                    'name' => 'FC Bayern München',
                    'short_name' => 'Bayern Munich',
                    'tla' => 'FCB',
                    'logo_url' => 'https://crests.football-data.org/5.png',
                    'venue' => 'Allianz Arena',
                    'country' => 'Germany',
                ],
                'utc_kickoff' => (clone $now)->addDays(1)->setTime(19, 0)->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
                'matchday' => 4,
                'round' => 'Group Stage',
                'venue' => 'Santiago Bernabéu, Madrid',
                'is_featured' => true,
            ],
            [
                'provider_id' => 'mock-pd-004',
                'competition' => [
                    'code' => 'PD',
                    'name' => 'La Liga',
                    'country' => 'Spain',
                    'emblem_url' => 'https://crests.football-data.org/laliga.png',
                ],
                'home_team' => [
                    'name' => 'FC Barcelona',
                    'short_name' => 'Barcelona',
                    'tla' => 'BAR',
                    'logo_url' => 'https://crests.football-data.org/81.png',
                    'venue' => 'Camp Nou',
                    'country' => 'Spain',
                ],
                'away_team' => [
                    'name' => 'Club Atlético de Madrid',
                    'short_name' => 'Atlético',
                    'tla' => 'ATM',
                    'logo_url' => 'https://crests.football-data.org/78.png',
                    'venue' => 'Metropolitano',
                    'country' => 'Spain',
                ],
                'utc_kickoff' => (clone $now)->addDays(2)->setTime(20, 0)->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
                'matchday' => 11,
                'round' => 'Regular Season',
                'venue' => 'Camp Nou, Barcelona',
                'is_featured' => false,
            ],
            [
                'provider_id' => 'mock-afcon-005',
                'competition' => [
                    'code' => 'AFCON',
                    'name' => 'Africa Cup of Nations',
                    'country' => 'Africa',
                    'emblem_url' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=200&q=80',
                ],
                'home_team' => [
                    'name' => 'Nigeria (Super Eagles)',
                    'short_name' => 'Nigeria',
                    'tla' => 'NGA',
                    'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/thumb/8/87/Nigeria_FA.svg/300px-Nigeria_FA.svg.png',
                    'venue' => 'Godswill Akpabio International Stadium',
                    'country' => 'Nigeria',
                ],
                'away_team' => [
                    'name' => 'Ivory Coast (The Elephants)',
                    'short_name' => 'Ivory Coast',
                    'tla' => 'CIV',
                    'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/thumb/f/f6/Ivorian_Football_Federation_logo.svg/300px-Ivorian_Football_Federation_logo.svg.png',
                    'venue' => 'Stade Félix Houphouët-Boigny',
                    'country' => 'Ivory Coast',
                ],
                'utc_kickoff' => (clone $now)->addDays(3)->setTime(16, 0)->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
                'matchday' => 1,
                'round' => 'Qualifiers',
                'venue' => 'Godswill Akpabio Stadium, Uyo',
                'is_featured' => true,
            ],
        ];

        return $fixtures;
    }

    /**
     * Generate a mock live fixture currently in play.
     */
    public function getLiveFixtures(): array
    {
        $now = now();

        return [
            [
                'provider_id' => 'mock-live-999',
                'competition' => [
                    'code' => 'PL',
                    'name' => 'Premier League',
                    'country' => 'England',
                    'emblem_url' => 'https://crests.football-data.org/PL.png',
                ],
                'home_team' => [
                    'name' => 'Liverpool FC',
                    'short_name' => 'Liverpool',
                    'tla' => 'LIV',
                    'logo_url' => 'https://crests.football-data.org/64.png',
                    'venue' => 'Anfield',
                    'country' => 'England',
                ],
                'away_team' => [
                    'name' => 'Manchester United FC',
                    'short_name' => 'Man United',
                    'tla' => 'MUN',
                    'logo_url' => 'https://crests.football-data.org/66.png',
                    'venue' => 'Old Trafford',
                    'country' => 'England',
                ],
                'utc_kickoff' => (clone $now)->subMinutes(58)->format('Y-m-d H:i:s'),
                'status' => 'in_play',
                'matchday' => 12,
                'round' => 'Regular Season',
                'home_score' => 2,
                'away_score' => 1,
                'match_clock' => '58\'',
                'venue' => 'Anfield, Liverpool',
                'is_featured' => true,
                'is_live' => true,
            ],
        ];
    }

    public function getFixtureDetails(string|int $providerFixtureId): ?array
    {
        $all = array_merge($this->getUpcomingFixtures(), $this->getLiveFixtures());

        foreach ($all as $item) {
            if ($item['provider_id'] === (string) $providerFixtureId) {
                return $item;
            }
        }

        return null;
    }
}
