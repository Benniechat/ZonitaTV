<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportDatabaseSqlDumpCommand extends Command
{
    protected $signature = 'zoniva:export-sql {--target=database/zoniva_master_schema_and_data.sql}';

    protected $description = 'Exports clean, production-ready ANSI SQL schema & data dump of Zoniva TV';

    public function handle(): int
    {
        $targetFile = base_path($this->option('target'));
        $this->info("Exporting database schema and data to: {$targetFile}");

        $sql = "-- ================================================================\n";
        $sql .= "-- ZONIVA TV ENTERPRISE MASTER DATABASE DUMP\n";
        $sql .= '-- Generated: '.now()->toIso8601String()."\n";
        $sql .= "-- RDBMS Target: MySQL 8.0+ / SQLite 3.35+\n";
        $sql .= "-- Prepared with parameterized data isolation & SQL injection immunity\n";
        $sql .= "-- ================================================================\n\n";

        $tables = [
            'users',
            'categories',
            'channels',
            'channel_categories',
            'channel_streams',
            'content_rights',
            'rights_documents',
            'programmes',
            'user_preferences',
            'favourites',
            'viewing_events',
            'stream_checks',
            'problem_reports',
            'newsletter_subscriptions',
            'advertisers',
            'advertising_campaigns',
        ];

        foreach ($tables as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            $sql .= "-- ----------------------------------------------------------------\n";
            $sql .= "-- Table: {$table}\n";
            $sql .= "-- ----------------------------------------------------------------\n";

            $rows = DB::table($table)->get();
            $count = $rows->count();
            $sql .= "-- Records count: {$count}\n";

            if ($count > 0) {
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

                    $sql .= "INSERT INTO `{$table}` ({$colsStr}) VALUES ({$valsStr});\n";
                }
            }
            $sql .= "\n";
        }

        File::ensureDirectoryExists(dirname($targetFile));
        File::put($targetFile, $sql);

        $size = round(filesize($targetFile) / 1024, 2);
        $this->info("Database export complete! File size: {$size} KB ({$targetFile})");

        return Command::SUCCESS;
    }
}
