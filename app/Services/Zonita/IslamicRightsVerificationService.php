<?php

namespace App\Services\Zonita;

use App\Models\IslamicContentProvider;
use Illuminate\Support\Facades\Log;

class IslamicRightsVerificationService
{
    /**
     * Permitted source classifications eligible for linear virtual broadcasting.
     */
    protected array $permittedPlayoutClassifications = [
        'OFFICIAL_CONTENT_OWNER',
        'AUTHORIZED_DISTRIBUTOR',
        'LICENSED_STREAMING_PROVIDER',
        'OPENLY_LICENSED_SOURCE',
        'OFFICIAL_EMBED_ONLY',
    ];

    /**
     * Classifications that are strictly for discovery and must NEVER enter playout.
     */
    protected array $discoveryOnlyClassifications = [
        'DISCOVERY_ONLY',
        'UNVERIFIED_SOURCE',
        'RESTRICTED_SOURCE',
        'PUBLIC_DOMAIN_CANDIDATE',
    ];

    /**
     * Evaluates an Islamic content provider or asset for broadcast eligibility.
     */
    public function auditProviderForBroadcasting(IslamicContentProvider $provider): array
    {
        $isEligible = true;
        $reasons = [];

        // 1. Source Classification Check
        if (in_array($provider->source_classification, $this->discoveryOnlyClassifications)) {
            $isEligible = false;
            $reasons[] = "Classification is {$provider->source_classification}. Requires direct licensing outreach before broadcast.";
        }

        // 2. Verification Status Check
        if ($provider->verification_status === 'UNVERIFIED') {
            $isEligible = false;
            $reasons[] = 'Provider authenticity has not been independently verified.';
        }

        // 3. Territorial Clearance (Zonita TV requires Nigeria or Global coverage)
        $permittedTerritories = $provider->permitted_territories ?? ['GLOBAL', 'NGA'];
        if (! in_array('GLOBAL', $permittedTerritories) && ! in_array('NGA', $permittedTerritories)) {
            $isEligible = false;
            $reasons[] = 'Territorial restriction: Nigeria is not in permitted territories.';
        }

        // 4. Commercial Use Permission (if channel is monetised)
        if (! $provider->commercial_use_permitted && in_array($provider->source_classification, ['OFFICIAL_CONTENT_OWNER', 'AUTHORIZED_DISTRIBUTOR'])) {
            $reasons[] = 'Notice: Commercial use requires specific ad-free or revenue-sharing arrangement.';
        }

        Log::info("Islamic Rights Audit for [{$provider->name}]: eligible=".($isEligible ? 'true' : 'false').', status='.$provider->licensing_status);

        return [
            'provider_id' => $provider->id,
            'name' => $provider->name,
            'is_eligible' => $isEligible,
            'classification' => $provider->source_classification,
            'licensing_status' => $provider->licensing_status,
            'reasons' => $reasons,
        ];
    }

    /**
     * Checks if a video candidate can be scheduled on a virtual channel.
     */
    public function isCandidateSchedulable(array $candidate, IslamicContentProvider $provider): bool
    {
        // Discovery-only providers cannot schedule content into the broadcasting queue
        if ($provider->source_classification === 'DISCOVERY_ONLY' || $provider->source_classification === 'UNVERIFIED_SOURCE') {
            return false;
        }

        // Must have a valid identifier
        if (empty($candidate['youtube_id'])) {
            return false;
        }

        return true;
    }
}
