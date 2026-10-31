<?php

namespace App\Console\Commands;

use App\Models\ContentRight;
use App\Services\RightsEligibilityService;
use Illuminate\Console\Command;

class MonitorRightsExpirationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rights:monitor-expirations {--dry-run : Check and report without modifying records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect expired and expiring content rights, automatically de-listing expired assets from schedules';

    /**
     * Execute the console command.
     */
    public function handle(RightsEligibilityService $service): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Scanning Content Rights Ledger for license expirations...');

        $result = $service->purgeExpiredRights($isDryRun);

        // Upcoming expirations (within next 7 days)
        $expiringSoon = ContentRight::with(['channel', 'video', 'movie'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays(7))
            ->whereNotIn('status', ['expired', 'rejected'])
            ->get();

        $this->info("\n--- Expiring Within 7 Days ---");
        foreach ($expiringSoon as $right) {
            $daysLeft = now()->diffInDays($right->expires_at);
            $targetDesc = $right->channel?->name ?? $right->video?->title ?? $right->movie?->title ?? "Rights #{$right->id}";
            $this->comment("EXPIRING SOON: [ID #{$right->id}] '{$targetDesc}' expires in {$daysLeft} days ({$right->expires_at->toDateString()})");
        }

        $this->newLine();
        $this->info("Scan Complete. Expired: {$result['expired_count']} | Purged Schedule Blocks: {$result['purged_schedules_count']} | Expiring Soon: {$expiringSoon->count()}");

        return self::SUCCESS;
    }
}
