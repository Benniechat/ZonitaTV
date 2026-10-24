<?php

namespace App\Services\Sports;

use App\Models\SportsFixture;
use App\Services\CloudinaryService;

class MatchThumbnailService
{
    public function __construct(
        protected CloudinaryService $cloudinary
    ) {}

    /**
     * Generate or resolve an optimized match card artwork URL.
     */
    public function resolveMatchThumbnail(SportsFixture $fixture): string
    {
        if ($fixture->thumbnail_card_url) {
            return $fixture->thumbnail_card_url;
        }

        // Check if home or away team has Cloudinary or valid web crest
        $homeLogo = $fixture->homeTeam?->logo;
        $awayLogo = $fixture->awayTeam?->logo;

        // If Cloudinary is configured and home crest is external, use Cloudinary dynamic CDN
        if ($homeLogo && (str_starts_with($homeLogo, 'http://') || str_starts_with($homeLogo, 'https://'))) {
            if ($this->cloudinary->isConfigured()) {
                return $this->cloudinary->fetchUrl($homeLogo, [
                    'width' => 640,
                    'height' => 360,
                    'crop' => 'fill',
                    'gravity' => 'center',
                ]);
            }
        }

        return $fixture->competition?->emblem_url ?: asset('images/logo/zonitatvlogo2.png');
    }
}
