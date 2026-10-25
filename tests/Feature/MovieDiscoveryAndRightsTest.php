<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Movie;
use App\Services\Zonita\MovieRightsVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MovieDiscoveryAndRightsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'youtube.com/oembed*' => Http::response([
                'title' => 'Sample Broadcaster Video',
                'author_name' => 'Official Channel',
                'type' => 'video',
                'provider_name' => 'YouTube',
            ], 200),
            '*' => Http::response([], 200),
        ]);
    }

    public function test_movies_discover_and_verify_command_populates_catalogue_and_schedules_channels(): void
    {
        $this->artisan('movies:discover-and-verify')->assertExitCode(0)->run();

        // Check Movie Catalogue Counts
        $this->assertGreaterThanOrEqual(10, Movie::count());
        $this->assertGreaterThanOrEqual(10, Movie::where('verification_status', 'verified')->count());

        // Check 3 Dedicated Linear Channels
        $classics = Channel::where('slug', 'zonita-classics')->first();
        $this->assertNotNull($classics);
        $this->assertEquals('embed_player', $classics->playback_mode);
        $this->assertGreaterThan(0, $classics->scheduleEntries()->count());
        $this->assertGreaterThan(0, $classics->channelVideos()->count());

        $africanCinema = Channel::where('slug', 'zonita-african-cinema')->first();
        $this->assertNotNull($africanCinema);
        $this->assertGreaterThan(0, $africanCinema->scheduleEntries()->count());
        $this->assertGreaterThan(0, $africanCinema->channelVideos()->count());

        $documentaries = Channel::where('slug', 'zonita-documentaries')->first();
        $this->assertNotNull($documentaries);
        $this->assertGreaterThan(0, $documentaries->scheduleEntries()->count());
        $this->assertGreaterThan(0, $documentaries->channelVideos()->count());
    }

    public function test_rights_verification_approves_public_domain_and_authorized_distributor_movies(): void
    {
        $this->artisan('movies:discover-and-verify')->run();

        // 1. Verify Public Domain Title (Night of the Living Dead)
        $publicDomainMovie = Movie::where('title', 'Night of the Living Dead')->first();
        $this->assertNotNull($publicDomainMovie);
        $this->assertEquals('public_domain', $publicDomainMovie->rights_classification);
        $this->assertEquals('verified', $publicDomainMovie->verification_status);
        $this->assertTrue($publicDomainMovie->commercial_use_permitted);
        $this->assertTrue($publicDomainMovie->redistribution_permitted);
        $this->assertTrue($publicDomainMovie->public_performance_permitted);
        $this->assertTrue($publicDomainMovie->is_schedulable);

        // 2. Verify Authorized African Title (THE TRAIN)
        $africanMovie = Movie::where('title', 'like', '%THE TRAIN%')->first();
        $this->assertNotNull($africanMovie);
        $this->assertEquals('authorized_distributor', $africanMovie->rights_classification);
        $this->assertEquals('verified', $africanMovie->verification_status);
        $this->assertTrue($africanMovie->commercial_use_permitted);
        $this->assertTrue($africanMovie->is_schedulable);
        $this->assertNotNull($africanMovie->licence_reference);
    }

    public function test_rights_verification_rejects_expired_or_geo_blocked_movies(): void
    {
        $rightsService = app(MovieRightsVerificationService::class);

        // 1. Expired Licence Test
        $expiredMovie = Movie::create([
            'title' => 'Expired Indie Release',
            'slug' => 'expired-indie-release',
            'source_provider' => 'independent_filmmaker',
            'source_url' => 'https://example.com/expired',
            'rights_classification' => 'direct_licence',
            'licence_name' => 'Single-Year Direct Syndicate',
            'licence_reference' => 'LIC-EXPIRED-999',
            'licence_expires_at' => now()->subMonths(2),
            'commercial_use_permitted' => true,
            'redistribution_permitted' => true,
            'public_performance_permitted' => true,
            'permitted_territories' => ['GLOBAL'],
        ]);

        $resExpired = $rightsService->verifyMovieRights($expiredMovie);
        $this->assertFalse($resExpired['is_schedulable']);
        $this->assertEquals('expired', $resExpired['status']);
        $this->assertStringContainsString('expired', strtolower($resExpired['reason']));

        // 2. Geo-Blocked in Nigeria Test
        $blockedMovie = Movie::create([
            'title' => 'Europe Only Commercial Film',
            'slug' => 'europe-only-film',
            'source_provider' => 'independent_filmmaker',
            'source_url' => 'https://example.com/geo-blocked',
            'rights_classification' => 'direct_licence',
            'licence_name' => 'Restricted Territorial Grant',
            'licence_reference' => 'LIC-EU-ONLY-111',
            'commercial_use_permitted' => true,
            'redistribution_permitted' => true,
            'public_performance_permitted' => true,
            'permitted_territories' => ['GBR', 'FRA', 'DEU'],
            'blocked_territories' => ['NGA'],
        ]);

        $resBlocked = $rightsService->verifyMovieRights($blockedMovie);
        $this->assertFalse($resBlocked['is_schedulable']);
        $this->assertEquals('rights_rejected', $resBlocked['status']);
        $this->assertStringContainsString('nigeria', strtolower($resBlocked['reason']));
    }

    public function test_movie_directory_web_routes_render_successfully(): void
    {
        $this->artisan('movies:discover-and-verify')->run();

        // 1. Movie Directory Index
        $response = $this->get(route('movies.index'));
        $response->assertStatus(200);
        $response->assertSee('Zonita Movie Discovery');
        $response->assertSee('Night of the Living Dead');
        $response->assertSee('Zonita Classics');

        // 2. Movie Details & Rights Certificate
        $movie = Movie::first();
        $responseShow = $this->get(route('movies.show', $movie->slug));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Broadcast Rights Certificate');
        $responseShow->assertSee('Compliance Audit');
        $responseShow->assertSee($movie->title);
    }

    public function test_movie_channels_render_with_realtime_playout(): void
    {
        $this->artisan('movies:discover-and-verify')->run();

        $response = $this->get(route('channels.show', 'zonita-classics'));
        $response->assertStatus(200);
        $response->assertSee('Zonita Classics');
        $response->assertSee('24/7 REAL-TIME BROADCAST');
        $response->assertSee("'controls': 0", false);
    }
}
