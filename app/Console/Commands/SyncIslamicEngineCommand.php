<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Zonita\IslamicContentDiscoveryService;
use App\Services\Zonita\IslamicPlayoutSchedulingService;
use Illuminate\Console\Command;

class SyncIslamicEngineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'islamic:sync-engine {--days=7 : Number of days of schedules to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync verified Islamic content providers directory and deploy 24/7 virtual channels with rolling schedules';

    /**
     * Execute the console command.
     */
    public function handle(
        IslamicContentDiscoveryService $discoveryService,
        IslamicPlayoutSchedulingService $schedulingService
    ): int {
        $this->info('Starting Zonita TV Islamic Broadcasting & Discovery Engine synchronization...');

        $this->comment('Step 1: Synchronizing Islamic Content Providers Directory & Rights Database...');
        $stats = $discoveryService->syncProviderDirectory();
        $this->info("✓ Synchronized {$stats['providers_synced']} verified Islamic content providers & media production houses ({$stats['verified_owners']} verified owners, {$stats['active_broadcasting']} active broadcast partners).");

        $days = (int) $this->option('days');
        if ($days < 1) {
            $days = 7;
        }

        $this->comment("Step 2: Deploying 8 Islamic Virtual Channels and generating {$days}-day rolling schedules...");
        $results = $schedulingService->deployIslamicVirtualNetwork($days);

        $this->table(
            ['Slug', 'Channel Name', 'Language', 'Status', 'Active Upcoming Schedules'],
            collect($results['channels'])->map(function (array $item) {
                return [
                    $item['slug'],
                    $item['name'],
                    $item['language'],
                    $item['status'],
                    $item['active_schedules_count'].' programmes',
                ];
            })->all()
        );

        $totalActive = collect($results['channels'])->sum('active_schedules_count');
        $this->info("✓ Deployed {$results['channels_deployed']} channels with {$totalActive} active upcoming scheduled broadcast events.");
        $this->info('Islamic Broadcasting Engine synchronized successfully.');

        return self::SUCCESS;
    }
}
