<?php

namespace App\Services\Zonita;

use App\Models\ContentPartner;
use App\Models\ContentRight;
use App\Models\DistributionAgreement;
use App\Models\User;
use Illuminate\Support\Str;

class ContentAcquisitionService
{
    /**
     * Onboard a new prospective content partner.
     */
    public function createPartner(array $data, ?User $agent = null): ContentPartner
    {
        return ContentPartner::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.rand(100, 999),
            'partner_type' => $data['partner_type'] ?? 'studio',
            'pipeline_stage' => $data['pipeline_stage'] ?? 'prospect',
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'country_code' => strtoupper($data['country_code'] ?? 'NGA'),
            'assigned_agent_id' => $agent?->id ?? ($data['assigned_agent_id'] ?? null),
            'lead_source' => $data['lead_source'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Transition a partner across acquisition pipeline stages.
     */
    public function updatePipelineStage(ContentPartner $partner, string $newStage, ?string $notes = null): ContentPartner
    {
        $partner->advanceStage($newStage);

        if ($notes) {
            $partner->notes = ($partner->notes ? $partner->notes."\n\n" : '').
                '['.now()->toDateString()." Stage Change to {$newStage}]: {$notes}";
            $partner->save();
        }

        return $partner;
    }

    /**
     * Create a distribution agreement for a content partner.
     */
    public function createAgreement(ContentPartner $partner, array $data): DistributionAgreement
    {
        $agreementNumber = $data['agreement_number'] ?? DistributionAgreement::generateAgreementNumber();

        return DistributionAgreement::create([
            'content_partner_id' => $partner->id,
            'agreement_number' => $agreementNumber,
            'title' => $data['title'],
            'agreement_type' => $data['agreement_type'] ?? 'revenue_share',
            'exclusivity_type' => $data['exclusivity_type'] ?? 'non_exclusive',
            'exclusive_window_days' => (int) ($data['exclusive_window_days'] ?? 0),
            'financial_terms' => $data['financial_terms'] ?? null,
            'contract_document_url' => $data['contract_document_url'] ?? null,
            'start_date' => $data['start_date'] ?? now()->toDateString(),
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'terms_summary' => $data['terms_summary'] ?? null,
        ]);
    }

    /**
     * Activate a distribution agreement and mirror permissions into the Content Rights ledger.
     */
    public function activateAgreement(DistributionAgreement $agreement, array $linkOptions = []): DistributionAgreement
    {
        $partner = $agreement->partner;

        // Create or update linked ContentRight record in rights ledger
        $right = ContentRight::create([
            'channel_id' => $linkOptions['channel_id'] ?? null,
            'video_id' => $linkOptions['video_id'] ?? null,
            'rights_holder_name' => $partner->name,
            'contract_reference' => $agreement->agreement_number,
            'classification' => 'direct_licence',
            'licence_name' => $agreement->title,
            'licensing_agreement_text' => $agreement->terms_summary,
            'commercial_terms' => $agreement->financial_terms,
            'is_exclusive' => $agreement->exclusivity_type !== 'non_exclusive',
            'status' => 'embedding_approved',
            'allow_directory_listing' => true,
            'allow_web_embedding' => true,
            'allow_direct_hls' => true,
            'allow_monetisation' => in_array($agreement->agreement_type, ['revenue_share', 'flat_fee', 'hybrid']),
            'permitted_territories' => $linkOptions['permitted_territories'] ?? ['GLOBAL'],
            'blocked_territories' => $linkOptions['blocked_territories'] ?? [],
            'effective_from' => $agreement->start_date,
            'expires_at' => $agreement->end_date,
            'auto_delist_on_expiry' => true,
            'reviewed_at' => now(),
        ]);

        $agreement->update([
            'status' => 'active',
            'signed_at' => now(),
            'content_right_id' => $right->id,
        ]);

        // If partner stage was agreement_signed, promote to active
        if (in_array($partner->pipeline_stage, ['agreement_signed', 'content_received', 'negotiation'])) {
            $partner->advanceStage('active');
        }

        return $agreement;
    }

    /**
     * Compute 360-degree partner performance metrics.
     */
    public function getPartnerPerformanceMetrics(ContentPartner $partner): array
    {
        $deliveredChannels = $partner->channels()->count();
        $deliveredVideos = $partner->videos()->count();
        $activeAgreements = $partner->agreements()->where('status', 'active')->count();

        $activeVideos = $partner->videos()
            ->where('availability_status', 'approved')
            ->count();

        $totalRuntimeSeconds = (int) $partner->videos()->sum('duration_seconds');
        $totalRuntimeMinutes = (int) round($totalRuntimeSeconds / 60);

        return [
            'total_channels' => $deliveredChannels,
            'channels_count' => $deliveredChannels,
            'total_videos' => $deliveredVideos,
            'videos_count' => $deliveredVideos,
            'active_videos' => $activeVideos,
            'active_agreements' => $activeAgreements,
            'active_agreements_count' => $activeAgreements,
            'total_runtime_minutes' => $totalRuntimeMinutes,
            'pipeline_stage' => $partner->pipeline_stage,
            'is_active' => $partner->isActive(),
        ];
    }
}
