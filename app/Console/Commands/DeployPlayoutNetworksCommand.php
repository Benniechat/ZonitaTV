<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\Zonita\ChannelProgrammingEngine;
use Illuminate\Console\Command;

class DeployPlayoutNetworksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:deploy-networks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy and schedule 24/7 realtime linear playout networks including Gospel Drama (Damilola Mike-Bamiloye, Prem TV) and Kids (CoComelon, Folktales, Bible Cartoons)';

    /**
     * Execute the console command.
     */
    public function handle(ChannelProgrammingEngine $engine): int
    {
        $this->info('Starting 24/7 Linear Playout Network Deployment...');

        // Ensure Kids category exists
        Category::firstOrCreate(
            ['slug' => 'kids'],
            [
                'name' => 'Kids & Family',
                'icon_name' => 'face-smile',
                'meta_title' => 'Kids Cartoons, Animated Bible Stories & African Folktales',
                'description' => 'Safe, educational, and inspiring cartoons, CoComelon songs, African folktales, and Superbook Bible animations.',
                'sort_order' => 6,
            ]
        );

        $result = $engine->deployPilotNetwork();

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Channels Created / Updated', $result['channels']],
                ['Videos Ingested / Verified', $result['videos_verified']],
                ['Rolling Schedule Entries Created', $result['schedule_entries']],
                ['Playout State', 'Active 24/7 Realtime (Zero Seeking, Auto-Advancing)'],
            ]
        );

        $this->info('All 24/7 linear playout networks deployed and scheduled successfully!');

        return Command::SUCCESS;
    }
}
