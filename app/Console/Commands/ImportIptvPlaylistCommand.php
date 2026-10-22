<?php

namespace App\Console\Commands;

use App\Services\IptvPlaylistIngestService;
use Illuminate\Console\Command;

class ImportIptvPlaylistCommand extends Command
{
    protected $signature = 'zoniva:import-iptv 
                            {--url=https://iptv-org.github.io/iptv/countries/ng.m3u : URL to the M3U playlist file}
                            {--country= : 2-letter country code filter (e.g. NG)}
                            {--limit=50 : Maximum number of channels to import}';

    protected $description = 'Import channels and stream feeds from an IPTV M3U playlist (e.g. IPTV-ORG)';

    public function handle(IptvPlaylistIngestService $ingestService): int
    {
        $url = $this->option('url');
        $country = $this->option('country');
        $limit = (int) $this->option('limit');

        $this->info("Fetching playlist from: {$url}");
        if ($country) {
            $this->comment("Filtering for country: {$country}");
        }
        $this->comment("Import limit: {$limit} channels");

        try {
            $report = $ingestService->ingestFromUrl($url, $country, $limit);

            $this->newLine();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Processed', $report['total_processed']],
                    ['New Channels Created', $report['imported']],
                    ['Existing Channels Updated', $report['updated']],
                    ['Skipped (Filtered / Incompatible)', $report['skipped']],
                ]
            );

            $this->info('IPTV playlist ingestion completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Ingest failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
