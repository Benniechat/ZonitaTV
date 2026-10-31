<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Services\Zonita\ProgrammeScheduleService;
use App\Services\Zonita\XmltvExportService;
use Illuminate\Console\Command;

class GenerateRollingScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'epg:generate-rolling 
                            {--channel= : Specific channel slug or all} 
                            {--days=7 : Days ahead to generate (1 to 30)} 
                            {--resolve-conflicts : Auto-resolve overlaps and gaps} 
                            {--min-hours=48 : Minimum hours ahead threshold for auto-replenish}';

    /**
     * The console command description.
     */
    protected $description = 'Generate continuous rolling EPG schedules (7-30 days), resolve playout conflicts, and synchronize programmes.';

    /**
     * Execute the console command.
     */
    public function handle(ProgrammeScheduleService $scheduleService, XmltvExportService $xmltvService): int
    {
        $daysAhead = (int) $this->option('days') ?: 7;
        $daysAhead = max(1, min(30, $daysAhead));
        $channelSlug = $this->option('channel');
        $resolveConflicts = (bool) $this->option('resolve-conflicts');
        $minHours = (int) $this->option('min-hours') ?: 48;

        $this->info("Zonita TV EPG Rolling Schedule Generator (Horizon: {$daysAhead} Days)");

        if ($channelSlug && $channelSlug !== 'all') {
            $channel = Channel::where('slug', $channelSlug)->first();
            if (! $channel) {
                $this->error("Channel with slug [{$channelSlug}] not found.");

                return self::FAILURE;
            }

            $this->info("Generating {$daysAhead}-day rolling schedule for [{$channel->name}]...");
            $res = $scheduleService->generateRollingSchedule($channel, $daysAhead);
            $this->info("Entries created: {$res['entries_created']}. Status: {$res['status']}");

            if ($resolveConflicts) {
                $conflictRes = $scheduleService->resolveScheduleConflicts($channel);
                $this->info("Conflicts resolved: Overlaps: {$conflictRes['overlaps_resolved']}, Gaps filled: {$conflictRes['gaps_filled']}, Realigned: {$conflictRes['entries_realigned']}");
            }
        } else {
            $this->info("Evaluating all active channels for rolling schedule replenishment (Threshold: <{$minHours}h)...");
            $results = $scheduleService->replenishAllChannels($minHours, $daysAhead);

            if (empty($results)) {
                $this->info('All active channels currently have sufficient rolling schedule coverage (>48h).');
            } else {
                foreach ($results as $item) {
                    $this->line(" - Channel [{$item['channel']}]: Created {$item['entries_created']} entries (was {$item['previous_hours']}h ahead).");
                }
            }
        }

        $this->info('Programme synchronization and EPG generation complete.');

        return self::SUCCESS;
    }
}
