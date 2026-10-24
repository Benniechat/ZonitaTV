<?php

namespace App\Console\Commands;

use App\Services\Sports\FixtureDiscoveryService;
use Illuminate\Console\Command;

class SyncSportsFixturesCommand extends Command
{
    protected $signature = 'sports:sync-fixtures 
                            {--days=7 : Days ahead of fixtures to discover}';

    protected $description = 'Automatically discover and synchronize sports fixtures into Zonita TV';

    public function handle(FixtureDiscoveryService $discovery): int
    {
        $this->info('Starting automated sports fixture discovery...');

        $days = (int) $this->option('days');
        $stats = $discovery->syncFixtures(daysAhead: $days);

        $this->table(
            ['Metric', 'Count'],
            [
                ['New Fixtures Discovered', $stats['discovered']],
                ['Existing Fixtures Updated', $stats['updated']],
                ['Active Live Matches Synced', $stats['live_in_play']],
            ]
        );

        $this->info('Sports fixture synchronization completed successfully.');
        return 0;
    }
}
