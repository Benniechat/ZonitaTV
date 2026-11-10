<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class FoundationStandardsTest extends TestCase
{
    /**
     * Verify that the environment template provides canonical app configuration.
     */
    public function test_env_example_contains_core_configuration_keys(): void
    {
        $envExample = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('APP_NAME="Zonita TV"', $envExample);
        $this->assertStringContainsString('DB_CONNECTION=', $envExample);
        $this->assertStringContainsString('SESSION_DRIVER=', $envExample);
        $this->assertStringContainsString('QUEUE_CONNECTION=', $envExample);
        $this->assertStringContainsString('GOOGLE_CLIENT_ID=', $envExample);
        $this->assertStringContainsString('CLOUDINARY_CLOUD_NAME=', $envExample);
    }

    /**
     * Verify that Pint formatting configuration is present and valid JSON.
     */
    public function test_pint_json_is_valid(): void
    {
        $this->assertFileExists(base_path('pint.json'));

        $pintConfig = json_decode(file_get_contents(base_path('pint.json')), true);
        $this->assertIsArray($pintConfig);
        $this->assertEquals('laravel', $pintConfig['preset']);
    }

    /**
     * Verify standard API response format for success.
     */
    public function test_api_response_success_format(): void
    {
        $response = ApiResponse::success(['id' => 123, 'title' => 'Zonita Broadcast'], 'Resource retrieved');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Resource retrieved', $data['message']);
        $this->assertEquals(123, $data['data']['id']);
    }

    /**
     * Verify standard API response format for error.
     */
    public function test_api_response_error_format(): void
    {
        $response = ApiResponse::error('Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, ['email' => ['Required field']]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Validation failed', $data['message']);
        $this->assertArrayHasKey('email', $data['errors']);
    }

    /**
     * Verify database connection is active and responsive.
     */
    public function test_database_connection_is_healthy(): void
    {
        $result = DB::select('SELECT 1 as is_alive');
        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result[0]->is_alive);
    }

    /**
     * Verify health check probe returns 200 with 2.0 system metadata.
     */
    public function test_health_check_endpoint_returns_healthy_report(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'app_name',
                'app_version',
                'environment',
                'services' => [
                    'database',
                    'cache',
                    'storage',
                    'playout',
                ],
            ])
            ->assertJson([
                'app_version' => '2.0.0-production',
            ]);
    }

    /**
     * Verify lightweight ping endpoint responds with 200 OK.
     */
    public function test_ping_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/ping');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'ok',
            ]);
    }
}
