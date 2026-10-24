<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelStream;
use App\Models\StreamCheck;
use Illuminate\Support\Facades\Http;
use Throwable;

class StreamVerificationService
{
    /**
     * Diagnostic verification of a stream endpoint.
     * Note: A 200 HTTP check does not substitute for distribution rights verification.
     */
    public function verifyStream(ChannelStream $stream): StreamCheck
    {
        $channel = $stream->channel;
        $url = $stream->stream_url;

        if (empty($url)) {
            return StreamCheck::create([
                'channel_id' => $channel->id,
                'channel_stream_id' => $stream->id,
                'is_reachable' => false,
                'error_message' => 'Stream URL is empty.',
                'checked_at' => now(),
            ]);
        }

        $startTime = microtime(true);

        try {
            // Send lightweight HEAD / GET range request with 5s timeout
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'ZonitaTV-StreamDiagnosticBot/1.0 (+https://zonitatv.com)',
                ])
                ->head($url);

            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->status();
            $isReachable = $response->successful() || $statusCode === 302 || $statusCode === 301;

            $check = StreamCheck::create([
                'channel_id' => $channel->id,
                'channel_stream_id' => $stream->id,
                'http_status_code' => $statusCode,
                'response_time_ms' => $responseTimeMs,
                'is_reachable' => $isReachable,
                'error_message' => $isReachable ? null : 'Received non-2xx status code: '.$statusCode,
                'checked_at' => now(),
            ]);

            // Update channel health cache
            $channel->update([
                'is_technically_healthy' => $isReachable,
                'last_verified_at' => now(),
            ]);

            return $check;
        } catch (Throwable $e) {
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            $check = StreamCheck::create([
                'channel_id' => $channel->id,
                'channel_stream_id' => $stream->id,
                'http_status_code' => 0,
                'response_time_ms' => $responseTimeMs,
                'is_reachable' => false,
                'error_message' => $e->getMessage(),
                'checked_at' => now(),
            ]);

            $channel->update([
                'is_technically_healthy' => false,
                'last_verified_at' => now(),
            ]);

            return $check;
        }
    }
}
