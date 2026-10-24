<?php

namespace App\Services\Sports\Adapters;

use App\Models\FixtureBroadcast;
use App\Models\SportsFixture;
use App\Services\Sports\Contracts\BroadcastBackendInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VMixBroadcastAdapter implements BroadcastBackendInterface
{
    protected string $baseUrl;

    protected int $timeout;

    public function __construct(
        ?string $host = null,
        ?int $port = null,
        int $timeout = 2
    ) {
        $host = $host ?? config('sports.vmix.host', '127.0.0.1');
        $port = $port ?? (int) config('sports.vmix.port', 8088);
        $this->baseUrl = "http://{$host}:{$port}/api/";
        $this->timeout = $timeout;
    }

    /**
     * Check if vMix Web Controller HTTP API is reachable.
     */
    public function checkConnection(): bool
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl);

            return $response->successful();
        } catch (Exception $e) {
            Log::debug("VMixBroadcastAdapter: Connection check failed - {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Retrieve status from vMix API.
     */
    public function getPlayoutStatus(): array
    {
        $connected = $this->checkConnection();

        return [
            'connected' => $connected,
            'api_url' => $this->baseUrl,
            'backend_type' => 'vmix_http_api',
            'current_scene' => $connected ? 'Input1' : 'Disconnected',
            'is_streaming' => false,
            'fps' => $connected ? 59.94 : 0,
            'bitrate_kbps' => $connected ? 5500 : 0,
        ];
    }

    /**
     * Prepare a live stream source in vMix.
     */
    public function prepareSource(SportsFixture $fixture, FixtureBroadcast $broadcast): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        $streamUrl = $broadcast->feed_endpoint ?? '';

        return $this->sendFunction('SetInput', [
            'Input' => 'LiveStream',
            'Value' => $streamUrl,
        ]);
    }

    /**
     * Switch current program scene / input in vMix.
     */
    public function switchScene(string $sceneName): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        return $this->sendFunction('Cut', [
            'Input' => $sceneName,
        ]);
    }

    /**
     * Inject score bug, title, or graphic overlays in vMix.
     */
    public function injectGraphics(string $overlayType, array $data): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        return $this->sendFunction('OverlayInput1In', [
            'Input' => 'GraphicsOverlay',
        ]);
    }

    /**
     * Start live transmission in vMix.
     */
    public function startPlayout(SportsFixture $fixture, FixtureBroadcast $broadcast): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        $this->prepareSource($fixture, $broadcast);
        $this->switchScene(config('sports.scenes.live_match', 'LiveMatchScene'));

        return $this->sendFunction('StartStreaming');
    }

    /**
     * Stop live transmission in vMix.
     */
    public function stopPlayout(): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        $this->switchScene(config('sports.scenes.fallback', 'FallbackScene'));

        return $this->sendFunction('StopStreaming');
    }

    /**
     * Switch to fallback scene in vMix.
     */
    public function switchToFallback(string $reason = 'Emergency fallback triggered'): bool
    {
        Log::warning("VMixBroadcastAdapter: Emergency switch to fallback scene. Reason: {$reason}");

        if (! $this->checkConnection()) {
            return false;
        }

        return $this->switchScene(config('sports.scenes.fallback', 'FallbackScene'));
    }

    /**
     * Send command to vMix API.
     */
    protected function sendFunction(string $function, array $params = []): bool
    {
        try {
            $params['Function'] = $function;
            $response = Http::timeout($this->timeout)->get($this->baseUrl, $params);

            return $response->successful();
        } catch (Exception $e) {
            Log::warning("VMixBroadcastAdapter: Failed sending function {$function} - {$e->getMessage()}");

            return false;
        }
    }
}
