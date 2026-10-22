<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentRight;
use Illuminate\Http\Request;

class RightsAdminController extends Controller
{
    public function index()
    {
        $rights = ContentRight::with(['channel', 'reviewer'])
            ->latest()
            ->paginate(25);

        return view('admin.rights.index', compact('rights'));
    }

    public function updateStatus(Request $request, ContentRight $right)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending_review,directory_approved,embedding_approved,redistribution_approved,restricted,expired,rejected',
            'reviewer_notes' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'allow_directory_listing' => 'boolean',
            'allow_web_embedding' => 'boolean',
            'allow_direct_hls' => 'boolean',
        ]);

        $right->update([
            'status' => $validated['status'],
            'reviewer_notes' => $validated['reviewer_notes'],
            'expires_at' => $validated['expires_at'],
            'allow_directory_listing' => $request->boolean('allow_directory_listing'),
            'allow_web_embedding' => $request->boolean('allow_web_embedding'),
            'allow_direct_hls' => $request->boolean('allow_direct_hls'),
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Rights status updated successfully.');
    }
}
