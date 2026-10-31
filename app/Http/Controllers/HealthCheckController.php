<?php

namespace App\Http\Controllers;

use App\Services\SystemHealthService;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function __construct(
        protected SystemHealthService $healthService
    ) {}

    /**
     * Comprehensive system health endpoint for monitoring & Cloud probes.
     */
    public function check(): JsonResponse
    {
        $report = $this->healthService->getHealthReport();
        $httpCode = $report['status'] === 'unhealthy' ? 503 : 200;

        return response()->json($report, $httpCode, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Lightweight ping check for L4/L7 load balancers and ingress controllers.
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name', 'Zonita TV'),
            'timestamp' => now()->toIso8601String(),
        ], 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
