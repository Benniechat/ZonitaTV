<?php

namespace App\Services\Sports\Adapters;

use App\Models\FixtureBroadcast;
use App\Models\SportsFixture;
use App\Services\Sports\Contracts\BroadcastBackendInterface;
use Illuminate\Support\Facades\Log;

class MockBroadcastAdapter implements BroadcastBackendInterface
{
    protected string $currentScene = 'FallbackScene';

    protected bool $isStreaming = false;

    protected ?string $preparedSourceUrl = null;

    protected array $activeGraphics = [];

    protected bool $simulateConnectionFailure = false;

    public function setSimulateConnectionFailure(bool $fail): self
    {
        $this->simulateConnectionFailure = $fail;

        return $this;
    }

    public function checkConnection(): bool
    {
        return ! $this->simulateConnectionFailure;
    }

    public function getPlayoutStatus(): array
    {
        return [
            'connected' => $this->checkConnection(),
            'current_scene' => $this->currentScene,
            'is_streaming' => $this->isStreaming,
            'prepared_source' => $this->preparedSourceUrl,
            'active_graphics' => $this->activeGraphics,
            'fps' => 60,
            'bitrate_kbps' => 4500,
            'backend_type' => 'mock',
        ];
    }

    public function prepareSource(SportsFixture $fixture, FixtureBroadcast $broadcast): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        $this->preparedSourceUrl = $broadcast->feed_endpoint ?? 'srt://stream.zonita.tv/live/'.$fixture->id;
        Log::info("MockBroadcastAdapter: Prepared source {$this->preparedSourceUrl} for fixture #{$fixture->id}");

        return true;
    }

    public function switchScene(string $sceneName): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        $this->currentScene = $sceneName;
        Log::info("MockBroadcastAdapter: Switched scene to '{$sceneName}'");

        return true;
    }

    public function injectGraphics(string $overlayType, array $data): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        $this->activeGraphics[$overlayType] = $data;
        Log::info("MockBroadcastAdapter: Injected '{$overlayType}' overlay graphics", $data);

        return true;
    }

    public function startPlayout(SportsFixture $fixture, FixtureBroadcast $broadcast): bool
    {
        if (! $this->checkConnection()) {
            return false;
        }

        $this->prepareSource($fixture, $broadcast);
        $this->isStreaming = true;
        $this->switchScene(config('sports.scenes.live_match', 'LiveMatchScene'));

        Log::info("MockBroadcastAdapter: Live playout started for fixture #{$fixture->id}");

        return true;
    }

    public function stopPlayout(): bool
    {
        $this->isStreaming = false;
        $this->switchScene(config('sports.scenes.fallback', 'FallbackScene'));
        Log::info('MockBroadcastAdapter: Live playout stopped, returned to FallbackScene');

        return true;
    }

    public function switchToFallback(string $reason = 'Emergency fallback triggered'): bool
    {
        $this->switchScene(config('sports.scenes.fallback', 'FallbackScene'));
        Log::warning("MockBroadcastAdapter: Switched to fallback scene. Reason: {$reason}");

        return true;
    }
}
