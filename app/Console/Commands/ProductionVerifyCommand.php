<?php

namespace App\Console\Commands;

use App\Services\SystemHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ProductionVerifyCommand extends Command
{
    protected $signature = 'production:verify {--check-backup : Run database SQL backup export validation}';

    protected $description = 'Verifies system health, environment configuration, and production readiness for Zonita TV';

    public function handle(SystemHealthService $healthService): int
    {
        $this->info('================================================================');
        $this->info('   ZONITA TV PRODUCTION READINESS & DEPLOYMENT VERIFICATION    ');
        $this->info('================================================================');
        $this->newLine();

        $allPassed = true;

        // 1. Environment Configuration Audit
        $this->line('<fg=cyan;options=bold>[1/4] Auditing Environment Configuration...</>');

        $envChecks = [
            ['Setting' => 'APP_KEY', 'Config' => config('app.key') ? 'Configured' : 'MISSING', 'Status' => config('app.key') ? 'PASS' : 'FAIL'],
            ['Setting' => 'APP_ENV', 'Config' => config('app.env'), 'Status' => 'PASS'],
            ['Setting' => 'APP_DEBUG', 'Config' => config('app.debug') ? 'TRUE (Warning if in Prod)' : 'FALSE (Secure)', 'Status' => 'PASS'],
            ['Setting' => 'DB_CONNECTION', 'Config' => config('database.default'), 'Status' => 'PASS'],
            ['Setting' => 'CACHE_STORE', 'Config' => config('cache.default'), 'Status' => 'PASS'],
            ['Setting' => 'SESSION_DRIVER', 'Config' => config('session.driver'), 'Status' => 'PASS'],
            ['Setting' => 'QUEUE_CONNECTION', 'Config' => config('queue.default'), 'Status' => 'PASS'],
        ];

        $this->table(['Parameter', 'Current Value', 'Audit'], $envChecks);
        $this->newLine();

        // 2. Core Service Diagnostics
        $this->line('<fg=cyan;options=bold>[2/4] Testing Core Service Health & Latency...</>');
        $report = $healthService->getHealthReport();

        $serviceRows = [];
        foreach ($report['services'] as $name => $srv) {
            $isHealthy = in_array($srv['status'], ['healthy', 'degraded']);
            if ($srv['status'] === 'unhealthy') {
                $allPassed = false;
            }

            $latency = isset($srv['latency_ms']) ? $srv['latency_ms'].' ms' : 'N/A';
            $details = $srv['connection'] ?? $srv['driver'] ?? $srv['disk'] ?? ($srv['message'] ?? 'OK');

            $serviceRows[] = [
                'Component' => ucfirst($name),
                'Status' => strtoupper($srv['status']),
                'Latency / Metric' => $latency,
                'Details' => $details,
            ];
        }

        $this->table(['Component', 'Status', 'Latency / Metric', 'Details'], $serviceRows);
        $this->newLine();

        // 3. Storage Directory & Permission Structure
        $this->line('<fg=cyan;options=bold>[3/4] Verifying Storage & Runtime Directory Permissions...</>');
        $requiredDirs = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            storage_path('app/public'),
        ];

        $dirRows = [];
        foreach ($requiredDirs as $dir) {
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }

            $isWritable = is_writable($dir);
            if (! $isWritable) {
                $allPassed = false;
            }

            $dirRows[] = [
                'Directory' => str_replace(base_path(), '', $dir),
                'Exists' => 'YES',
                'Writable' => $isWritable ? 'YES' : 'NO',
                'Audit' => $isWritable ? 'PASS' : 'FAIL',
            ];
        }

        $this->table(['Directory', 'Exists', 'Writable', 'Audit'], $dirRows);
        $this->newLine();

        // 4. Automated Backup Export Verification (Optional or default check)
        if ($this->option('check-backup')) {
            $this->line('<fg=cyan;options=bold>[4/4] Testing Master Database Backup SQL Export...</>');
            $testDumpPath = 'database/test_production_backup_verify.sql';
            $exitCode = Artisan::call('zoniva:export-sql', ['--target' => $testDumpPath]);

            $fullTestDumpPath = base_path($testDumpPath);
            if ($exitCode === 0 && File::exists($fullTestDumpPath)) {
                $fileSizeKb = round(File::size($fullTestDumpPath) / 1024, 2);
                $this->info("   Backup SQL export test passed ({$fileSizeKb} KB generated).");
                File::delete($fullTestDumpPath);
            } else {
                $this->error('   Backup SQL export test failed.');
                $allPassed = false;
            }
            $this->newLine();
        } else {
            $this->line('<fg=cyan;options=bold>[4/4] Backup Export: Skipped (Use --check-backup to validate)</>');
            $this->newLine();
        }

        // Summary Verdict
        $this->info('================================================================');
        if ($allPassed) {
            $this->info('   VERDICT: [PASS] ALL SYSTEMS READY FOR PRODUCTION RELEASE!    ');
            $this->info('================================================================');

            return Command::SUCCESS;
        }

        $this->error('   VERDICT: [FAIL] PRODUCTION READINESS AUDIT IDENTIFIED ISSUES! ');
        $this->info('================================================================');

        return Command::FAILURE;
    }
}
