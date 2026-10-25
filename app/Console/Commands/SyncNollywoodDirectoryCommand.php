<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Zonita\NollywoodDirectoryService;
use Illuminate\Console\Command;

class SyncNollywoodDirectoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nollywood:sync-directory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize and audit the 60 Nollywood Movie & Drama production brands across English, Yoruba, and African cinema for Zonita TV 24/7 linear broadcasting';

    /**
     * Execute the console command.
     */
    public function handle(NollywoodDirectoryService $service): int
    {
        $this->info('Starting Zonita TV Nollywood Movie & Production Directory Synchronization...');
        $this->comment('Cataloguing 60 Nollywood channels across English-language, Yoruba cinema, and movie aggregators.');

        $result = $service->syncDirectory();

        $this->newLine();
        $this->table(
            ['Metric', 'Count / State'],
            [
                ['Nollywood Channels & Producers Synced', $result['channels_synced']],
                ['Priority 1 Filmmaker-Owned Channels', $result['top_priority_count']],
                ['Linear 24/7 Playout Channels Deployed', $result['channels_deployed']],
                ['7-Day Rolling Playout Schedule Entries', $result['schedule_entries']],
                ['Broadcast Playout State', '24/7 Real-Time Linear (No Scrubbing, Zero Seeking)'],
            ]
        );

        $this->info('Nollywood Movie Directory and 24/7 Playout Network synchronized successfully!');

        return Command::SUCCESS;
    }
}
