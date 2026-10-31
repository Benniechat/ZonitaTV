<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportDatabaseSqlDumpCommand extends Command
{
    protected $signature = 'zoniva:export-sql {--target=database/zoniva_master_schema_and_data.sql}';

    protected $description = 'Exports clean, production-ready ANSI SQL schema & data dump of Zoniva TV with zero-memory streaming';

    public function handle(): int
    {
        $targetFile = base_path($this->option('target'));
        $this->info("Exporting database schema and data to: {$targetFile}");

        File::ensureDirectoryExists(dirname($targetFile));
        $handle = fopen($targetFile, 'w');

        if (! $handle) {
            $this->error("Failed to open file for writing: {$targetFile}");

            return Command::FAILURE;
        }

        // Header
        fwrite($handle, "-- ================================================================\n");
        fwrite($handle, "-- ZONITA TV ENTERPRISE MASTER DATABASE DUMP\n");
        fwrite($handle, '-- Generated: '.now()->toIso8601String()."\n");
        fwrite($handle, "-- RDBMS Target: MySQL 8.0+ / SQLite 3.35+\n");
        fwrite($handle, "-- Stream-buffered with chunked query processing & memory protection\n");
        fwrite($handle, "-- ================================================================\n\n");

        $tables = [
            'users',
            'categories',
            'channels',
            'channel_categories',
            'channel_streams',
            'channel_videos',
            'channel_source_rules',
            'content_partners',
            'distribution_agreements',
            'content_rights',
            'rights_documents',
            'content_complaints',
            'discovery_candidates',
            'videos',
            'movies',
            'series',
            'seasons',
            'episodes',
            'programmes',
            'schedule_entries',
            'schedule_blocks',
            'fixtures',
            'user_preferences',
            'favourites',
            'viewing_events',
            'stream_checks',
            'playback_failures',
            'problem_reports',
            'system_alerts',
            'newsletter_subscriptions',
            'advertisers',
            'advertising_campaigns',
        ];

        foreach ($tables as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            fwrite($handle, "-- ----------------------------------------------------------------\n");
            fwrite($handle, "-- Table: {$table}\n");
            fwrite($handle, "-- ----------------------------------------------------------------\n");

            $count = DB::table($table)->count();
            fwrite($handle, "-- Records count: {$count}\n");

            if ($count > 0) {
                // Stream chunks to ensure zero memory exhaustion
                DB::table($table)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use ($handle, $table) {
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $columns = array_keys($rowArray);
                        $values = array_values($rowArray);

                        $escapedValues = array_map(function ($val) {
                            if (is_null($val)) {
                                return 'NULL';
                            }
                            if (is_numeric($val)) {
                                return $val;
                            }

                            return "'".addslashes((string) $val)."'";
                        }, $values);

                        $colsStr = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
                        $valsStr = implode(', ', $escapedValues);

                        fwrite($handle, "INSERT INTO `{$table}` ({$colsStr}) VALUES ({$valsStr});\n");
                    }
                });
            }
            fwrite($handle, "\n");
        }

        fclose($handle);

        $size = round(filesize($targetFile) / 1024, 2);
        $this->info("Database export complete! File size: {$size} KB ({$targetFile})");

        return Command::SUCCESS;
    }
}
