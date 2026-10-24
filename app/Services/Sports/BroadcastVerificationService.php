<?php

namespace App\Services\Sports;

use App\Models\FixtureBroadcast;
use App\Models\SportsBroadcastRight;
use App\Models\SportsFixture;

class BroadcastVerificationService
{
    public function __construct(
        protected SportyTVScheduleProvider $sportyProvider
    ) {}

    /**
     * Verify broadcast destinations and operating modes for unverified or upcoming fixtures.
     *
     * @return array<string, int>
     */
    public function verifyPendingFixtures(int $limit = 50): array
    {
        $fixtures = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->where(function ($q) {
                $q->whereDoesntHave('broadcasts')
                    ->orWhereHas('broadcasts', function ($sub) {
                        $sub->where('verification_status', 'unverified')
                            ->orWhere('verification_status', 'discovered');
                    });
            })
            ->where('utc_kickoff', '>=', now()->subHours(4))
            ->orderBy('utc_kickoff', 'asc')
            ->take($limit)
            ->get();

        $confirmed = 0;
        $unconfirmed = 0;
        $modeCPermitted = 0;

        foreach ($fixtures as $fixture) {
            $coverage = $this->sportyProvider->checkCoverage($fixture);

            if ($coverage) {
                $broadcast = $this->attachVerifiedBroadcast($fixture, $coverage);
                $confirmed++;
                if ($broadcast->operating_mode === 'mode_c_rebroadcast') {
                    $modeCPermitted++;
                }
            } else {
                $this->recordUnconfirmedBroadcast($fixture);
                $unconfirmed++;
            }
        }

        return [
            'checked' => $fixtures->count(),
            'confirmed' => $confirmed,
            'unconfirmed' => $unconfirmed,
            'mode_c_permitted' => $modeCPermitted,
        ];
    }

    /**
     * Alias for verifying upcoming fixtures.
     *
     * @return array<string, int>
     */
    public function verifyUpcomingFixtures(int $limit = 50): array
    {
        return $this->verifyPendingFixtures($limit);
    }

    /**
     * Force re-verification of a specific fixture.
     */
    public function reverifyFixture(SportsFixture $fixture): FixtureBroadcast
    {
        $coverage = $this->sportyProvider->checkCoverage($fixture);

        if ($coverage) {
            return $this->attachVerifiedBroadcast($fixture, $coverage);
        }

        return $this->recordUnconfirmedBroadcast($fixture);
    }

    /**
     * Attach and enforce statutory rights for a confirmed broadcast.
     *
     * @param  array<string, mixed>  $coverage
     */
    public function attachVerifiedBroadcast(SportsFixture $fixture, array $coverage): FixtureBroadcast
    {
        // Check active rights agreement
        $activeRight = SportsBroadcastRight::where('rights_holder', 'SportyTV')
            ->where('status', 'approved')
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->first();

        $operatingMode = 'mode_a_link';
        $allowEmbedding = false;
        $allowRedistribution = false;
        $verificationStatus = 'confirmed';

        if ($activeRight) {
            if ($activeRight->allow_mode_c) {
                $operatingMode = 'mode_c_rebroadcast';
                $allowRedistribution = true;
                $verificationStatus = 'authorised_for_distribution';
            } elseif ($activeRight->allow_mode_b || ($coverage['allow_embedding'] ?? false)) {
                $operatingMode = 'mode_b_embed';
                $allowEmbedding = true;
            }
        }

        return FixtureBroadcast::updateOrCreate(
            [
                'fixture_id' => $fixture->id,
                'broadcaster_name' => $coverage['broadcaster_name'] ?? 'SportyTV',
            ],
            [
                'source_url' => $coverage['source_url'] ?? null,
                'feed_endpoint' => $coverage['feed_endpoint'] ?? null,
                'operating_mode' => $operatingMode,
                'verification_status' => $verificationStatus,
                'territory' => $coverage['territory'] ?? 'NGA',
                'allow_embedding' => $allowEmbedding,
                'allow_redistribution' => $allowRedistribution,
                'rights_reference' => $activeRight?->document_reference,
                'verified_at' => now(),
                'expires_at' => (clone $fixture->utc_kickoff)->addHours(4),
                'notes' => $coverage['notes'] ?? 'Verified via SportyTV scheduling provider.',
            ]
        );
    }

    /**
     * Record an unconfirmed broadcast placeholder.
     */
    public function recordUnconfirmedBroadcast(SportsFixture $fixture): FixtureBroadcast
    {
        return FixtureBroadcast::updateOrCreate(
            [
                'fixture_id' => $fixture->id,
                'broadcaster_name' => 'SportyTV',
            ],
            [
                'verification_status' => 'not_confirmed',
                'operating_mode' => 'mode_a_link',
                'verified_at' => now(),
                'notes' => 'No confirmed SportyTV announcement found for this fixture.',
            ]
        );
    }
}
