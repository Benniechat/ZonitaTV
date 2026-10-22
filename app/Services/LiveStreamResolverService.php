<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LiveStreamResolverService
{
    /**
     * Resolves an active direct stream or resilient playlist for a given channel URL.
     *
     * 1. Attempts to extract direct active HLS manifest (manifest.googlevideo.com .m3u8).
     * 2. If no direct manifest is active, falls back to the continuous channel playlist series (UU...).
     */
    public function resolveChannelStream(string $youtubeChannelUrlOrId): array
    {
        try {
            $url = str_starts_with($youtubeChannelUrlOrId, 'http')
                ? $youtubeChannelUrlOrId
                : "https://www.youtube.com/channel/{$youtubeChannelUrlOrId}/live";

            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get($url);

            if (! $response->successful()) {
                return $this->fallbackPlaylist($youtubeChannelUrlOrId);
            }

            $html = $response->body();

            // Check if active direct HLS manifest exists
            if (preg_match('/"hlsManifestUrl":"([^"]+)"/', $html, $matches)) {
                $rawManifest = stripslashes($matches[1]);

                return [
                    'success' => true,
                    'type' => 'hls_direct',
                    'stream_url' => $rawManifest,
                    'is_live' => true,
                ];
            }

            // Check if active live video ID is present in page
            if (preg_match('/"liveStreamability".*?"videoId":"([a-zA-Z0-9_\-]{11})"/', $html, $matches)) {
                return [
                    'success' => true,
                    'type' => 'embed',
                    'stream_url' => "https://www.youtube-nocookie.com/embed/{$matches[1]}",
                    'is_live' => true,
                ];
            }

            return $this->fallbackPlaylist($youtubeChannelUrlOrId);
        } catch (Throwable $e) {
            Log::warning('LiveStreamResolverService failed: '.$e->getMessage());

            return $this->fallbackPlaylist($youtubeChannelUrlOrId);
        }
    }

    private function fallbackPlaylist(string $identifier): array
    {
        // Extract UC channel ID if present and transform to UU playlist
        if (preg_match('/(UC[a-zA-Z0-9_\-]{22})/', $identifier, $matches)) {
            $playlistId = 'UU'.substr($matches[1], 2);

            return [
                'success' => true,
                'type' => 'embed',
                'stream_url' => "https://www.youtube-nocookie.com/embed/videoseries?list={$playlistId}",
                'is_live' => false,
            ];
        }

        return [
            'success' => false,
            'type' => 'none',
            'stream_url' => null,
            'is_live' => false,
        ];
    }
}
