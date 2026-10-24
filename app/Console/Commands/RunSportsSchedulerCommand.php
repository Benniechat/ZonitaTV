<?php

namespace App\Console\Commands;

use App\Services\Sports\BroadcastRecoveryService;
use App\Services\Sports\BroadcastVerificationService;
use App\Services\Sports\FixtureDiscoveryService;
use App\Services\Sports\MatchSchedulingService;
use App\Services\Sports\PlaybackHealthMonitor;
use Illuminate\Console\Command;

class RunSportsSchedulerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sports:run-scheduler {--once : Run a single synchronization cycle instead of daemon loop}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Autonomous Zonita Sports Live engine: Discovers fixtures, verifies SportyTV broadcasts, and updates linear playout';

    /**
     * Execute the console command.
     */
    public function handle(
        FixtureDiscoveryService $discovery,
        BroadcastVerificationService $verifier,
        MatchSchedulingService $scheduler,
        PlaybackHealthMonitor $healthMonitor,
        BroadcastRecoveryService $recovery
    ): int {
        $this->info('Starting Zonita Sports Autonomous MCR Scheduler...');

        $runCycle = function () use ($discovery, $verifier, $scheduler, $recovery) {
            $this->line('['.now()->format('Y-m-d H:i:s').'] Executing Sports Engine sync cycle...');

            // 1. Discover Fixtures
            $discoveryResult = $discovery->syncAllCompetitions();
            $this->info(" -> Discovered: {$discoveryResult['new_fixtures']} new, {$discoveryResult['updated_fixtures']} updated.");

            // 2. Verify SportyTV Broadcasts
            $verifyResult = $verifier->verifyUpcomingFixtures();
            $this->info(" -> Verified: {$verifyResult['confirmed']} SportyTV confirmed, {$verifyResult['mode_c_permitted']} Mode C rebroadcasts.");

            // 3. Schedule Linear EPG Timeline
            $scheduleResult = $scheduler->syncFixtureSchedule();
            $this->info(" -> Linear Channel: {$scheduleResult['scheduled']} fixtures scheduled, {$scheduleResult['updated']} updated.");

            // 4. Health Probe & Recovery
            $recoveryResult = $recovery->evaluateAndRecover();
            if ($recoveryResult['fallback_triggered'] > 0 || $recoveryResult['remediated'] > 0) {
                $this->warn(" -> Telemetry Recovery: {$recoveryResult['fallback_triggered']} fallbacks engaged, {$recoveryResult['remediated']} sessions restored.");
            } else {
                $this->line(' -> Telemetry Health: All active transmission sessions nominal.');
            }
        };

        if ($this->option('once')) {
            $runCycle();

            return self::SUCCESS;
        }

        // Single cycle by default if run via console schedule
        $runCycle();

        return self::SUCCESS;
    }
}
