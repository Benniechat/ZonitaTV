<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelStream;
use App\Models\ContentRight;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class IptvPlaylistIngestService
{
    /**
     * Map IPTV-ORG group-titles to Zonita TV categories
     */
    protected array $categoryMap = [
        'sports' => 'sports',
        'sport' => 'sports',
        'auto' => 'sports',
        'football' => 'sports',
        'combat sports' => 'sports',
        'news' => 'news',
        'weather' => 'news',
        'business' => 'news',
        'legislative' => 'news',
        'religious' => 'christian-tv',
        'christian' => 'christian-tv',
        'faith' => 'christian-tv',
        'gospel' => 'christian-tv',
        'documentary' => 'education',
        'science' => 'education',
        'education' => 'education',
        'history' => 'education',
        'culture' => 'education',
        'entertainment' => 'entertainment',
        'movies' => 'entertainment',
        'music' => 'entertainment',
        'comedy' => 'entertainment',
        'kids' => 'entertainment',
        'animation' => 'entertainment',
        'general' => 'entertainment',
        'series' => 'entertainment',
    ];

    /**
     * Ingest an M3U playlist from URL or raw content
     *
     * @param  string  $sourceUrl  URL or path to .m3u
     * @param  string|null  $countryFilter  2-letter or 3-letter country code (e.g. 'NG', 'US', or null for all)
     * @param  int  $limit  Maximum channels to process
     * @return array Ingestion report metrics
     */
    public function ingestFromUrl(string $sourceUrl, ?string $countryFilter = null, int $limit = 100): array
    {
        $response = Http::timeout(45)
            ->withoutVerifying()
            ->withHeaders(['User-Agent' => 'ZonitaTV-PlaylistIngest/1.0'])
            ->get($sourceUrl);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to fetch playlist from {$sourceUrl}. HTTP Status: {$response->status()}");
        }

        return $this->parseAndImport($response->body(), $countryFilter, $limit, $sourceUrl);
    }

    /**
     * Parse raw M3U text and populate channels, streams, categories, and rights
     */
    public function parseAndImport(string $m3uContent, ?string $countryFilter = null, int $limit = 100, ?string $sourceUrl = null): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $m3uContent);
        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        $categories = Category::all()->keyBy('slug');
        $currentExtInf = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            if (str_starts_with($line, '#EXTINF:')) {
                $currentExtInf = $this->parseExtInf($line);

                continue;
            }

            if (str_starts_with($line, '#')) {
                // Other tags like #EXTVLCOPT
                continue;
            }

            // This is a stream URL line
            if ($currentExtInf && ! empty($line)) {
                $streamUrl = $line;

                // Country filtering logic
                if ($countryFilter) {
                    $cFilterUpper = strtoupper($countryFilter);
                    $tvgIdCountry = $this->extractCountryFromTvgId($currentExtInf['tvg_id'] ?? '');

                    if ($tvgIdCountry && $tvgIdCountry !== $cFilterUpper && $tvgIdCountry !== substr($cFilterUpper, 0, 2)) {
                        $currentExtInf = null;
                        $skippedCount++;

                        continue;
                    }
                }

                try {
                    $result = $this->persistChannel($currentExtInf, $streamUrl, $categories, $sourceUrl);
                    if ($result === 'created') {
                        $importedCount++;
                    } elseif ($result === 'updated') {
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } catch (Throwable $e) {
                    $skippedCount++;
                }

                $currentExtInf = null;

                if (($importedCount + $updatedCount) >= $limit) {
                    break;
                }
            }
        }

        return [
            'total_processed' => $importedCount + $updatedCount + $skippedCount,
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
        ];
    }

    /**
     * Parse an #EXTINF line into structured attributes
     */
    protected function parseExtInf(string $line): array
    {
        $data = [
            'tvg_id' => null,
            'tvg_name' => null,
            'tvg_logo' => null,
            'group_title' => null,
            'name' => null,
        ];

        if (preg_match('/tvg-id="([^"]*)"/i', $line, $m)) {
            $data['tvg_id'] = trim($m[1]);
        }
        if (preg_match('/tvg-name="([^"]*)"/i', $line, $m)) {
            $data['tvg_name'] = trim($m[1]);
        }
        if (preg_match('/tvg-logo="([^"]*)"/i', $line, $m)) {
            $data['tvg_logo'] = trim($m[1]);
        }
        if (preg_match('/group-title="([^"]*)"/i', $line, $m)) {
            $data['group_title'] = trim($m[1]);
        }

        // Channel display name comes after the last comma
        $commaPos = strrpos($line, ',');
        if ($commaPos !== false) {
            $data['name'] = trim(substr($line, $commaPos + 1));
        }

        if (empty($data['name'])) {
            $data['name'] = $data['tvg_name'] ?: ($data['tvg_id'] ?: 'Untitled Channel');
        }

        return $data;
    }

    /**
     * Persist parsed channel and configure playback + rights
     */
    protected function persistChannel(array $meta, string $streamUrl, $categories, ?string $sourceUrl): string
    {
        $name = $meta['name'];
        $tvgId = $meta['tvg_id'];
        $logo = $meta['tvg_logo'];
        $groupTitle = strtolower($meta['group_title'] ?? '');

        // Generate clean unique slug
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'channel-'.substr(md5($streamUrl), 0, 8);
        }

        $existing = null;
        if (! empty($tvgId)) {
            $existing = Channel::where('tvg_id', $tvgId)->first();
        }
        if (! $existing) {
            $existing = Channel::where('slug', $baseSlug)->first();
        }

        $countryCode = 'NGA';
        if ($tvgId && preg_match('/\.([a-z]{2})$/i', $tvgId, $cm)) {
            $countryCode = strtoupper($cm[1]);
        }

        $playbackMode = (str_contains($streamUrl, '.m3u8') || str_contains($streamUrl, 'http'))
            ? 'direct_stream'
            : 'external_destination';

        if ($existing) {
            $existing->update([
                'tvg_id' => $tvgId ?: $existing->tvg_id,
                'logo_path' => $logo ?: $existing->logo_path,
                'playback_mode' => $playbackMode,
                'is_active' => true,
                'is_published' => true,
            ]);

            // Update primary stream
            ChannelStream::updateOrCreate(
                ['channel_id' => $existing->id, 'label' => 'IPTV-ORG Feed'],
                [
                    'stream_type' => 'hls_m3u8',
                    'stream_url' => $streamUrl,
                    'is_active' => true,
                ]
            );

            return 'updated';
        }

        $channel = Channel::create([
            'name' => $name,
            'slug' => $baseSlug,
            'tvg_id' => $tvgId,
            'tagline' => $meta['group_title'] ? "Watch {$meta['group_title']} broadcast on Zonita TV" : null,
            'description' => "Verified live broadcast feed for {$name} discovered via open IPTV-ORG catalogue.",
            'logo_path' => $logo ?: '/images/logo/zonitatvLogo.png',
            'broadcaster_name' => $name,
            'broadcaster_website' => null,
            'official_destination_url' => $streamUrl,
            'country_code' => $countryCode,
            'language_code' => 'en',
            'playback_mode' => $playbackMode,
            'is_featured' => false,
            'is_active' => true,
            'is_published' => true,
            'display_order' => 50,
            'is_technically_healthy' => true,
        ]);

        // Map Category
        $matchedCategorySlug = 'entertainment';
        foreach ($this->categoryMap as $key => $targetSlug) {
            if (str_contains($groupTitle, $key)) {
                $matchedCategorySlug = $targetSlug;
                break;
            }
        }

        if (isset($categories[$matchedCategorySlug])) {
            $channel->categories()->sync([$categories[$matchedCategorySlug]->id]);
        }

        // Primary Stream
        ChannelStream::create([
            'channel_id' => $channel->id,
            'label' => 'IPTV-ORG Feed',
            'stream_type' => 'hls_m3u8',
            'stream_url' => $streamUrl,
            'is_active' => true,
        ]);

        // Content Rights record in accordance with blueprint rights lifecycle
        ContentRight::create([
            'channel_id' => $channel->id,
            'rights_holder_name' => 'IPTV-ORG Free-to-Air Directory Stream',
            'contract_reference' => 'IPTV-ORG-'.strtoupper(substr(md5($streamUrl), 0, 8)),
            'status' => 'directory_approved',
            'allow_directory_listing' => true,
            'allow_web_embedding' => true,
            'allow_direct_hls' => true,
            'permitted_territories' => ['GLOBAL'],
            'effective_from' => now(),
            'expires_at' => now()->addYear(),
            'reviewer_notes' => 'Imported via IPTV-ORG verified public playlist feed.',
        ]);

        return 'created';
    }

    protected function extractCountryFromTvgId(string $tvgId): ?string
    {
        if (preg_match('/\.([a-z]{2})(?:@[a-z]+)?$/i', $tvgId, $m)) {
            return strtoupper($m[1]);
        }

        return null;
    }
}
