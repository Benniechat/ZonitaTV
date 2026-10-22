<?php

namespace App\Console\Commands;

use App\Services\Zonita\ChannelProgrammingEngine;
use Illuminate\Console\Command;

class DeployZonitaEngineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zonita:deploy-pilot-engine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes Zonita TV AI-Powered Multi-Channel Programming Engine and 7-day rolling schedules';

    /**
     * Execute the console command.
     */
    public function handle(ChannelProgrammingEngine $engine): int
    {
        $this->info('Starting Zonita TV AI-Powered Multi-Channel Programming Engine deployment...');

        $result = $engine->deployPilotNetwork();

        $this->info("Successfully deployed {$result['channels']} pilot channels.");
        $this->info("Verified and ingested {$result['videos_verified']} candidate programmes.");
        $this->info("Generated {$result['schedule_entries']} continuous rolling schedule entries across the network.");

        return Command::SUCCESS;
    }
}
