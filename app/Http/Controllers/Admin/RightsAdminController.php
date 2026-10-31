<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ContentRight;
use App\Models\Movie;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RightsAdminController extends Controller
{
    /**
     * Display the comprehensive Rights & Licensing Ledger.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $classification = $request->query('classification');
        $search = $request->query('search');
        $expiringSoon = $request->boolean('expiring_soon');

        $query = ContentRight::with(['channel', 'video', 'movie', 'series', 'reviewer'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($classification) {
            $query->where('classification', $classification);
        }

        if ($expiringSoon) {
            $query->expiringSoon(7);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rights_holder_name', 'like', "%{$search}%")
                    ->orWhere('contract_reference', 'like', "%{$search}%")
                    ->orWhere('licence_name', 'like', "%{$search}%");
            });
        }

        $rights = $query->paginate(25)->withQueryString();

        $metrics = [
            'total_agreements' => ContentRight::count(),
            'active_rights' => ContentRight::active()->count(),
            'pending_review' => ContentRight::where('status', 'pending_review')->count(),
            'expiring_soon' => ContentRight::expiringSoon(7)->count(),
            'expired' => ContentRight::expired()->count(),
        ];

        $channels = Channel::orderBy('name')->get();
        $videos = Video::latest()->take(50)->get();
        $movies = Movie::latest()->take(50)->get();

        return view('admin.rights.index', compact(
            'rights',
            'status',
            'classification',
            'search',
            'expiringSoon',
            'metrics',
            'channels',
            'videos',
            'movies'
        ));
    }

    /**
     * Register a new content rights agreement in the ledger.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rights_holder_name' => 'required|string|max:200',
            'contract_reference' => 'nullable|string|max:100',
            'classification' => 'required|string|in:public_domain,creative_commons,authorized_embed,direct_licence,owned_original,under_review,restricted,expired,rejected',
            'licence_name' => 'nullable|string|max:255',
            'channel_id' => 'nullable|exists:channels,id',
            'video_id' => 'nullable|exists:videos,id',
            'movie_id' => 'nullable|exists:movies,id',
            'status' => 'required|string|in:pending_review,directory_approved,embedding_approved,redistribution_approved,restricted,expired,rejected',
            'effective_from' => 'required|date',
            'expires_at' => 'nullable|date|after_or_equal:effective_from',
            'permitted_territories' => 'nullable|string', // comma-separated e.g. "NGA, GHA, GLOBAL"
            'blocked_territories' => 'nullable|string',
            'licensing_agreement_text' => 'nullable|string',
            'attribution_requirements' => 'nullable|string',
            'allow_directory_listing' => 'boolean',
            'allow_web_embedding' => 'boolean',
            'allow_direct_hls' => 'boolean',
            'allow_monetisation' => 'boolean',
            'is_exclusive' => 'boolean',
            'auto_delist_on_expiry' => 'boolean',
        ]);

        $permitted = null;
        if (! empty($validated['permitted_territories'])) {
            $permitted = array_values(array_filter(array_map('trim', explode(',', $validated['permitted_territories']))));
        }

        $blocked = null;
        if (! empty($validated['blocked_territories'])) {
            $blocked = array_values(array_filter(array_map('trim', explode(',', $validated['blocked_territories']))));
        }

        $right = ContentRight::create([
            'channel_id' => $validated['channel_id'] ?? null,
            'video_id' => $validated['video_id'] ?? null,
            'movie_id' => $validated['movie_id'] ?? null,
            'rights_holder_name' => $validated['rights_holder_name'],
            'contract_reference' => $validated['contract_reference'] ?? null,
            'classification' => $validated['classification'],
            'licence_name' => $validated['licence_name'] ?? null,
            'licensing_agreement_text' => $validated['licensing_agreement_text'] ?? null,
            'attribution_requirements' => $validated['attribution_requirements'] ?? null,
            'status' => $validated['status'],
            'effective_from' => $validated['effective_from'],
            'expires_at' => $validated['expires_at'] ?? null,
            'permitted_territories' => $permitted,
            'blocked_territories' => $blocked,
            'allow_directory_listing' => $request->boolean('allow_directory_listing'),
            'allow_web_embedding' => $request->boolean('allow_web_embedding'),
            'allow_direct_hls' => $request->boolean('allow_direct_hls'),
            'allow_monetisation' => $request->boolean('allow_monetisation'),
            'is_exclusive' => $request->boolean('is_exclusive'),
            'auto_delist_on_expiry' => $request->boolean('auto_delist_on_expiry', true),
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.rights.index')
            ->with('success', "Rights agreement for '{$right->rights_holder_name}' successfully added to the ledger.");
    }

    /**
     * Update the status and terms of an existing rights agreement.
     */
    public function updateStatus(Request $request, ContentRight $right): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending_review,directory_approved,embedding_approved,redistribution_approved,restricted,expired,rejected',
            'classification' => 'nullable|string|in:public_domain,creative_commons,authorized_embed,direct_licence,owned_original,under_review,restricted,expired,rejected',
            'reviewer_notes' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'allow_directory_listing' => 'boolean',
            'allow_web_embedding' => 'boolean',
            'allow_direct_hls' => 'boolean',
            'allow_monetisation' => 'boolean',
            'auto_delist_on_expiry' => 'boolean',
        ]);

        $right->update([
            'status' => $validated['status'],
            'classification' => $validated['classification'] ?? $right->classification,
            'reviewer_notes' => $validated['reviewer_notes'] ?? $right->reviewer_notes,
            'expires_at' => array_key_exists('expires_at', $validated) ? $validated['expires_at'] : $right->expires_at,
            'allow_directory_listing' => $request->boolean('allow_directory_listing'),
            'allow_web_embedding' => $request->boolean('allow_web_embedding'),
            'allow_direct_hls' => $request->boolean('allow_direct_hls'),
            'allow_monetisation' => $request->boolean('allow_monetisation'),
            'auto_delist_on_expiry' => $request->boolean('auto_delist_on_expiry', $right->auto_delist_on_expiry),
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Rights status and permissions updated successfully.');
    }
}
