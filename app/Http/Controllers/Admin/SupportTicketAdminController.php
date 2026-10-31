<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ProblemReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketAdminController extends Controller
{
    /**
     * Display a listing of community problem reports and support tickets.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'all');
        $issueType = $request->input('issue_type');
        $channelId = $request->input('channel_id');
        $search = $request->input('search');

        $query = ProblemReport::with(['channel', 'user'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($issueType) {
            $query->where('issue_type', $issueType);
        }

        if ($channelId) {
            $query->where('channel_id', $channelId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $reports = $query->paginate(20)->withQueryString();

        $counts = [
            'total' => ProblemReport::count(),
            'new' => ProblemReport::where('status', 'new')->count(),
            'investigating' => ProblemReport::where('status', 'investigating')->count(),
            'resolved' => ProblemReport::where('status', 'resolved')->count(),
            'dismissed' => ProblemReport::where('status', 'dismissed')->count(),
        ];

        $channels = Channel::orderBy('name')->get(['id', 'name', 'channel_number']);

        return view('admin.support.index', compact('reports', 'counts', 'channels', 'status', 'issueType', 'channelId', 'search'));
    }

    /**
     * Show detailed problem report and triage workspace.
     */
    public function show(ProblemReport $report): View
    {
        $report->load(['channel.latestRights', 'channel.primaryStream', 'user']);

        return view('admin.support.show', compact('report'));
    }

    /**
     * Update support ticket status and resolution notes.
     */
    public function update(Request $request, ProblemReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,investigating,resolved,dismissed'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $report->update([
            'status' => $validated['status'],
            'description' => $validated['admin_notes']
                ? $report->description."\n\n[Admin Update - ".now()->format('Y-m-d H:i').']: '.$validated['admin_notes']
                : $report->description,
        ]);

        return redirect()->route('admin.support.show', $report)
            ->with('success', "Ticket #{$report->id} status updated to {$validated['status']}.");
    }

    /**
     * Delete or dismiss a problem report.
     */
    public function destroy(ProblemReport $report): RedirectResponse
    {
        $report->delete();

        return redirect()->route('admin.support.index')
            ->with('success', "Problem report #{$report->id} removed.");
    }
}
