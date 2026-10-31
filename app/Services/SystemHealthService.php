<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ScheduleEntry;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemHealthService
{
    /**
     * Run all diagnostic checks and return consolidated health report.
     */
    public function getHealthReport(): array
    {
        $startTime = microtime(true);

        $dbCheck = $this->checkDatabase();
        $cacheCheck = $this->checkCache();
        $storageCheck = $this->checkStorage();
        $playoutCheck = $this->checkPlayout();

        $services = [
            'database' => $dbCheck,
            'cache' => $cacheCheck,
            'storage' => $storageCheck,
            'playout' => $playoutCheck,
        ];

        $status = 'healthy';
        if ($dbCheck['status'] === 'unhealthy' || $cacheCheck['status'] === 'unhealthy') {
            $status = 'unhealthy';
        } elseif ($storageCheck['status'] === 'unhealthy' || $playoutCheck['status'] === 'degraded') {
            $status = 'degraded';
        }

        $totalDurationMs = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'status' => $status,
            'app_name' => config('app.name', 'Zonita TV'),
            'app_version' => '1.0.0-production',
            'environment' => config('app.env', 'production'),
            'timestamp' => now()->toIso8601String(),
            'response_time_ms' => $totalDurationMs,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'services' => $services,
        ];
    }

    /**
     * Check relational database connectivity and response latency.
     */
    public function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'latency_ms' => $latencyMs,
                'connection' => config('database.default'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check caching backend availability with read/write ping.
     */
    public function checkCache(): array
    {
        $start = microtime(true);
        $pingKey = 'zonita_health_ping_'.uniqid();

        try {
            Cache::put($pingKey, 'pong', 10);
            $retrieved = Cache::get($pingKey);
            Cache::forget($pingKey);

            if ($retrieved !== 'pong') {
                return [
                    'status' => 'unhealthy',
                    'error' => 'Cache ping write/read mismatch.',
                ];
            }

            return [
                'status' => 'healthy',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'driver' => config('cache.default'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage write/read disk access.
     */
    public function checkStorage(): array
    {
        $start = microtime(true);
        $testFile = 'health_check_test_'.uniqid().'.tmp';

        try {
            Storage::disk('local')->put($testFile, 'health-check');
            $contents = Storage::disk('local')->get($testFile);
            Storage::disk('local')->delete($testFile);

            if ($contents !== 'health-check') {
                return [
                    'status' => 'unhealthy',
                    'error' => 'Storage test file verification failed.',
                ];
            }

            return [
                'status' => 'healthy',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'disk' => 'local',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check MCR playout pipeline health (active channels and active schedule status).
     */
    public function checkPlayout(): array
    {
        try {
            $totalActiveChannels = Channel::where('is_active', true)->where('is_published', true)->count();
            $liveScheduleEntries = ScheduleEntry::where('status', 'playing')
                ->where('start_time', '<=', now())
                ->where('end_time', '>', now())
                ->count();

            $status = $totalActiveChannels > 0 ? 'healthy' : 'degraded';

            return [
                'status' => $status,
                'active_channels' => $totalActiveChannels,
                'currently_playing_entries' => $liveScheduleEntries,
                'message' => $status === 'healthy' ? 'Linear playout operations operational.' : 'No active channels detected.',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }
}
