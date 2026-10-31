<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\ContentComplaint;
use App\Models\NewsletterSubscription;
use App\Models\ProblemReport;
use App\Models\SystemAlert;
use App\Models\User;
use App\Services\AlertNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsAlertsAndSupportWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected AlertNotificationService $alertService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create([
            'email' => 'admin@zonitatv.com',
            'role' => 'super_admin',
        ]);
        $this->alertService = app(AlertNotificationService::class);
    }

    public function test_stream_failure_triggers_system_alert_and_marks_channel_unhealthy(): void
    {
        $channel = Channel::create([
            'name' => 'African Movie Channel',
            'slug' => 'african-movie-channel',
            'channel_number' => '301',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_technically_healthy' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $alert = $this->alertService->notifyStreamFailure(
            $channel,
            'ERR_DEAD_STREAM_150',
            'HLS origin connection timeout'
        );

        $this->assertInstanceOf(SystemAlert::class, $alert);
        $this->assertEquals('critical', $alert->level);
        $this->assertEquals('stream_failure', $alert->alert_type);
        $this->assertFalse($alert->is_resolved);
        $this->assertStringContainsString('ERR_DEAD_STREAM_150', $alert->message);

        $channel->refresh();
        $this->assertFalse($channel->is_technically_healthy);
    }

    public function test_system_alert_resolution_restores_channel_health(): void
    {
        $channel = Channel::create([
            'name' => 'Wazobia TV HD',
            'slug' => 'wazobia-tv-hd',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_technically_healthy' => true,
            'is_active' => true,
            'is_published' => true,
        ]);

        $alert = $this->alertService->notifyStreamFailure($channel, 'ERR_BUFFER_STALL');
        $this->assertFalse($channel->fresh()->is_technically_healthy);

        $resolved = $this->alertService->resolveAlert($alert);
        $this->assertTrue($resolved);

        $alert->refresh();
        $this->assertTrue($alert->is_resolved);
        $this->assertNotNull($alert->resolved_at);

        // Channel health restored
        $this->assertTrue($channel->fresh()->is_technically_healthy);
    }

    public function test_copyright_dispute_notification_creates_takedown_system_alert(): void
    {
        $channel = Channel::create([
            'name' => 'Copyright Subject Channel',
            'slug' => 'copyright-subject-channel',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        $complaint = ContentComplaint::create([
            'reference_number' => 'DMCA-2026-9901',
            'complainant_name' => 'Nollywood Copyright Guild',
            'complainant_email' => 'legal@nollywoodguild.org',
            'complaint_type' => 'copyright_infringement',
            'copyrighted_work_description' => 'Original Film Title',
            'infringing_url' => 'https://zonitatv.com/channel/copyright-subject-channel',
            'channel_id' => $channel->id,
            'status' => 'pending',
        ]);

        $alert = $this->alertService->notifyCopyrightTakedown($complaint);

        $this->assertInstanceOf(SystemAlert::class, $alert);
        $this->assertEquals('warning', $alert->level);
        $this->assertEquals('takedown_ticket', $alert->alert_type);
        $this->assertStringContainsString('DMCA-2026-9901', $alert->message);
    }

    public function test_public_problem_report_submission_creates_report_and_dispatches_alert(): void
    {
        $channel = Channel::create([
            'name' => 'Silverbird Television',
            'slug' => 'silverbird-tv',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        $response = $this->post(route('channels.report', $channel->id), [
            'email' => 'viewer@lagos.ng',
            'issue_type' => 'broken_stream',
            'description' => 'Video feed is showing a black screen with no audio.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('problem_reports', [
            'channel_id' => $channel->id,
            'email' => 'viewer@lagos.ng',
            'issue_type' => 'broken_stream',
            'status' => 'new',
        ]);

        $this->assertDatabaseHas('system_alerts', [
            'channel_id' => $channel->id,
            'alert_type' => 'user_problem_report',
        ]);
    }

    public function test_support_ticket_triage_updates_status_and_appends_notes(): void
    {
        $channel = Channel::create([
            'name' => 'TVC News Live',
            'slug' => 'tvc-news-live',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'zoniva_original',
            'is_active' => true,
            'is_published' => true,
        ]);

        $report = ProblemReport::create([
            'channel_id' => $channel->id,
            'email' => 'tech@viewer.org',
            'issue_type' => 'sound_issue',
            'description' => 'Audio is completely out of sync by 5 seconds.',
            'status' => 'new',
        ]);

        // 1. Visit support queue
        $resIndex = $this->actingAs($this->adminUser)->get(route('admin.support.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Problem Reports');
        $resIndex->assertSee('TVC News Live');

        // 2. Visit show ticket
        $resShow = $this->actingAs($this->adminUser)->get(route('admin.support.show', $report->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Problem Report Triage');
        $resShow->assertSee('Audio is completely out of sync');

        // 3. Update ticket to investigating with admin notes
        $resUpdate = $this->actingAs($this->adminUser)->post(route('admin.support.update', $report->id), [
            'status' => 'investigating',
            'admin_notes' => 'Adjusted audio offset in live transcode pipeline.',
        ]);

        $resUpdate->assertRedirect(route('admin.support.show', $report->id));
        $resUpdate->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('investigating', $report->status);
        $this->assertStringContainsString('Adjusted audio offset', $report->description);
    }

    public function test_whatsapp_community_guide_alert_dispatch(): void
    {
        NewsletterSubscription::create([
            'email' => 'subscriber1@gmail.com',
            'whatsapp_phone' => '+2348012345678',
            'is_active' => true,
        ]);

        NewsletterSubscription::create([
            'email' => 'subscriber2@gmail.com',
            'whatsapp_phone' => '+2348098765432',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.community.broadcast'), [
            'target_group' => 'all',
            'custom_message' => 'Nollywood Blockbuster Festival starting tonight at 8 PM WAT!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('system_alerts', [
            'alert_type' => 'whatsapp_broadcast',
            'is_resolved' => 1,
        ]);
    }

    public function test_unauthorized_guests_and_regular_users_cannot_access_alerts_or_support(): void
    {
        // 1. Guest redirected to login
        $this->get(route('admin.alerts.index'))->assertRedirect(route('login'));
        $this->get(route('admin.support.index'))->assertRedirect(route('login'));
        $this->get(route('admin.community.index'))->assertRedirect(route('login'));

        // 2. Regular user (role = 'user') gets 403 Forbidden
        $viewer = User::factory()->create(['role' => 'user']);
        $this->actingAs($viewer)->get(route('admin.alerts.index'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('admin.support.index'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('admin.community.index'))->assertStatus(403);
    }
}
