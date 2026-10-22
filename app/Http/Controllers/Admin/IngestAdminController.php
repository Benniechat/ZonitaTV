<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Programme;
use App\Services\EpgSyncService;
use App\Services\IptvPlaylistIngestService;
use Illuminate\Http\Request;
use Throwable;

class IngestAdminController extends Controller
{
    protected IptvPlaylistIngestService $ingestService;

    protected EpgSyncService $epgService;

    public function __construct(
        IptvPlaylistIngestService $ingestService,
        EpgSyncService $epgService
    ) {
        $this->ingestService = $ingestService;
        $this->epgService = $epgService;
    }

    public function index()
    {
        $metrics = [
            'total_channels' => Channel::count(),
            'channels_with_tvg_id' => Channel::whereNotNull('tvg_id')->count(),
            'total_programmes' => Programme::count(),
            'live_now_count' => Programme::where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->count(),
            'latest_programmes' => Programme::with('channel')->latest('start_time')->take(8)->get(),
            'recent_channels' => Channel::with('categories')->latest()->take(8)->get(),
        ];

        return view('admin.ingest.index', compact('metrics'));
    }

    public function runPlaylistIngest(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'country' => 'nullable|string|max:3',
            'limit' => 'required|integer|min:1|max:250',
        ]);

        try {
            $report = $this->ingestService->ingestFromUrl(
                $validated['url'],
                $validated['country'] ? strtoupper(trim($validated['country'])) : null,
                $validated['limit']
            );

            return back()->with('success', "IPTV Ingest Complete: {$report['imported']} new channels imported, {$report['updated']} updated, {$report['skipped']} skipped.");
        } catch (Throwable $e) {
            return back()->with('error', 'Playlist Ingest Failed: '.$e->getMessage());
        }
    }

    public function runEpgSync(Request $request)
    {
        $validated = $request->validate([
            'xmltv_url' => 'nullable|url',
            'action_type' => 'required|string|in:remote_xmltv,generate_schedules',
        ]);

        try {
            if ($validated['action_type'] === 'remote_xmltv' && ! empty($validated['xmltv_url'])) {
                $result = $this->epgService->syncFromUrl($validated['xmltv_url']);

                return back()->with('success', "EPG XMLTV Synchronization Finished: {$result['synced']} programmes parsed and mapped.");
            }

            $count = $this->epgService->generateDynamicSchedulesForAllChannels();

            return back()->with('success', "EPG Schedule Engine Finished: Generated {$count} continuous programme slots across active channels.");
        } catch (Throwable $e) {
            return back()->with('error', 'EPG Sync Failed: '.$e->getMessage());
        }
    }
}
