<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Channel;
use App\Models\ContentProvider;
use App\Models\DiscoveryCandidate;
use App\Services\Zonita\ContentDiscoveryPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DiscoveryWorkbenchController extends Controller
{
    public function __construct(
        protected ContentDiscoveryPipelineService $pipeline
    ) {}

    /**
     * Display the discovery staging workbench.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'staged');
        $sourceType = $request->query('source_type');
        $providerId = $request->query('provider_id');
        $query = $request->query('q');

        $candidateQuery = DiscoveryCandidate::with(['provider', 'channel', 'category', 'reviewer'])
            ->latest();

        if ($status !== 'all') {
            $candidateQuery->where('intake_status', $status);
        }

        if ($sourceType) {
            $candidateQuery->where('source_type', $sourceType);
        }

        if ($providerId) {
            $candidateQuery->where('content_provider_id', $providerId);
        }

        if ($query) {
            $candidateQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('source_identifier', 'like', "%{$query}%")
                    ->orWhere('creator_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        $candidates = $candidateQuery->paginate(20)->withQueryString();

        $metrics = [
            'staged_count' => DiscoveryCandidate::where('intake_status', 'staged')->count(),
            'approved_count' => DiscoveryCandidate::where('intake_status', 'approved')->count(),
            'rejected_count' => DiscoveryCandidate::where('intake_status', 'rejected')->count(),
            'providers_count' => ContentProvider::count(),
        ];

        $providers = ContentProvider::orderBy('name')->get();
        $channels = Channel::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.discovery.index', compact(
            'candidates',
            'status',
            'sourceType',
            'providerId',
            'query',
            'metrics',
            'providers',
            'channels',
            'categories'
        ));
    }

    /**
     * Show form to manually stage a discovery candidate.
     */
    public function create(): View
    {
        $providers = ContentProvider::where('is_active', true)->orderBy('name')->get();
        $channels = Channel::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.discovery.create', compact('providers', 'channels', 'categories'));
    }

    /**
     * Ingest new prospective content candidate.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_url' => 'required|url|max:1000',
            'source_type' => 'required|string|in:youtube_video,youtube_playlist,internet_archive,direct_url,creator_submission',
            'title' => 'nullable|string|max:255',
            'creator_name' => 'nullable|string|max:255',
            'content_provider_id' => 'nullable|exists:content_providers,id',
            'channel_id' => 'nullable|exists:channels,id',
            'category_id' => 'nullable|exists:categories,id',
            'rights_basis' => 'required|string|max:50',
            'duration_seconds' => 'nullable|integer|min:0',
            'content_rating' => 'nullable|string|in:G,PG,13+,16+,18+',
        ]);

        $provider = ! empty($validated['content_provider_id'])
            ? ContentProvider::find($validated['content_provider_id'])
            : null;

        $result = $this->pipeline->ingestCandidate($validated, $provider);

        if ($result['status'] === 'duplicate') {
            return redirect()->route('admin.discovery.index')
                ->with('error', "Intake skipped: {$result['reason']}");
        }

        if ($result['status'] === 'rejected' || $result['status'] === 'error') {
            return back()->withInput()->with('error', "Intake rejected: {$result['reason']}");
        }

        return redirect()->route('admin.discovery.show', $result['candidate']->id)
            ->with('success', "Prospective content '{$result['candidate']->title}' staged successfully for review.");
    }

    /**
     * Inspect candidate details and review workbench.
     */
    public function show(int $id): View
    {
        $candidate = DiscoveryCandidate::with(['provider', 'channel', 'category', 'approvedVideo', 'reviewer'])
            ->findOrFail($id);

        $channels = Channel::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.discovery.show', compact('candidate', 'channels', 'categories'));
    }

    /**
     * Approve candidate and promote to active video pool.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $candidate = DiscoveryCandidate::findOrFail($id);

        $channelId = $request->input('channel_id') ?: $candidate->channel_id;
        $categoryId = $request->input('category_id') ?: $candidate->category_id;

        $channel = $channelId ? Channel::find($channelId) : null;
        $category = $categoryId ? Category::find($categoryId) : null;

        $video = $this->pipeline->promoteCandidate($candidate, $request->user(), $channel, $category);

        return redirect()->route('admin.discovery.index')
            ->with('success', "Candidate '{$candidate->title}' was approved and promoted to active broadcast catalogue (#{$video->id}).");
    }

    /**
     * Reject candidate with a documented reason.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $candidate = DiscoveryCandidate::findOrFail($id);

        $this->pipeline->rejectCandidate($candidate, $request->user(), $request->input('rejection_reason'));

        return redirect()->route('admin.discovery.index')
            ->with('success', "Candidate '{$candidate->title}' has been marked as rejected.");
    }

    /**
     * Content Providers directory.
     */
    public function providers(): View
    {
        $providers = ContentProvider::withCount(['candidates', 'candidates as approved_candidates_count' => function ($q) {
            $q->where('intake_status', 'approved');
        }])->latest()->paginate(20);

        return view('admin.discovery.providers', compact('providers'));
    }

    /**
     * Store new Content Provider.
     */
    public function storeProvider(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider_type' => 'required|string|in:youtube_channel,internet_archive,direct_creator,rss_feed,film_distributor,public_domain_repository',
            'website_url' => 'nullable|url|max:1000',
            'source_feed_url' => 'nullable|url|max:1000',
            'contact_email' => 'nullable|email|max:255',
            'contact_name' => 'nullable|string|max:255',
            'default_rights_basis' => 'required|string|max:50',
            'is_trusted' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $provider = ContentProvider::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.rand(100, 999),
            'provider_type' => $validated['provider_type'],
            'website_url' => $validated['website_url'] ?? null,
            'source_feed_url' => $validated['source_feed_url'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_name' => $validated['contact_name'] ?? null,
            'default_rights_basis' => $validated['default_rights_basis'],
            'is_trusted' => $request->boolean('is_trusted'),
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.discovery.providers')
            ->with('success', "Content provider '{$provider->name}' registered successfully.");
    }
}
