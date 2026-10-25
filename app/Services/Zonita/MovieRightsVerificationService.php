<?php

namespace App\Services\Zonita;

use App\Models\Movie;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MovieRightsVerificationService
{
    /**
     * Verifies the legal rights, broadcast permissions, and playout eligibility of a movie.
     */
    public function verifyMovieRights(Movie $movie): array
    {
        $flags = [];
        $isSchedulable = true;
        $status = 'verified';
        $reason = 'All statutory rights and playout criteria verified.';

        // 1. Rights Classification Check
        switch ($movie->rights_classification) {
            case 'public_domain':
                $flags['rights_type'] = 'Public Domain (Statutory / Non-Renewal)';
                // Public domain works inherently permit continuous broadcast and public performance
                $movie->commercial_use_permitted = true;
                $movie->redistribution_permitted = true;
                $movie->public_performance_permitted = true;
                break;

            case 'authorized_distributor':
            case 'direct_licence':
                $flags['rights_type'] = 'Authorized Distributor / Direct Licensor Agreement';
                if (! $movie->licence_reference) {
                    $isSchedulable = false;
                    $status = 'rights_rejected';
                    $reason = 'Missing required licence agreement or contract reference.';
                    $flags['contract'] = 'Missing reference';
                }
                break;

            case 'creative_commons':
                $flags['rights_type'] = 'Creative Commons Open Licence';
                // Check for non-commercial restrictions
                if (stripos($movie->licence_name, 'NC') !== false) {
                    $movie->commercial_use_permitted = false;
                    $flags['commercial_restriction'] = 'Non-commercial clause detected';
                }
                break;

            case 'under_review':
                $isSchedulable = false;
                $status = 'pending_verification';
                $reason = 'Movie is currently under legal and copyright review.';
                break;

            case 'restricted':
            default:
                $isSchedulable = false;
                $status = 'rights_rejected';
                $reason = 'Broadcast and redistribution rights restricted or unavailable for this title.';
                break;
        }

        // 2. Licence Expiration Check
        if ($movie->licence_expires_at && Carbon::parse($movie->licence_expires_at)->isPast()) {
            $isSchedulable = false;
            $status = 'expired';
            $reason = 'Licence agreement has expired on '.$movie->licence_expires_at->toFormattedDateString();
            $flags['expiry'] = 'Expired';
        }

        // 3. Territorial Clearance Check (Zonita TV requires Nigeria or Global clearance)
        $permittedTerritories = $movie->permitted_territories ?? ['GLOBAL'];
        $blockedTerritories = $movie->blocked_territories ?? [];

        if (in_array('NGA', $blockedTerritories) || in_array('ALL', $blockedTerritories)) {
            $isSchedulable = false;
            $status = 'rights_rejected';
            $reason = 'Geographical restriction: Title is explicitly geo-blocked in Nigeria.';
            $flags['territory'] = 'Blocked in Nigeria';
        } elseif (! in_array('GLOBAL', $permittedTerritories) && ! in_array('NGA', $permittedTerritories)) {
            $isSchedulable = false;
            $status = 'rights_rejected';
            $reason = 'Territorial restriction: Nigeria is not listed in permitted territories.';
            $flags['territory'] = 'Nigeria not permitted';
        }

        // 4. Source & Playback Verification
        if (empty($movie->source_url) && empty($movie->source_identifier)) {
            $isSchedulable = false;
            $status = 'failed_verification';
            $reason = 'Missing source URL or media identifier.';
            $flags['source'] = 'Unreachable';
        }

        // Update movie record
        $movie->verification_status = $status;
        $movie->is_schedulable = $isSchedulable;
        $movie->last_verified_at = now();
        $movie->notes = $reason;
        $movie->save();

        Log::info("Movie rights verification for [{$movie->title}]: status={$status}, schedulable=".($isSchedulable ? 'yes' : 'no'));

        return [
            'movie_id' => $movie->id,
            'title' => $movie->title,
            'status' => $status,
            'is_schedulable' => $isSchedulable,
            'reason' => $reason,
            'flags' => $flags,
        ];
    }
}
