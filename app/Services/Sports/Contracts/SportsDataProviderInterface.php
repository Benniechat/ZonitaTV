<?php

namespace App\Services\Sports\Contracts;

interface SportsDataProviderInterface
{
    /**
     * Get a list of upcoming fixtures across target competitions.
     *
     * @param  array<int, string>  $competitionCodes
     * @return array<int, array<string, mixed>>
     */
    public function getUpcomingFixtures(array $competitionCodes = [], int $daysAhead = 7): array;

    /**
     * Get currently live fixtures in progress.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLiveFixtures(): array;

    /**
     * Fetch updated details for a single fixture by provider ID.
     *
     * @return array<string, mixed>|null
     */
    public function getFixtureDetails(string|int $providerFixtureId): ?array;
}
