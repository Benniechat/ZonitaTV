<?php

namespace App\Services\Sports\Contracts;

use App\Models\FixtureBroadcast;
use App\Models\SportsFixture;

interface BroadcastBackendInterface
{
    /**
     * Check if the broadcast backend (OBS / vMix) is reachable and responsive.
     */
    public function checkConnection(): bool;

    /**
     * Retrieve status details from the broadcast engine (e.g., current scene, streaming state, FPS, bitrate).
     *
     * @return array<string, mixed>
     */
    public function getPlayoutStatus(): array;

    /**
     * Prepare a live source (e.g. inject SRT/RTMP/HLS URL into an input source).
     */
    public function prepareSource(SportsFixture $fixture, FixtureBroadcast $broadcast): bool;

    /**
     * Switch current program scene in the broadcast engine.
     */
    public function switchScene(string $sceneName): bool;

    /**
     * Inject score bug, title, or holding graphic overlays.
     *
     * @param  array<string, mixed>  $data
     */
    public function injectGraphics(string $overlayType, array $data): bool;

    /**
     * Start live transmission / streaming output.
     */
    public function startPlayout(SportsFixture $fixture, FixtureBroadcast $broadcast): bool;

    /**
     * Stop live transmission / streaming output.
     */
    public function stopPlayout(): bool;

    /**
     * Immediately switch to fallback scene (holding slide / evergreen loop).
     */
    public function switchToFallback(string $reason = 'Emergency fallback triggered'): bool;
}
