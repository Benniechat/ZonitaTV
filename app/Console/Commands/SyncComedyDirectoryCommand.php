<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Zonita\ComedyDirectoryService;
use Illuminate\Console\Command;

class SyncComedyDirectoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comedy:sync-directory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize 70 Nigerian comedy channels, creators, and deploy the 10 virtual 24/7 comedy broadcast channels';

    /**
     * Execute the console command.
     */
    public function handle(ComedyDirectoryService $service): int
    {
        $this->info('Starting Zonita TV Comedy Entertainment Directory Synchronization & Broadcast Deployment...');
        $this->line('Cataloguing 70 Nigerian & African comedy creators across Stand-Up, Skits, Sitcoms, and Animation.');

        $result = $service->syncDirectory();

        $this->newLine();
        $this->table(
            ['Metric', 'Count / State'],
            [
                ['Comedy Creators & Studios Catalogued', $result['creators_synced']],
                ['Dedicated Virtual 24/7 Comedy Channels Deployed', $result['virtual_channels_deployed']],
                ['7-Day Rolling Playout Schedule Entries', $result['schedule_entries_created']],
                ['Broadcast Playout State', '24/7 Real-Time Linear (No Scrubbing, Zero Seeking)'],
            ]
        );

        $this->info('Zonita TV Comedy Directory & 24/7 Virtual Playout Network synchronized successfully!');

        return Command::SUCCESS;
    }
}
