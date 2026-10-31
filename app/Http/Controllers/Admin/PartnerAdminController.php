<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPartner;
use App\Models\DistributionAgreement;
use App\Models\User;
use App\Services\Zonita\ContentAcquisitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerAdminController extends Controller
{
    public function __construct(
        protected ContentAcquisitionService $acquisitionService
    ) {}

    /**
     * Display the Partner CRM and acquisition pipeline.
     */
    public function index(Request $request): View
    {
        $stage = $request->query('stage');
        $type = $request->query('type');
        $search = $request->query('search');

        $query = ContentPartner::with(['agreements', 'assignedAgent'])
            ->withCount(['channels', 'videos'])
            ->latest();

        if ($stage) {
            $query->where('pipeline_stage', $stage);
        }

        if ($type) {
            $query->where('partner_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        $partners = $query->paginate(20)->withQueryString();

        $metrics = [
            'total_partners' => ContentPartner::count(),
            'prospects' => ContentPartner::prospects()->count(),
            'active_partners' => ContentPartner::active()->count(),
            'agreements_count' => DistributionAgreement::count(),
            'active_agreements' => DistributionAgreement::active()->count(),
        ];

        return view('admin.partners.index', compact(
            'partners',
            'stage',
            'type',
            'search',
            'metrics'
        ));
    }

    /**
     * Show form to onboard a new content partner.
     */
    public function create(): View
    {
        $agents = User::whereIn('role', ['content_manager', 'super_admin'])->get();

        return view('admin.partners.create', compact('agents'));
    }

    /**
     * Store new prospective partner.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'partner_type' => 'required|string|in:studio,tv_network,religious_broadcaster,sports_league,independent_filmmaker,distributor',
            'pipeline_stage' => 'required|string|in:prospect,contact_made,negotiation,agreement_signed,content_received,active,churned',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:1000',
            'country_code' => 'required|string|size:3',
            'assigned_agent_id' => 'nullable|exists:users,id',
            'lead_source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $partner = $this->acquisitionService->createPartner($validated, $request->user());

        return redirect()->route('admin.partners.show', $partner->id)
            ->with('success', "Partner '{$partner->name}' onboarded into pipeline at stage '{$partner->pipeline_stage}'.");
    }

    /**
     * 360-degree partner profile and agreement workbench.
     */
    public function show(int $id): View
    {
        $partner = ContentPartner::with([
            'agreements.contentRight',
            'assignedAgent',
            'channels.categories',
            'videos',
        ])->findOrFail($id);

        $metrics = $this->acquisitionService->getPartnerPerformanceMetrics($partner);

        return view('admin.partners.show', compact('partner', 'metrics'));
    }

    /**
     * Show partner edit form.
     */
    public function edit(int $id): View
    {
        $partner = ContentPartner::findOrFail($id);
        $agents = User::whereIn('role', ['content_manager', 'super_admin'])->get();

        return view('admin.partners.edit', compact('partner', 'agents'));
    }

    /**
     * Update partner profile.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $partner = ContentPartner::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'partner_type' => 'required|string|in:studio,tv_network,religious_broadcaster,sports_league,independent_filmmaker,distributor',
            'pipeline_stage' => 'nullable|string|in:prospect,contact_made,negotiation,agreement_signed,content_received,active,churned',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:1000',
            'country_code' => 'required|string|size:3',
            'assigned_agent_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $partner->update($validated);

        return redirect()->route('admin.partners.show', $partner->id)
            ->with('success', 'Partner profile updated successfully.');
    }

    /**
     * Fast pipeline stage transition.
     */
    public function updateStage(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'pipeline_stage' => 'required|string|in:prospect,contact_made,negotiation,agreement_signed,content_received,active,churned',
            'stage_notes' => 'nullable|string|max:1000',
        ]);

        $partner = ContentPartner::findOrFail($id);
        $this->acquisitionService->updatePipelineStage($partner, $validated['pipeline_stage'], $validated['stage_notes'] ?? null);

        return back()->with('success', "Partner advanced to stage '{$validated['pipeline_stage']}'.");
    }

    /**
     * Register and optionally activate a distribution agreement.
     */
    public function storeAgreement(Request $request, int $id): RedirectResponse
    {
        $partner = ContentPartner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'agreement_type' => 'required|string|in:revenue_share,flat_fee,promotional_exchange,royalty_free,hybrid',
            'exclusivity_type' => 'required|string|in:non_exclusive,exclusive_window,fully_exclusive',
            'exclusive_window_days' => 'nullable|integer|min:0',
            'rev_share_percentage' => 'nullable|numeric|min:0|max:100',
            'flat_fee_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'contract_document_url' => 'nullable|url|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'terms_summary' => 'nullable|string',
            'auto_activate' => 'boolean',
        ]);

        $financialTerms = [];
        if (! empty($validated['rev_share_percentage'])) {
            $financialTerms['revenue_share_percentage'] = (float) $validated['rev_share_percentage'];
        }
        if (! empty($validated['flat_fee_amount'])) {
            $financialTerms['flat_fee_amount'] = (float) $validated['flat_fee_amount'];
            $financialTerms['currency'] = $validated['currency'] ?? 'USD';
        }

        $agreement = $this->acquisitionService->createAgreement($partner, [
            'title' => $validated['title'],
            'agreement_type' => $validated['agreement_type'],
            'exclusivity_type' => $validated['exclusivity_type'],
            'exclusive_window_days' => $validated['exclusive_window_days'] ?? 0,
            'financial_terms' => $financialTerms ?: null,
            'contract_document_url' => $validated['contract_document_url'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'terms_summary' => $validated['terms_summary'] ?? null,
            'status' => $request->boolean('auto_activate') ? 'active' : 'draft',
        ]);

        if ($request->boolean('auto_activate')) {
            $this->acquisitionService->activateAgreement($agreement);
        }

        return redirect()->route('admin.partners.show', $partner->id)
            ->with('success', "Distribution agreement '{$agreement->title}' registered successfully.");
    }
}
