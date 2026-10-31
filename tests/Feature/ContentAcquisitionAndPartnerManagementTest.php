<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\ContentPartner;
use App\Models\ContentRight;
use App\Models\DistributionAgreement;
use App\Models\User;
use App\Models\Video;
use App\Services\Zonita\ContentAcquisitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAcquisitionAndPartnerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_crm_lifecycle_and_pipeline_stage_transitions(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $service = app(ContentAcquisitionService::class);

        // 1. Initial creation at prospect stage
        $partner = $service->createPartner([
            'name' => 'Koga Entertainment Studios',
            'partner_type' => 'studio',
            'pipeline_stage' => 'prospect',
            'country_code' => 'NGA',
            'website_url' => 'https://koga-entertainment.com',
            'contact_name' => 'Tunde Adebayo',
            'contact_email' => 'tunde@koga-studios.com',
            'contact_phone' => '+2348030000001',
            'assigned_agent_id' => $admin->id,
            'notes' => 'Catalog of 40 feature-length films and music specials.',
        ]);

        $this->assertDatabaseHas('content_partners', [
            'id' => $partner->id,
            'name' => 'Koga Entertainment Studios',
            'pipeline_stage' => 'prospect',
        ]);
        $this->assertFalse($partner->isActive());

        // 2. Advance stage sequentially
        $partner->advanceStage('contact_made');
        $this->assertEquals('contact_made', $partner->fresh()->pipeline_stage);

        $partner->advanceStage('negotiation');
        $this->assertEquals('negotiation', $partner->fresh()->pipeline_stage);

        // Test scope queries
        $this->assertEquals(1, ContentPartner::byStage('negotiation')->count());
        $this->assertEquals(0, ContentPartner::active()->count());

        // 3. Update stage via HTTP endpoint
        $response = $this->actingAs($admin)->post(route('admin.partners.stage', $partner->id), [
            'pipeline_stage' => 'agreement_signed',
            'notes' => 'Bilateral distribution terms agreed and signed.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('agreement_signed', $partner->fresh()->pipeline_stage);

        // Advance to active
        $service->updatePipelineStage($partner, 'active', 'Onboarding completed and live channels linked.');
        $this->assertTrue($partner->fresh()->isActive());
        $this->assertEquals(1, ContentPartner::active()->count());
    }

    public function test_distribution_agreement_creation_with_financial_terms(): void
    {
        $admin = User::factory()->create(['role' => 'content_manager']);
        $service = app(ContentAcquisitionService::class);

        $partner = ContentPartner::create([
            'name' => 'Ebonylife Media Group',
            'partner_type' => 'studio',
            'pipeline_stage' => 'agreement_signed',
            'country_code' => 'NGA',
            'contact_name' => 'Mo Abudu',
            'contact_email' => 'distribution@ebonylifegroup.com',
        ]);

        $agreement = $service->createAgreement($partner, [
            'title' => 'Exclusive Pan-African Digital Streaming License 2026-2028',
            'agreement_type' => 'revenue_share',
            'financial_terms' => [
                'revenue_share_percentage' => 45,
                'currency' => 'USD',
                'reporting_frequency' => 'monthly',
            ],
            'exclusivity_type' => 'exclusive_window',
            'start_date' => now()->startOfDay()->toDateString(),
            'end_date' => now()->addYears(2)->endOfDay()->toDateString(),
            'status' => 'draft',
            'terms_summary' => 'Includes 12 premium movie titles and 2 series seasons.',
        ]);

        $this->assertDatabaseHas('distribution_agreements', [
            'id' => $agreement->id,
            'content_partner_id' => $partner->id,
            'title' => 'Exclusive Pan-African Digital Streaming License 2026-2028',
            'agreement_type' => 'revenue_share',
            'exclusivity_type' => 'exclusive_window',
        ]);

        $this->assertMatchesRegularExpression('/^ZTV-AGR-\d{4}-[A-Z0-9]+$/', $agreement->agreement_number);
        $this->assertFalse($agreement->isExpired());
    }

    public function test_agreement_activation_mirrors_to_content_rights_ledger(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $service = app(ContentAcquisitionService::class);

        $partner = ContentPartner::create([
            'name' => 'TVC Communications',
            'partner_type' => 'tv_network',
            'pipeline_stage' => 'negotiation',
            'country_code' => 'NGA',
            'contact_name' => 'Head of Syndication',
            'contact_email' => 'syndication@tvccommunications.tv',
        ]);

        $channel = Channel::create([
            'name' => 'TVC News Africa Live',
            'slug' => 'tvc-news-africa-live',
            'official_destination_url' => 'https://youtube.com/@TVCNewsNigeria',
            'country_code' => 'NGA',
            'language_code' => 'en',
            'playback_mode' => 'direct_stream',
            'is_active' => true,
            'is_published' => true,
        ]);

        $agreement = DistributionAgreement::create([
            'content_partner_id' => $partner->id,
            'agreement_number' => DistributionAgreement::generateAgreementNumber(),
            'title' => '24/7 Linear News Channel Syndication Agreement',
            'agreement_type' => 'direct_licence',
            'financial_model' => 'flat_fee',
            'financial_terms' => [
                'flat_fee_amount' => 1500,
                'currency' => 'USD',
                'payment_interval' => 'annual',
            ],
            'exclusivity_type' => 'non_exclusive',
            'permitted_territories' => ['GLOBAL'],
            'blocked_territories' => [],
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'status' => 'draft',
        ]);

        // Activate agreement linked to Channel
        $activatedAgreement = $service->activateAgreement($agreement, [
            'channel_id' => $channel->id,
        ]);

        $this->assertEquals('active', $activatedAgreement->status);
        $this->assertEquals('active', $partner->fresh()->pipeline_stage);

        // Verify mirrored rights entry in content_rights ledger
        $this->assertDatabaseHas('content_rights', [
            'channel_id' => $channel->id,
            'rights_holder_name' => 'TVC Communications',
            'contract_reference' => $agreement->agreement_number,
            'classification' => 'direct_licence',
            'status' => 'embedding_approved',
            'is_exclusive' => false,
            'allow_web_embedding' => true,
            'allow_directory_listing' => true,
        ]);

        $mirroredRight = ContentRight::where('contract_reference', $agreement->agreement_number)->first();
        $this->assertNotNull($mirroredRight);
        $this->assertTrue($mirroredRight->isTerritoryPermitted('NGA'));
        $this->assertTrue($mirroredRight->isTerritoryPermitted('GBR'));
    }

    public function test_partner_delivered_content_inventory_and_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $service = app(ContentAcquisitionService::class);

        $partner = ContentPartner::create([
            'name' => 'Yoruba Hood Cinema Network',
            'partner_type' => 'studio',
            'pipeline_stage' => 'active',
            'country_code' => 'NGA',
        ]);

        // Associate 2 channels
        $channel1 = Channel::create([
            'name' => 'Yoruba Hood Classics',
            'slug' => 'yoruba-hood-classics',
            'official_destination_url' => 'https://youtube.com/@yorubahood',
            'country_code' => 'NGA',
            'language_code' => 'yo',
            'playback_mode' => 'direct_stream',
            'content_partner_id' => $partner->id,
            'is_active' => true,
            'is_published' => true,
        ]);

        $channel2 = Channel::create([
            'name' => 'Yoruba Hood Epics',
            'slug' => 'yoruba-hood-epics',
            'official_destination_url' => 'https://youtube.com/@yorubahoodepics',
            'country_code' => 'NGA',
            'language_code' => 'yo',
            'playback_mode' => 'direct_stream',
            'content_partner_id' => $partner->id,
            'is_active' => true,
            'is_published' => true,
        ]);

        // Associate 3 videos with durations (1800s, 3600s, 5400s = 10800s = 180 min)
        Video::create([
            'content_partner_id' => $partner->id,
            'channel_id' => $channel1->id,
            'youtube_id' => 'yh_vid_001',
            'title' => 'Ajani Ogun Reborn',
            'duration_seconds' => 1800,
            'availability_status' => 'approved',
            'format_type' => 'movie',
        ]);
        Video::create([
            'content_partner_id' => $partner->id,
            'channel_id' => $channel1->id,
            'youtube_id' => 'yh_vid_002',
            'title' => 'Jagun Jagun Chronicles',
            'duration_seconds' => 3600,
            'availability_status' => 'approved',
            'format_type' => 'movie',
        ]);
        Video::create([
            'content_partner_id' => $partner->id,
            'channel_id' => $channel2->id,
            'youtube_id' => 'yh_vid_003',
            'title' => 'Anikulapo Tales',
            'duration_seconds' => 5400,
            'availability_status' => 'approved',
            'format_type' => 'movie',
        ]);

        // Create 1 active agreement and 1 expired agreement
        DistributionAgreement::create([
            'content_partner_id' => $partner->id,
            'agreement_number' => DistributionAgreement::generateAgreementNumber(),
            'title' => 'Active Term Agreement',
            'financial_model' => 'revenue_share',
            'exclusivity_type' => 'non_exclusive',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        DistributionAgreement::create([
            'content_partner_id' => $partner->id,
            'agreement_number' => DistributionAgreement::generateAgreementNumber(),
            'title' => 'Expired Prior Agreement',
            'financial_model' => 'flat_fee',
            'exclusivity_type' => 'non_exclusive',
            'start_date' => now()->subYears(2),
            'end_date' => now()->subYear(),
            'status' => 'expired',
        ]);

        $metrics = $service->getPartnerPerformanceMetrics($partner);

        $this->assertEquals(2, $metrics['channels_count']);
        $this->assertEquals(3, $metrics['videos_count']);
        $this->assertEquals(1, $metrics['active_agreements_count']);
        $this->assertEquals(180, $metrics['total_runtime_minutes']);

        // Check show profile page renders inventory
        $response = $this->actingAs($admin)->get(route('admin.partners.show', $partner->id));
        $response->assertOk();
        $response->assertSee('Yoruba Hood Cinema Network');
        $response->assertSee('Yoruba Hood Classics');
        $response->assertSee('Ajani Ogun Reborn');
    }

    public function test_admin_partner_management_requires_privileged_role(): void
    {
        $partner = ContentPartner::create([
            'name' => 'Restricted Studio',
            'partner_type' => 'studio',
            'pipeline_stage' => 'prospect',
            'country_code' => 'NGA',
        ]);

        // Unauthenticated guest redirected to login
        $guestResp = $this->get(route('admin.partners.index'));
        $guestResp->assertRedirect(route('login'));

        // Non-privileged user gets 403 Forbidden
        $regularUser = User::factory()->create(['role' => 'user']);
        $forbiddenResp = $this->actingAs($regularUser)->get(route('admin.partners.index'));
        $forbiddenResp->assertForbidden();

        // Privileged super_admin gets 200 OK
        $admin = User::factory()->create(['role' => 'super_admin']);
        $okResp = $this->actingAs($admin)->get(route('admin.partners.index'));
        $okResp->assertOk();
        $okResp->assertSee('Partner CRM');
    }

    public function test_admin_can_update_partner_profile(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $partner = ContentPartner::create([
            'name' => 'Initial Studio Name',
            'partner_type' => 'studio',
            'pipeline_stage' => 'prospect',
            'country_code' => 'NGA',
            'contact_email' => 'old@studio.com',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.partners.update', $partner->id), [
            'name' => 'Updated Studio Holdings Ltd',
            'partner_type' => 'distributor',
            'pipeline_stage' => 'negotiation',
            'country_code' => 'GHA',
            'website_url' => 'https://updatedstudio.com',
            'contact_name' => 'Kwame Mensah',
            'contact_email' => 'kwame@updatedstudio.com',
            'contact_phone' => '+233240000000',
            'notes' => 'Pan-West African distributor covering Ghana and Nigeria.',
        ]);

        $response->assertRedirect(route('admin.partners.show', $partner->id));

        $this->assertDatabaseHas('content_partners', [
            'id' => $partner->id,
            'name' => 'Updated Studio Holdings Ltd',
            'partner_type' => 'distributor',
            'pipeline_stage' => 'negotiation',
            'country_code' => 'GHA',
            'contact_name' => 'Kwame Mensah',
        ]);
    }
}
