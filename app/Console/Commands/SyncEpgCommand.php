<?php

namespace App\Console\Commands;

use App\Services\EpgSyncService;
use Illuminate\Console\Command;

class SyncEpgCommand extends Command
{
    protected $signature = 'zoniva:sync-epg 
                            {--source= : Remote XMLTV URL or local file path}
                            {--generate : Generate continuous dynamic EPG programming blocks for all channels}';

    protected $description = 'Synchronize Electronic Program Guide (EPG) from XMLTV feeds or generate dynamic schedule timelines';

    public function handle(EpgSyncService $epgService): int
    {
        $source = $this->option('source');
        $generate = $this->option('generate');

        if ($source) {
            $this->info("Synchronizing EPG from XMLTV source: {$source}");
            try {
                $result = $epgService->syncFromUrl($source);
                $this->info("XMLTV Sync finished. Programmes updated: {$result['synced']}, Skipped: {$result['skipped']}");

                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error('EPG Sync error: '.$e->getMessage());

                return self::FAILURE;
            }
        }

        // Default or --generate: populate dynamic rolling EPG for all channels
        $this->info('Generating continuous dynamic EPG schedule timelines for all active channels...');
        $count = $epgService->generateDynamicSchedulesForAllChannels();
        $this->info("Successfully generated {$count} programme schedule entries across the catalogue.");

        return self::SUCCESS;
    }
}
