<?php

namespace App\Services;

use App\Models\Channel;
use Carbon\Carbon;

class RightsEligibilityService
{
    /**
     * Evaluates whether a channel can be listed publicly in the directory.
     */
    public function canList(Channel $channel, ?string $clientCountry = 'NGA'): bool
    {
        if (! $channel->is_active || ! $channel->is_published) {
            return false;
        }

        $rights = $channel->latestRights;
        if (! $rights) {
            return false;
        }

        if (in_array($rights->status, ['rejected', 'pending_review', 'expired'])) {
            return false;
        }

        if ($rights->expires_at && Carbon::parse($rights->expires_at)->isPast()) {
            return false;
        }

        if (! $rights->allow_directory_listing) {
            return false;
        }

        return $this->isTerritoryAllowed($rights, $clientCountry);
    }

    /**
     * Determines the exact playback mode permissible for this viewer.
     */
    public function resolvePermittedPlayback(Channel $channel, ?string $clientCountry = 'NGA'): array
    {
        if (! $this->canList($channel, $clientCountry)) {
            return [
                'allowed' => false,
                'mode' => 'none',
                'reason' => 'Listing not permitted or permissions have expired.',
                'destination_url' => null,
                'stream' => null,
            ];
        }

        $rights = $channel->latestRights;
        $primaryStream = $channel->primaryStream;

        // Mode D: Zonita Originals
        if ($channel->playback_mode === 'zonita_original') {
            return [
                'allowed' => true,
                'mode' => 'zonita_original',
                'stream' => $primaryStream,
                'destination_url' => null,
            ];
        }

        // Mode A: Direct HLS Playback inside Zonita TV
        if ($channel->playback_mode === 'direct_stream' && $rights->allow_direct_hls && $primaryStream?->stream_url) {
            return [
                'allowed' => true,
                'mode' => 'direct_stream',
                'stream' => $primaryStream,
                'destination_url' => null,
            ];
        }

        // Mode B: Official Broadcaster Embed Player
        if ($channel->playback_mode === 'embed_player' && $rights->allow_web_embedding && ($primaryStream?->embed_code || $primaryStream?->stream_url)) {
            return [
                'allowed' => true,
                'mode' => 'embed_player',
                'stream' => $primaryStream,
                'destination_url' => null,
            ];
        }

        // Mode C: Fallback to Official Broadcaster Destination URL
        return [
            'allowed' => true,
            'mode' => 'external_destination',
            'destination_url' => $channel->official_destination_url,
            'stream' => null,
        ];
    }

    private function isTerritoryAllowed($rights, ?string $clientCountry): bool
    {
        if (empty($rights->permitted_territories) || in_array('GLOBAL', $rights->permitted_territories)) {
            if (! empty($rights->blocked_territories) && in_array($clientCountry, $rights->blocked_territories)) {
                return false;
            }

            return true;
        }

        return in_array($clientCountry, $rights->permitted_territories);
    }
}
