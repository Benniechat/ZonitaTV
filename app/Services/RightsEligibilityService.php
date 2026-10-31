<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ContentRight;
use App\Models\ScheduleEntry;
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

        if ($channel->isVirtual() || $channel->playback_mode === 'zoniva_original') {
            return true;
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

        // Mode D: Zoniva Originals
        if ($channel->playback_mode === 'zoniva_original' || $channel->isVirtual()) {
            return [
                'allowed' => true,
                'mode' => 'zoniva_original',
                'stream' => $primaryStream,
                'destination_url' => null,
            ];
        }

        // Mode A: Direct HLS Playback inside Zoniva TV
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

        // Mode D: Zonita 24/7 Virtual Linear Playout Engine
        if ($channel->playback_mode === 'zoniva_original' || $channel->isVirtual()) {
            return [
                'allowed' => true,
                'mode' => 'zoniva_original',
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

    /**
     * Purges and de-lists content assets whose rights have expired.
     */
    public function purgeExpiredRights(bool $isDryRun = false): array
    {
        $expiredRights = ContentRight::with(['channel', 'video', 'movie'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('status', '!=', 'expired')
            ->get();

        $expiredCount = 0;
        $purgedSchedulesCount = 0;

        foreach ($expiredRights as $right) {
            $expiredCount++;

            if (! $isDryRun) {
                $right->update(['status' => 'expired']);

                if ($right->auto_delist_on_expiry) {
                    if ($right->channel) {
                        $right->channel->update(['is_published' => false]);
                    }

                    if ($right->video) {
                        $right->video->update([
                            'availability_status' => 'expired',
                            'is_embeddable' => false,
                        ]);

                        $cancelled = ScheduleEntry::where('video_id', $right->video->id)
                            ->where('start_time', '>=', now())
                            ->update(['status' => 'cancelled']);

                        $purgedSchedulesCount += $cancelled;
                    }

                    if ($right->movie) {
                        $right->movie->update([
                            'is_schedulable' => false,
                            'verification_status' => 'expired',
                        ]);
                    }
                }
            }
        }

        return [
            'expired_count' => $expiredCount,
            'purged_schedules_count' => $purgedSchedulesCount,
        ];
    }
}
