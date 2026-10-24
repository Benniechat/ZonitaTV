<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\ContentComplaint;
use App\Models\ScheduleEntry;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_disclaimer_page_renders_successfully(): void
    {
        $response = $this->get('/legal-disclaimer');

        $response->assertStatus(200);
        $response->assertSee('Legal Disclaimer &amp; Platform Notice', false);
        $response->assertSee('Third-Party Media Embedding Technology');
    }

    public function test_terms_of_use_page_renders_successfully(): void
    {
        $response = $this->get('/terms-of-use');

        $response->assertStatus(200);
        $response->assertSee('Terms of Use');
        $response->assertSee('Acceptable Use Requirements');
    }

    public function test_copyright_policy_page_renders_successfully(): void
    {
        $response = $this->get('/copyright-policy');

        $response->assertStatus(200);
        $response->assertSee('Copyright & Complaints Policy', false);
        $response->assertSee('Notice & Review Procedure', false);
    }

    public function test_privacy_policy_page_renders_successfully(): void
    {
        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
        $response->assertSee('Nigeria Data Protection Act 2023');
    }

    public function test_advertising_policy_page_renders_successfully(): void
    {
        $response = $this->get('/advertising-policy');

        $response->assertStatus(200);
        $response->assertSee('Advertising & Sponsorship Disclosure', false);
        $response->assertSee('Strict Technical Player Integrity');
    }

    public function test_content_rights_page_renders_successfully(): void
    {
        $response = $this->get('/content-rights');

        $response->assertStatus(200);
        $response->assertSee('Content Rights & Removal Policy', false);
        $response->assertSee('Software-Enforced Rights Lifecycle');
    }

    public function test_backward_compatible_aliases_redirect_correctly(): void
    {
        $privacy = $this->get('/privacy');
        $privacy->assertRedirect('/privacy-policy');

        $terms = $this->get('/terms');
        $terms->assertRedirect('/terms-of-use');
    }

    public function test_report_content_form_renders_successfully(): void
    {
        $response = $this->get('/report-content?url=https://zonitatv.onrender.com/channel/test&ref=test-stream');

        $response->assertStatus(200);
        $response->assertSee('Submit Content Complaint or Takedown Notice');
        $response->assertSee('https://zonitatv.onrender.com/channel/test');
    }

    public function test_complaint_submission_creates_record_and_generates_reference_number(): void
    {
        $payload = [
            'complainant_name' => 'Rights Enforcement Officer',
            'complainant_email' => 'takedown@broadcaster.com',
            'complainant_organization' => 'Premier Broadcast Network',
            'relationship_to_owner' => 'authorized_agent',
            'complaint_type' => 'copyright',
            'copyrighted_work_description' => 'Official Premier Championship Match Telecast (Season 2026/2027)',
            'infringing_url' => 'https://zonitatv.onrender.com/channel/sports-hd',
            'content_reference' => 'YOUTUBE_XYZ123',
            'infringement_details' => 'Broadcast rights for this match are exclusive to our network. Embedding on this platform is unauthorized.',
            'good_faith_declaration' => '1',
            'authority_confirmation' => '1',
        ];

        $response = $this->post('/report-content', $payload);

        $response->assertRedirect('/report-content');
        $response->assertSessionHas('complaint_success', true);
        $response->assertSessionHas('reference_number');

        $this->assertDatabaseHas('content_complaints', [
            'complainant_name' => 'Rights Enforcement Officer',
            'complainant_email' => 'takedown@broadcaster.com',
            'complaint_type' => 'copyright',
            'status' => 'pending',
            'content_reference' => 'YOUTUBE_XYZ123',
        ]);

        $complaint = ContentComplaint::where('complainant_email', 'takedown@broadcaster.com')->first();
        $this->assertNotNull($complaint);
        $this->assertStringStartsWith('ZTV-CMP-', $complaint->reference_number);
    }

    public function test_complaint_submission_requires_mandatory_declarations(): void
    {
        $payload = [
            'complainant_name' => 'Incomplete Complainant',
            'complainant_email' => 'incomplete@broadcaster.com',
            'relationship_to_owner' => 'owner',
            'complaint_type' => 'copyright',
            'copyrighted_work_description' => 'Protected Film Title',
            'infringing_url' => 'https://zonitatv.onrender.com/channel/movies',
            'infringement_details' => 'Unauthorised broadcast details here.',
            // Missing good_faith_declaration and authority_confirmation
        ];

        $response = $this->post('/report-content', $payload);

        $response->assertSessionHasErrors(['good_faith_declaration', 'authority_confirmation']);
        $this->assertDatabaseMissing('content_complaints', [
            'complainant_email' => 'incomplete@broadcaster.com',
        ]);
    }

    public function test_admin_can_view_complaints_register_and_details(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $complaint = ContentComplaint::create([
            'reference_number' => 'ZTV-CMP-20261024-TEST',
            'complainant_name' => 'Legal Director',
            'complainant_email' => 'legal@director.com',
            'relationship_to_owner' => 'owner',
            'complaint_type' => 'unauthorized_broadcast',
            'copyrighted_work_description' => 'Protected Broadcast Content',
            'infringing_url' => 'https://zonitatv.onrender.com/channel/live-feed',
            'content_reference' => 'TEST_STREAM_ID',
            'infringement_details' => 'Evidence of exclusive distribution right.',
            'good_faith_declaration' => true,
            'authority_confirmation' => true,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $indexResponse = $this->get('/admin/complaints');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('ZTV-CMP-20261024-TEST');
        $indexResponse->assertSee('Legal Director');

        $showResponse = $this->get("/admin/complaints/{$complaint->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('ZTV-CMP-20261024-TEST');
        $showResponse->assertSee('Execute Immediate Takedown');
    }

    public function test_admin_can_execute_takedown_and_purge_schedule(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $channel = Channel::create([
            'name' => 'Test Channel',
            'slug' => 'test-channel',
            'playback_mode' => 'embed_player',
            'official_destination_url' => 'https://youtube.com/@testchannel',
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'DISPUTED_YT_99',
            'title' => 'Disputed Match Stream',
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'confidence_score' => 95,
        ]);

        $schedule = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Disputed Match Stream',
            'duration_seconds' => 1800,
            'start_time' => now()->addMinutes(10),
            'end_time' => now()->addMinutes(40),
            'status' => 'scheduled',
        ]);

        $complaint = ContentComplaint::create([
            'reference_number' => 'ZTV-CMP-20261024-TKDN',
            'complainant_name' => 'Aggrieved Rights Owner',
            'complainant_email' => 'owner@rights.org',
            'relationship_to_owner' => 'owner',
            'complaint_type' => 'copyright',
            'copyrighted_work_description' => 'Proprietary sports recording',
            'infringing_url' => 'https://zonitatv.onrender.com/channel/test-channel',
            'content_reference' => 'DISPUTED_YT_99',
            'video_id' => $video->id,
            'channel_id' => $channel->id,
            'infringement_details' => 'Takedown demand under Copyright Act.',
            'good_faith_declaration' => true,
            'authority_confirmation' => true,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $takedownResponse = $this->post("/admin/complaints/{$complaint->id}/takedown", [
            'takedown_action' => 'disable_video',
            'admin_reason' => 'Valid copyright claim verified; stream removed from playout queues.',
        ]);

        $takedownResponse->assertRedirect("/admin/complaints/{$complaint->id}");

        $complaint->refresh();
        $this->assertEquals('content_disabled', $complaint->status);
        $this->assertNotNull($complaint->resolved_at);
        $this->assertEquals($admin->id, $complaint->resolved_by_user_id);

        $video->refresh();
        $this->assertEquals('disabled', $video->availability_status);
        $this->assertFalse($video->is_embeddable);
        $this->assertEquals(0, $video->confidence_score);
        $this->assertFalse($video->isPlayable());

        $schedule->refresh();
        $this->assertEquals('cancelled', $schedule->status);

        // Verify that disabled video is excluded from scheduling queries
        $eligibleVideos = Video::eligibleForScheduling()->pluck('id');
        $this->assertNotContains($video->id, $eligibleVideos);
    }
}
