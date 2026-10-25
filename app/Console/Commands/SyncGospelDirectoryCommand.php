<?php

namespace App\Console\Commands;

use App\Services\Zonita\GospelFilmDirectoryService;
use Illuminate\Console\Command;

class SyncGospelDirectoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gospel:sync-directory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize and audit the 60 Gospel Movie & Drama production ministries across Nigeria and Africa for Zonita TV 24/7 linear broadcasting';

    /**
     * Execute the console command.
     */
    public function handle(GospelFilmDirectoryService $service): int
    {
        $this->info('Starting Zonita TV Gospel Movie & Drama Directory Synchronization...');
        $this->comment('Cataloguing 60 film productions from Nigeria, Ghana, and Africa with rights tracking.');

        $result = $service->syncDirectory();

        $this->newLine();
        $this->table(
            ['Metric', 'Count / State'],
            [
                ['Gospel Ministries & Film Channels Synced', $result['ministries_synced']],
                ['Priority 1 Full-Length Core Producers', $result['top_priority_count']],
                ['Linear 24/7 Playout Channel', 'African Gospel Film Network (african-gospel-cinema-tv)'],
                ['7-Day Rolling Playout Schedule Entries', $result['schedule_entries']],
                ['Broadcast Playout State', '24/7 Real-Time Linear (No Scrubbing, Seeking Locked)'],
            ]
        );

        $this->info('Gospel Film Directory and 24/7 Playout Network synchronized successfully!');

        return Command::SUCCESS;
    }
}
