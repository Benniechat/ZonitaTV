<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentComplaint;
use App\Models\ScheduleEntry;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ComplaintAdminController extends Controller
{
    /**
     * Display a listing of content complaints.
     */
    public function index(Request $request): View
    {
        $query = ContentComplaint::with(['video', 'channel', 'resolver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('complaint_type', $request->input('type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('complainant_name', 'like', "%{$search}%")
                    ->orWhere('complainant_email', 'like', "%{$search}%")
                    ->orWhere('content_reference', 'like', "%{$search}%");
            });
        }

        $complaints = $query->paginate(15)->withQueryString();

        $counts = [
            'total' => ContentComplaint::count(),
            'pending' => ContentComplaint::where('status', 'pending')->count(),
            'under_investigation' => ContentComplaint::where('status', 'under_investigation')->count(),
            'content_disabled' => ContentComplaint::where('status', 'content_disabled')->count(),
            'resolved' => ContentComplaint::where('status', 'resolved')->count(),
        ];

        return view('admin.complaints.index', compact('complaints', 'counts'));
    }

    /**
     * Show detailed complaint information and action controls.
     */
    public function show(ContentComplaint $complaint): View
    {
        $complaint->load(['video', 'channel', 'resolver']);

        return view('admin.complaints.show', compact('complaint'));
    }

    /**
     * Update the status and internal notes of a complaint.
     */
    public function updateStatus(Request $request, ContentComplaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,under_investigation,content_disabled,content_removed,rejected,resolved'],
            'action_taken' => ['nullable', 'string', 'max:500'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $complaint->update([
            'status' => $validated['status'],
            'action_taken' => $validated['action_taken'] ?? $complaint->action_taken,
            'resolution_notes' => $validated['resolution_notes'] ?? $complaint->resolution_notes,
            'resolved_by_user_id' => Auth::id(),
            'resolved_at' => in_array($validated['status'], ['resolved', 'rejected', 'content_disabled', 'content_removed']) ? now() : null,
        ]);

        Log::info("Content complaint {$complaint->reference_number} updated to {$validated['status']} by user ".Auth::id());

        return redirect()->route('admin.complaints.show', $complaint)
            ->with('success', "Complaint {$complaint->reference_number} status updated to {$validated['status']}.");
    }

    /**
     * Takedown and disable the reported content from scheduling and discovery.
     */
    public function takedown(Request $request, ContentComplaint $complaint): RedirectResponse
    {
        $request->validate([
            'takedown_action' => ['required', 'string', 'in:disable_video,remove_video,mark_channel_review'],
            'admin_reason' => ['required', 'string', 'max:1000'],
        ]);

        $action = $request->input('takedown_action');
        $reason = $request->input('admin_reason');
        $affectedItems = 0;

        // 1. If complaint has an associated Video or identifiable content reference
        $video = $complaint->video;
        if (! $video && ! empty($complaint->content_reference)) {
            $video = Video::where('youtube_id', trim($complaint->content_reference))->first();
        }

        if ($video) {
            $newStatus = $action === 'remove_video' ? 'removed' : 'disabled';

            $video->update([
                'availability_status' => $newStatus,
                'is_embeddable' => false,
                'confidence_score' => 0,
            ]);

            // Cancel any future unplayed scheduled entries for this video
            $cancelled = ScheduleEntry::where('video_id', $video->id)
                ->where('start_time', '>=', now()->subMinutes(5))
                ->update(['status' => 'cancelled']);

            $affectedItems += $cancelled;

            Log::warning("Legal Takedown: Video {$video->youtube_id} marked {$newStatus}. Cancelled {$cancelled} future schedule entries.");
        }

        // 2. Update complaint record
        $complaint->update([
            'status' => 'content_disabled',
            'action_taken' => "Automated Takedown: {$action}. Reason: {$reason}",
            'resolution_notes' => ($complaint->resolution_notes ? $complaint->resolution_notes."\n\n" : '').
                "[Takedown Executed ".now()->toIso8601String()." by ".Auth::user()->name."]: {$reason}",
            'resolved_by_user_id' => Auth::id(),
            'resolved_at' => now(),
        ]);

        return redirect()->route('admin.complaints.show', $complaint)
            ->with('success', "Content associated with complaint {$complaint->reference_number} has been disabled and purged from active broadcast schedules.");
    }
}
