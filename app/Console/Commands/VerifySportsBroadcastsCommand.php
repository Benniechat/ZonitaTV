<?php

namespace App\Console\Commands;

use App\Services\Sports\BroadcastVerificationService;
use Illuminate\Console\Command;

class VerifySportsBroadcastsCommand extends Command
{
    protected $signature = 'sports:verify-broadcasts';

    protected $description = 'Verify SportyTV broadcast destinations and statutory rights for discovered fixtures';

    public function handle(BroadcastVerificationService $verifier): int
    {
        $this->info('Starting SportyTV broadcast verification...');

        $stats = $verifier->verifyPendingFixtures();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Fixtures Checked', $stats['checked']],
                ['SportyTV Broadcasts Confirmed', $stats['confirmed']],
                ['Unconfirmed / No Coverage', $stats['unconfirmed']],
            ]
        );

        $this->info('Broadcast verification completed.');

        return 0;
    }
}
