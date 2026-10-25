<?php

namespace App\Console\Commands;

use App\Services\Zonita\MovieDiscoveryService;
use Illuminate\Console\Command;

class DiscoverAndVerifyMoviesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:discover-and-verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover films from Internet Archive, Library of Congress, Wikimedia Commons, and African Cinema distributors with full legal rights verification and 24/7 continuous playout scheduling';

    /**
     * Execute the console command.
     */
    public function handle(MovieDiscoveryService $service): int
    {
        $this->info('Starting Zonita TV Movie Discovery and Rights Verification Engine...');
        $this->comment('Scanning approved repositories: Internet Archive, Library of Congress, Wikimedia Commons, and African Cinema Syndicates.');

        $result = $service->discoverAndDeployMovieCatalogue();

        $this->newLine();
        $this->table(
            ['Metric', 'Status / Count'],
            [
                ['Channels Initialized / Synchronized', $result['channels'].' channels (Classics, African Cinema, Documentaries)'],
                ['Movie Titles Ingested from Repositories', $result['movies_ingested']],
                ['Movies Fully Rights-Verified & Cleared', $result['movies_verified']],
                ['7-Day Continuous Rolling Schedule Entries', $result['schedule_entries']],
                ['Broadcast Playout State', '24/7 Real-Time Linear (No Scrubbing, Wall-Clock Sync)'],
            ]
        );

        $this->info('Zonita TV Movie Directory and Rights Engine completed successfully!');

        return Command::SUCCESS;
    }
}
