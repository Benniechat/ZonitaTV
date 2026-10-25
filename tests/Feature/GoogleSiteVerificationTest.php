<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleSiteVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Google site verification HTML file endpoint returns 200 OK with expected content.
     */
    public function test_google_site_verification_html_endpoint(): void
    {
        $response = $this->get('/google98a49cab5be19ce2.html');

        $response->assertStatus(200);
        $this->assertEquals('google-site-verification: google98a49cab5be19ce2.html', trim($response->getContent()));
    }

    /**
     * Test Google site verification meta tag is present on the layout.
     */
    public function test_google_site_verification_meta_tag(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<meta name="google-site-verification" content="4m1NYPs5Z60SZPllspbNVhbJmHKV-K0qmBWuPZ1KuIk" />', false);
    }
}
