<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityPerformanceAndAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_applied_to_all_web_responses(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // 1. Content Type Options nosniff
        $response->assertHeader('X-Content-Type-Options', 'nosniff');

        // 2. Clickjacking protection
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');

        // 3. Referrer Policy
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 4. Permissions Policy
        $this->assertStringContainsString('camera=()', $response->headers->get('Permissions-Policy'));
        $this->assertStringContainsString('microphone=()', $response->headers->get('Permissions-Policy'));

        // 5. Content Security Policy (CSP)
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('https://www.youtube.com', $csp);
        $this->assertStringContainsString('https://res.cloudinary.com', $csp);
    }

    public function test_hsts_security_header_present_on_secure_requests(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
        ])->get(route('home'));

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('Strict-Transport-Security'));
        $this->assertStringContainsString('max-age=31536000', $response->headers->get('Strict-Transport-Security'));
    }

    public function test_rate_limiting_definitions_registered_in_service_provider(): void
    {
        // 1. Search Rate Limiter (30 requests/min)
        $searchLimiter = RateLimiter::limiter('search');
        $this->assertNotNull($searchLimiter);
        $requestSearch = Request::create('/search', 'GET');
        $searchLimit = $searchLimiter($requestSearch);
        $this->assertInstanceOf(Limit::class, $searchLimit);
        $this->assertEquals(30, $searchLimit->maxAttempts);

        // 2. Reports Rate Limiter (5 requests/10 min)
        $reportsLimiter = RateLimiter::limiter('reports');
        $this->assertNotNull($reportsLimiter);
        $requestReports = Request::create('/channel/1/report', 'POST');
        $reportsLimit = $reportsLimiter($requestReports);
        $this->assertInstanceOf(Limit::class, $reportsLimit);
        $this->assertEquals(5, $reportsLimit->maxAttempts);

        // 3. API Rate Limiter (60 requests/min)
        $apiLimiter = RateLimiter::limiter('api');
        $this->assertNotNull($apiLimiter);
        $requestApi = Request::create('/api/recommendations', 'GET');
        $apiLimit = $apiLimiter($requestApi);
        $this->assertInstanceOf(Limit::class, $apiLimit);
        $this->assertEquals(60, $apiLimit->maxAttempts);

        // 4. Telemetry Rate Limiter (120 requests/min)
        $telemetryLimiter = RateLimiter::limiter('telemetry');
        $this->assertNotNull($telemetryLimiter);
        $requestTelemetry = Request::create('/api/playback/status/test', 'GET');
        $telemetryLimit = $telemetryLimiter($requestTelemetry);
        $this->assertInstanceOf(Limit::class, $telemetryLimit);
        $this->assertEquals(120, $telemetryLimit->maxAttempts);
    }

    public function test_epg_xmltv_feed_implements_caching_and_cache_control_headers(): void
    {
        $channel = Channel::create([
            'name' => 'Cache Test News HD',
            'slug' => 'cache-test-news-hd',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        Cache::forget('epg_xmltv_feed_all_7_UTC');

        $response = $this->get(route('api.epg.xmltv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->assertStringContainsString('max-age=300', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));

        // Confirm result is cached in Cache store
        $this->assertTrue(Cache::has('epg_xmltv_feed_all_7_UTC'));
        $cachedXml = Cache::get('epg_xmltv_feed_all_7_UTC');
        $this->assertNotEmpty($cachedXml);
        $this->assertStringContainsString('<?xml', $cachedXml);
    }

    public function test_wcag_2_1_aa_accessibility_landmarks_and_skip_to_content(): void
    {
        Category::create([
            'name' => 'News & Current Affairs',
            'slug' => 'news',
            'is_nav_visible' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // 1. Skip to main content accessible link
        $response->assertSee('Skip to main content');
        $response->assertSee('href="#main-content"', false);

        // 2. Accessible landmarks
        $response->assertSee('role="banner"', false);
        $response->assertSee('role="main"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('role="search"', false);

        // 3. Brand Logo accessible label
        $response->assertSee('aria-label="Zonita TV Homepage"', false);
    }
}
