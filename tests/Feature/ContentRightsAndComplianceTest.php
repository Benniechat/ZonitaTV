<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\ContentComplaint;
use App\Models\ContentRight;
use App\Models\ScheduleEntry;
use App\Models\User;
use App\Models\Video;
use App\Services\RightsEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentRightsAndComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_rights_ledger_persistence_and_classifications(): void
    {
        $channel = Channel::create([
            'name' => 'African Movie Network',
            'slug' => 'african-movie-network',
            'official_destination_url' => 'https://youtube.com/@AfricanMovieNet',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
        ]);

        $right = ContentRight::create([
            'channel_id' => $channel->id,
            'rights_holder_name' => 'Nollywood Distribution Consortium',
            'contract_reference' => 'NDC-2026-BILAT-09',
            'classification' => 'direct_licence',
            'licence_name' => 'African Broadcast Rights Agreement v3',
            'licensing_agreement_text' => 'Full non-exclusive linear and digital broadcast rights granted across Africa.',
            'attribution_requirements' => 'Courtesy of Nollywood Distribution Consortium',
            'commercial_terms' => [
                'fee_type' => 'revenue_share',
                'percentage' => 30,
            ],
            'status' => 'embedding_approved',
            'allow_directory_listing' => true,
            'allow_web_embedding' => true,
            'allow_direct_hls' => true,
            'allow_monetisation' => true,
            'is_exclusive' => false,
            'auto_delist_on_expiry' => true,
            'effective_from' => now()->subMonth(),
            'expires_at' => now()->addYear(),
            'permitted_territories' => ['NGA', 'GHA', 'KEN'],
            'blocked_territories' => ['USA'],
        ]);

        $this->assertDatabaseHas('content_rights', [
            'id' => $right->id,
            'channel_id' => $channel->id,
            'contract_reference' => 'NDC-2026-BILAT-09',
            'classification' => 'direct_licence',
            'status' => 'embedding_approved',
            'is_exclusive' => false,
            'auto_delist_on_expiry' => true,
        ]);

        $fresh = ContentRight::find($right->id);
        $this->assertEquals(30, $fresh->commercial_terms['percentage']);
        $this->assertFalse($fresh->isExpired());
    }

    public function test_territorial_rights_enforcement(): void
    {
        $right = ContentRight::create([
            'rights_holder_name' => 'Regional Broadcaster',
            'status' => 'embedding_approved',
            'classification' => 'direct_licence',
            'effective_from' => now()->subMonth(),
            'permitted_territories' => ['NGA', 'GHA', 'ZAF'],
            'blocked_territories' => ['USA', 'GBR'],
        ]);

        // Permitted countries
        $this->assertTrue($right->isTerritoryPermitted('NGA'));
        $this->assertTrue($right->isTerritoryPermitted('gha'));
        $this->assertTrue($right->isTerritoryPermitted('ZAF'));

        // Blocked countries
        $this->assertFalse($right->isTerritoryPermitted('USA'));
        $this->assertFalse($right->isTerritoryPermitted('gbr'));

        // Unlisted country when territory is restricted
        $this->assertFalse($right->isTerritoryPermitted('CAN'));

        // Global unrestricted rights
        $globalRight = ContentRight::create([
            'rights_holder_name' => 'Global Public Domain Archive',
            'status' => 'embedding_approved',
            'classification' => 'public_domain',
            'effective_from' => now()->subMonth(),
            'permitted_territories' => ['GLOBAL'],
            'blocked_territories' => ['PRK'],
        ]);

        $this->assertTrue($globalRight->isTerritoryPermitted('NGA'));
        $this->assertTrue($globalRight->isTerritoryPermitted('USA'));
        $this->assertTrue($globalRight->isTerritoryPermitted('FRA'));
        $this->assertFalse($globalRight->isTerritoryPermitted('PRK'));
    }

    public function test_automated_rights_expiration_monitor_delists_expired_content(): void
    {
        // 1. Expired Channel Rights
        $channel = Channel::create([
            'name' => 'Expiring Sports TV',
            'slug' => 'expiring-sports-tv',
            'official_destination_url' => 'https://youtube.com/@ExpiringSports',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
        ]);

        $channelRight = ContentRight::create([
            'channel_id' => $channel->id,
            'rights_holder_name' => 'Sports League',
            'classification' => 'direct_licence',
            'status' => 'embedding_approved',
            'auto_delist_on_expiry' => true,
            'effective_from' => now()->subYear(),
            'expires_at' => now()->subDay(), // Expired yesterday
        ]);

        // 2. Expired Video Rights with future schedule
        $video = Video::create([
            'youtube_id' => 'v_expired_video_01',
            'title' => 'Expired Licensing Film',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'rights_basis' => 'direct_licence',
            'format_type' => 'feature',
        ]);

        $videoRight = ContentRight::create([
            'video_id' => $video->id,
            'rights_holder_name' => 'Film Studio',
            'classification' => 'direct_licence',
            'status' => 'embedding_approved',
            'auto_delist_on_expiry' => true,
            'effective_from' => now()->subYear(),
            'expires_at' => now()->subHours(2), // Expired 2 hours ago
        ]);

        $futureSchedule = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Future Broadcast of Expired Film',
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(3),
            'duration_seconds' => 3600,
            'play_order' => 1,
            'status' => 'scheduled',
        ]);

        // Run expiration monitor purge
        $result = app(RightsEligibilityService::class)->purgeExpiredRights();
        $this->assertEquals(2, $result['expired_count']);
        $this->assertEquals(1, $result['purged_schedules_count']);

        // Assert channel right marked expired and channel de-listed
        $channelRight->refresh();
        $this->assertEquals('expired', $channelRight->status);
        $channel->refresh();
        $this->assertFalse($channel->is_published);

        // Assert video right marked expired and video availability revoked
        $videoRight->refresh();
        $this->assertEquals('expired', $videoRight->status);
        $video->refresh();
        $this->assertEquals('expired', $video->availability_status);
        $this->assertFalse($video->is_embeddable);

        // Assert future schedule entry cancelled
        $futureSchedule->refresh();
        $this->assertEquals('cancelled', $futureSchedule->status);
    }

    public function test_takedown_workflow_immediately_delists_reported_video_and_logs_audit(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $channel = Channel::create([
            'name' => 'General Broadcast Net',
            'slug' => 'general-broadcast-net',
            'official_destination_url' => 'https://youtube.com/@GeneralNet',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
        ]);

        $video = Video::create([
            'youtube_id' => 'v_takedown_target_99',
            'title' => 'Contested Footage Segment',
            'duration_seconds' => 1200,
            'availability_status' => 'approved',
            'is_embeddable' => true,
            'confidence_score' => 100,
            'rights_basis' => 'official_embed_permitted',
            'format_type' => 'clip',
        ]);

        $schedule = ScheduleEntry::create([
            'channel_id' => $channel->id,
            'video_id' => $video->id,
            'title' => 'Upcoming Contested Clip Playout',
            'start_time' => now()->addHours(4),
            'end_time' => now()->addHours(5),
            'duration_seconds' => 1200,
            'play_order' => 1,
            'status' => 'scheduled',
        ]);

        $complaint = ContentComplaint::create([
            'reference_number' => ContentComplaint::generateReferenceNumber(),
            'complainant_name' => 'Original Copyright Holder',
            'complainant_email' => 'legal@originalholder.com',
            'relationship_to_owner' => 'Authorized Legal Representative',
            'complaint_type' => 'dmca_takedown',
            'copyrighted_work_description' => 'Original documentary broadcast without bilateral license',
            'infringing_url' => 'https://zonitatv.com/channel/general-broadcast-net',
            'infringement_details' => 'Full documentary footage used without authorized license.',
            'content_reference' => 'v_takedown_target_99',
            'video_id' => $video->id,
            'channel_id' => $channel->id,
            'good_faith_declaration' => true,
            'authority_confirmation' => true,
            'status' => 'pending',
        ]);

        // Admin executes takedown
        $response = $this->actingAs($admin)->post(route('admin.complaints.takedown', $complaint), [
            'takedown_action' => 'disable_video',
            'admin_reason' => 'DMCA takedown verified and executed pursuant to notice from rights holder.',
        ]);

        $response->assertRedirect(route('admin.complaints.show', $complaint));

        // Assert video disabled and purged
        $video->refresh();
        $this->assertEquals('disabled', $video->availability_status);
        $this->assertFalse($video->is_embeddable);
        $this->assertEquals(0, $video->confidence_score);

        // Assert future scheduled entry cancelled
        $schedule->refresh();
        $this->assertEquals('cancelled', $schedule->status);

        // Assert complaint record updated with audit trace
        $complaint->refresh();
        $this->assertEquals('content_disabled', $complaint->status);
        $this->assertEquals($admin->id, $complaint->resolved_by_user_id);
        $this->assertNotNull($complaint->resolved_at);
        $this->assertStringContainsString('DMCA takedown verified', $complaint->resolution_notes);
    }

    public function test_admin_rights_management_requires_privileged_role(): void
    {
        // Guest redirected
        $guest = $this->get(route('admin.rights.index'));
        $guest->assertRedirect(route('login'));

        // Standard user forbidden
        $user = User::factory()->create(['role' => 'user']);
        $userRes = $this->actingAs($user)->get(route('admin.rights.index'));
        $userRes->assertForbidden();

        // Rights reviewer permitted
        $reviewer = User::factory()->create(['role' => 'rights_reviewer']);
        $revRes = $this->actingAs($reviewer)->get(route('admin.rights.index'));
        $revRes->assertOk();

        // Super admin permitted
        $admin = User::factory()->create(['role' => 'super_admin']);
        $adminRes = $this->actingAs($admin)->get(route('admin.rights.index'));
        $adminRes->assertOk();
    }

    public function test_admin_can_store_and_update_rights_agreement(): void
    {
        $reviewer = User::factory()->create(['role' => 'rights_reviewer']);

        $postData = [
            'rights_holder_name' => 'Metro Cinema Distribution',
            'contract_reference' => 'CTR-METRO-2026',
            'classification' => 'direct_licence',
            'licence_name' => 'West Africa Theatrical & Digital Licence',
            'status' => 'embedding_approved',
            'effective_from' => now()->toDateString(),
            'expires_at' => now()->addMonths(6)->toDateString(),
            'permitted_territories' => 'NGA, GHA, SEN',
            'attribution_requirements' => 'Courtesy of Metro Cinema',
            'allow_directory_listing' => '1',
            'allow_web_embedding' => '1',
            'auto_delist_on_expiry' => '1',
        ];

        $storeResponse = $this->actingAs($reviewer)->post(route('admin.rights.store'), $postData);
        $storeResponse->assertRedirect(route('admin.rights.index'));

        $right = ContentRight::where('contract_reference', 'CTR-METRO-2026')->first();
        $this->assertNotNull($right);
        $this->assertEquals('Metro Cinema Distribution', $right->rights_holder_name);
        $this->assertContains('SEN', $right->permitted_territories);

        // Update status and reviewer notes
        $updateResponse = $this->actingAs($reviewer)->post(route('admin.rights.update-status', $right->id), [
            'status' => 'redistribution_approved',
            'reviewer_notes' => 'Contract signed by executive management. Full redistribution authorized.',
            'allow_directory_listing' => '1',
            'allow_web_embedding' => '1',
            'allow_direct_hls' => '1',
        ]);

        $updateResponse->assertRedirect();
        $right->refresh();
        $this->assertEquals('redistribution_approved', $right->status);
        $this->assertTrue($right->allow_direct_hls);
        $this->assertStringContainsString('Full redistribution authorized', $right->reviewer_notes);
    }
}
