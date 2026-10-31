<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\ScheduleEntry;
use App\Models\Video;
use App\Services\SystemHealthService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductionDeploymentAndHandoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_health_endpoint_returns_json_diagnostics_and_healthy_status(): void
    {
        // Set up active channel and schedule entry so playout is healthy
        $channel = Channel::create([
            'name' => 'Zonita Premier Live',
            'slug' => 'zonita-premier-live',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'title' => 'Morning Broadcast',
            'slug' => 'morning-broadcast',
            'youtube_id' => 'morn_broad_11',
            'duration_seconds' => 1800,
            'is_active' => true,
        ]);

        ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Morning Broadcast',
            'duration_seconds' => 1800,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(20),
            'status' => 'playing',
        ]);

        // Web route: /health
        $responseWeb = $this->get('/health');
        $responseWeb->assertStatus(200);
        $this->assertStringContainsString('no-cache', (string) $responseWeb->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $responseWeb->headers->get('Cache-Control'));
        $responseWeb->assertJsonStructure([
            'status',
            'app_name',
            'app_version',
            'environment',
            'timestamp',
            'response_time_ms',
            'php_version',
            'laravel_version',
            'memory_usage_mb',
            'services' => [
                'database' => ['status', 'latency_ms', 'connection'],
                'cache' => ['status', 'driver'],
                'storage' => ['status', 'disk'],
                'playout' => ['status', 'active_channels', 'currently_playing_entries'],
            ],
        ]);
        $responseWeb->assertJsonPath('status', 'healthy');
        $responseWeb->assertJsonPath('services.database.status', 'healthy');
        $responseWeb->assertJsonPath('services.cache.status', 'healthy');
        $responseWeb->assertJsonPath('services.storage.status', 'healthy');
        $responseWeb->assertJsonPath('services.playout.status', 'healthy');

        // API route alias: /api/health
        $responseApi = $this->getJson('/api/health');
        $responseApi->assertStatus(200);
        $responseApi->assertJsonPath('status', 'healthy');
    }

    public function test_system_ping_endpoint_returns_minimal_load_balancer_heartbeat(): void
    {
        $response = $this->getJson('/ping');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
            'service' => config('app.name', 'Zonita TV'),
        ]);

        $responseApi = $this->getJson('/api/ping');
        $responseApi->assertStatus(200);
        $responseApi->assertJsonPath('status', 'ok');
    }

    public function test_system_health_fails_gracefully_with_503_when_service_is_unhealthy(): void
    {
        $mockHealthService = $this->createMock(SystemHealthService::class);
        $mockHealthService->method('getHealthReport')->willReturn([
            'status' => 'unhealthy',
            'app_name' => 'Zonita TV',
            'app_version' => '1.0.0-production',
            'environment' => 'production',
            'timestamp' => now()->toIso8601String(),
            'response_time_ms' => 12.5,
            'services' => [
                'database' => ['status' => 'unhealthy', 'error' => 'Connection refused'],
                'cache' => ['status' => 'healthy', 'driver' => 'database'],
                'storage' => ['status' => 'healthy', 'disk' => 'local'],
                'playout' => ['status' => 'healthy', 'active_channels' => 1],
            ],
        ]);

        $this->app->instance(SystemHealthService::class, $mockHealthService);

        $response = $this->getJson('/health');
        $response->assertStatus(503);
        $response->assertJsonPath('status', 'unhealthy');
        $response->assertJsonPath('services.database.status', 'unhealthy');
    }

    public function test_production_verify_artisan_command_passes_with_zero_exit_code(): void
    {
        $exitCode = Artisan::call('production:verify');
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('ZONITA TV PRODUCTION READINESS', $output);
        $this->assertStringContainsString('VERDICT: [PASS]', $output);
    }

    public function test_production_verify_with_backup_check_exports_and_validates_sql_dump(): void
    {
        $exitCode = Artisan::call('production:verify', ['--check-backup' => true]);
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Database export complete', $output);
    }

    public function test_master_database_backup_export_command_creates_valid_streamed_dump(): void
    {
        $testTarget = 'database/test_export_backup_verify.sql';
        $fullPath = base_path($testTarget);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        $exitCode = Artisan::call('zoniva:export-sql', ['--target' => $testTarget]);
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Database export complete', $output);
        $this->assertTrue(File::exists($fullPath));

        $fileContent = File::get($fullPath);
        $this->assertStringContainsString('ZONITA TV ENTERPRISE MASTER DATABASE DUMP', $fileContent);
        $this->assertStringContainsString('Table: users', $fileContent);
        $this->assertStringContainsString('Table: channels', $fileContent);

        // Clean up
        File::delete($fullPath);
    }

    public function test_scheduler_contains_all_production_recurring_broadcast_and_maintenance_jobs(): void
    {
        $schedule = app()->make(Schedule::class);
        $events = collect($schedule->events());

        // Extract command or description representations
        $commandDescriptions = $events->map(function ($event) {
            return $event->command ?? $event->description ?? $event->getSummaryForDisplay();
        })->implode(' ');

        $this->assertStringContainsString('playout:advance', $commandDescriptions);
        $this->assertStringContainsString('rights:monitor-expirations', $commandDescriptions);
        $this->assertStringContainsString('zoniva:export-sql', $commandDescriptions);
        $this->assertStringContainsString('sports:sync-fixtures', $commandDescriptions);
        $this->assertStringContainsString('sports:verify-broadcasts', $commandDescriptions);
    }

    public function test_operations_runbook_and_production_environment_files_exist_and_are_complete(): void
    {
        $runbookPath = base_path('docs/OPERATIONS_RUNBOOK.md');
        $this->assertTrue(File::exists($runbookPath), 'docs/OPERATIONS_RUNBOOK.md must exist');

        $runbook = File::get($runbookPath);
        $this->assertStringContainsString('Pre-Flight Production Deployment Checklist', $runbook);
        $this->assertStringContainsString('Standard Deployment Command Sequence', $runbook);
        $this->assertStringContainsString('MCR Playout Continuity & Incident Triage Runbook', $runbook);
        $this->assertStringContainsString('Content Rights & Takedown SLA Runbook', $runbook);
        $this->assertStringContainsString('Disaster Recovery & Backup Restoration', $runbook);
        $this->assertStringContainsString('Monitoring & Observability Endpoints', $runbook);

        $envProdPath = base_path('.env.production.example');
        $this->assertTrue(File::exists($envProdPath), '.env.production.example must exist');

        $envProd = File::get($envProdPath);
        $this->assertStringContainsString('APP_ENV=production', $envProd);
        $this->assertStringContainsString('DB_CONNECTION=pgsql', $envProd);
        $this->assertStringContainsString('CLOUDINARY_CLOUD_NAME=', $envProd);
        $this->assertStringContainsString('DMCA_NOTICE_EMAIL=', $envProd);
    }
}
