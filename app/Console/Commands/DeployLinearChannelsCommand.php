<?php

namespace App\Console\Commands;

use App\Services\LinearChannelEngineService;
use Illuminate\Console\Command;

class DeployLinearChannelsCommand extends Command
{
    protected $signature = 'zonita:deploy-linear-channels';

    protected $description = 'Deploys 24/7 automated linear channels with seamless YouTube programme playout';

    public function handle(LinearChannelEngineService $engine): int
    {
        $this->info('Starting Zonita 24/7 Linear Playout Engine deployment...');

        $report = $engine->deployLinearChannels();

        $this->info("Successfully deployed {$report['channels']} linear channels.");
        $this->info("Generated {$report['programmes']} 24/7 scheduled programme slots across the grid.");

        return Command::SUCCESS;
    }
}
