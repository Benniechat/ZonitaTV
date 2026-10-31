<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelStream;
use App\Models\ContentPartner;
use App\Models\ContentRight;
use App\Services\CloudinaryService;
use App\Services\Zonita\VirtualChannelManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChannelAdminController extends Controller
{
    public function __construct(
        protected CloudinaryService $cloudinary,
        protected VirtualChannelManagerService $virtualChannelService
    ) {}

    /**
     * Display a listing of channels with virtual/linear filtering.
     */
    public function index(Request $request): View
    {
        $query = Channel::with(['categories', 'latestRights', 'primaryStream', 'sourceRule', 'contentPartner'])
            ->latest('id');

        if ($request->filled('search')) {
            $term = '%'.trim($request->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('tagline', 'like', $term)
                    ->orWhere('channel_number', 'like', $term)
                    ->orWhere('broadcaster_name', 'like', $term);
            });
        }

        if ($request->filter === 'virtual') {
            $query->virtual();
        } elseif ($request->filter === 'published') {
            $query->where('is_published', true);
        } elseif ($request->filter === 'offline') {
            $query->where('is_published', false);
        }

        $channels = $query->paginate(20)->withQueryString();

        $stats = [
            'total_channels' => Channel::count(),
            'virtual_channels' => Channel::virtual()->count(),
            'on_air_channels' => Channel::where('is_published', true)->where('is_active', true)->count(),
            'partner_channels' => Channel::whereNotNull('content_partner_id')->count(),
        ];

        return view('admin.channels.index', compact('channels', 'stats'));
    }

    /**
     * Show channel creation form.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $partners = ContentPartner::orderBy('name')->get();

        return view('admin.channels.create', compact('categories', 'partners'));
    }

    /**
     * Store a newly created channel (standard or 24/7 virtual).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'channel_number' => 'nullable|string|max:10',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo_path' => 'nullable|string|max:500',
            'banner_path' => 'nullable|string|max:500',
            'brand_color' => 'nullable|string|max:20',
            'watermark_logo_path' => 'nullable|string|max:500',
            'logo_file' => 'nullable|image|max:10240',
            'banner_file' => 'nullable|image|max:10240',
            'watermark_file' => 'nullable|image|max:10240',
            'broadcaster_name' => 'nullable|string|max:150',
            'broadcaster_website' => 'nullable|url|max:255',
            'content_partner_id' => 'nullable|exists:content_partners,id',
            'official_destination_url' => 'required|url|max:500',
            'country_code' => 'required|string|size:3',
            'language_code' => 'required|string|max:10',
            'playback_mode' => 'required|string|in:direct_stream,embed_player,external_destination,zoniva_original',
            'is_virtual' => 'boolean',
            'curation_type' => 'nullable|string|max:50',
            'fallback_video_id' => 'nullable|string|max:32',
            'station_ident_video_id' => 'nullable|string|max:32',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
            'categories' => 'array',
            'stream_url' => 'nullable|url',
            'embed_code' => 'nullable|string',
            'rights_holder_name' => 'nullable|string|max:200',
            'rights_status' => 'nullable|string|in:pending_review,directory_approved,embedding_approved,redistribution_approved,restricted,expired,rejected',
            // Playout Priority & Rules (Virtual)
            'search_keywords' => 'nullable|string',
            'excluded_keywords' => 'nullable|string',
            'approved_publishers' => 'nullable|string',
            'preferred_formats' => 'nullable|array',
            'min_duration_minutes' => 'nullable|integer|min:1',
            'max_duration_minutes' => 'nullable|integer|min:2',
            'repeat_cooldown_hours' => 'nullable|integer|min:1',
            'min_reserve_count' => 'nullable|integer|min:1',
            'scheduling_mode' => 'nullable|string|in:continuous,blocks,hybrid',
            'priority_level' => 'nullable|string|in:premier_heavy,balanced,deep_catalog',
            'timezone' => 'nullable|string',
        ]);

        // Upload media assets if provided
        $logoPath = $validated['logo_path'] ?? null;
        if ($request->hasFile('logo_file')) {
            $uploadRes = $this->cloudinary->upload($request->file('logo_file'), 'zonita/logos');
            if ($uploadRes['success'] ?? false) {
                $logoPath = $uploadRes['secure_url'];
            }
        }

        $bannerPath = $validated['banner_path'] ?? null;
        if ($request->hasFile('banner_file')) {
            $uploadRes = $this->cloudinary->upload($request->file('banner_file'), 'zonita/banners');
            if ($uploadRes['success'] ?? false) {
                $bannerPath = $uploadRes['secure_url'];
            }
        }

        $watermarkPath = $validated['watermark_logo_path'] ?? null;
        if ($request->hasFile('watermark_file')) {
            $uploadRes = $this->cloudinary->upload($request->file('watermark_file'), 'zonita/watermarks');
            if ($uploadRes['success'] ?? false) {
                $watermarkPath = $uploadRes['secure_url'];
            }
        }

        $validated['logo_path'] = $logoPath;
        $validated['banner_path'] = $bannerPath;
        $validated['watermark_logo_path'] = $watermarkPath;

        if ($request->boolean('is_virtual') || $validated['playback_mode'] === 'zoniva_original') {
            $channel = $this->virtualChannelService->createVirtualChannel($validated, $request->user());
        } else {
            $channel = Channel::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']).'-'.rand(100, 999),
                'channel_number' => $validated['channel_number'] ?? null,
                'tagline' => $validated['tagline'] ?? null,
                'description' => $validated['description'] ?? null,
                'logo_path' => $logoPath,
                'banner_path' => $bannerPath,
                'brand_color' => $validated['brand_color'] ?? '#E50914',
                'watermark_logo_path' => $watermarkPath,
                'broadcaster_name' => $validated['broadcaster_name'] ?? null,
                'broadcaster_website' => $validated['broadcaster_website'] ?? null,
                'content_partner_id' => $validated['content_partner_id'] ?? null,
                'official_destination_url' => $validated['official_destination_url'],
                'country_code' => strtoupper($validated['country_code']),
                'language_code' => strtolower($validated['language_code']),
                'playback_mode' => $validated['playback_mode'],
                'is_virtual' => false,
                'fallback_video_id' => $validated['fallback_video_id'] ?? null,
                'station_ident_video_id' => $validated['station_ident_video_id'] ?? null,
                'is_featured' => $request->boolean('is_featured'),
                'is_active' => $request->boolean('is_active', true),
                'is_published' => $request->boolean('is_published', false),
            ]);

            if (! empty($validated['categories'])) {
                $channel->categories()->sync($validated['categories']);
            }

            if (! empty($validated['stream_url']) || ! empty($validated['embed_code'])) {
                ChannelStream::create([
                    'channel_id' => $channel->id,
                    'label' => 'Primary',
                    'stream_type' => $validated['playback_mode'] === 'direct_stream' ? 'hls_m3u8' : 'iframe_generic',
                    'stream_url' => $validated['stream_url'],
                    'embed_code' => $validated['embed_code'],
                    'is_active' => true,
                ]);
            }

            if (! empty($validated['rights_holder_name'])) {
                $rightsStatus = $validated['rights_status'] ?? 'pending_review';
                ContentRight::create([
                    'channel_id' => $channel->id,
                    'rights_holder_name' => $validated['rights_holder_name'],
                    'status' => $rightsStatus,
                    'allow_directory_listing' => in_array($rightsStatus, ['directory_approved', 'embedding_approved', 'redistribution_approved']),
                    'allow_web_embedding' => in_array($rightsStatus, ['embedding_approved', 'redistribution_approved']),
                    'allow_direct_hls' => $rightsStatus === 'redistribution_approved',
                    'effective_from' => now(),
                    'reviewed_by_user_id' => $request->user()?->id,
                    'reviewed_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.channels.show', $channel->id)
            ->with('success', "Channel '{$channel->name}' created successfully.");
    }

    /**
     * 360-degree channel profile, station branding preview, and live playout monitor.
     */
    public function show(Channel $channel): View
    {
        $channel->load([
            'categories',
            'contentPartner',
            'sourceRule',
            'primaryStream',
            'streams',
            'latestRights',
        ]);

        $playoutHealth = $this->virtualChannelService->getChannelPlayoutHealth($channel);

        return view('admin.channels.show', compact('channel', 'playoutHealth'));
    }

    /**
     * Show channel edit form.
     */
    public function edit(Channel $channel): View
    {
        $categories = Category::orderBy('name')->get();
        $partners = ContentPartner::orderBy('name')->get();
        $channel->load(['categories', 'primaryStream', 'latestRights', 'sourceRule', 'contentPartner']);

        return view('admin.channels.edit', compact('channel', 'categories', 'partners'));
    }

    /**
     * Update channel details, station branding, and playout rules.
     */
    public function update(Request $request, Channel $channel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'channel_number' => 'nullable|string|max:10',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo_path' => 'nullable|string|max:500',
            'banner_path' => 'nullable|string|max:500',
            'brand_color' => 'nullable|string|max:20',
            'watermark_logo_path' => 'nullable|string|max:500',
            'logo_file' => 'nullable|image|max:10240',
            'banner_file' => 'nullable|image|max:10240',
            'watermark_file' => 'nullable|image|max:10240',
            'broadcaster_name' => 'nullable|string|max:150',
            'broadcaster_website' => 'nullable|url|max:255',
            'content_partner_id' => 'nullable|exists:content_partners,id',
            'official_destination_url' => 'required|url|max:500',
            'country_code' => 'required|string|size:3',
            'language_code' => 'required|string|max:10',
            'playback_mode' => 'required|string|in:direct_stream,embed_player,external_destination,zoniva_original',
            'is_virtual' => 'boolean',
            'fallback_video_id' => 'nullable|string|max:32',
            'station_ident_video_id' => 'nullable|string|max:32',
            'categories' => 'array',
            // Playout Priority & Rules (Virtual)
            'search_keywords' => 'nullable|string',
            'excluded_keywords' => 'nullable|string',
            'approved_publishers' => 'nullable|string',
            'preferred_formats' => 'nullable|array',
            'min_duration_minutes' => 'nullable|integer|min:1',
            'max_duration_minutes' => 'nullable|integer|min:2',
            'repeat_cooldown_hours' => 'nullable|integer|min:1',
            'min_reserve_count' => 'nullable|integer|min:1',
            'scheduling_mode' => 'nullable|string|in:continuous,blocks,hybrid',
            'priority_level' => 'nullable|string|in:premier_heavy,balanced,deep_catalog',
            'timezone' => 'nullable|string',
        ]);

        $logoPath = $validated['logo_path'] ?? $channel->logo_path;
        if ($request->hasFile('logo_file')) {
            $uploadRes = $this->cloudinary->upload($request->file('logo_file'), 'zonita/logos');
            if ($uploadRes['success'] ?? false) {
                $logoPath = $uploadRes['secure_url'];
            }
        }

        $bannerPath = $validated['banner_path'] ?? $channel->banner_path;
        if ($request->hasFile('banner_file')) {
            $uploadRes = $this->cloudinary->upload($request->file('banner_file'), 'zonita/banners');
            if ($uploadRes['success'] ?? false) {
                $bannerPath = $uploadRes['secure_url'];
            }
        }

        $watermarkPath = $validated['watermark_logo_path'] ?? $channel->watermark_logo_path;
        if ($request->hasFile('watermark_file')) {
            $uploadRes = $this->cloudinary->upload($request->file('watermark_file'), 'zonita/watermarks');
            if ($uploadRes['success'] ?? false) {
                $watermarkPath = $uploadRes['secure_url'];
            }
        }

        $channel->update([
            'name' => $validated['name'],
            'channel_number' => $validated['channel_number'] ?? $channel->channel_number,
            'tagline' => $validated['tagline'] ?? null,
            'description' => $validated['description'] ?? null,
            'logo_path' => $logoPath,
            'banner_path' => $bannerPath,
            'brand_color' => $validated['brand_color'] ?? $channel->brand_color,
            'watermark_logo_path' => $watermarkPath,
            'broadcaster_name' => $validated['broadcaster_name'] ?? null,
            'broadcaster_website' => $validated['broadcaster_website'] ?? null,
            'content_partner_id' => $validated['content_partner_id'] ?? null,
            'official_destination_url' => $validated['official_destination_url'],
            'country_code' => strtoupper($validated['country_code']),
            'language_code' => strtolower($validated['language_code']),
            'playback_mode' => $validated['playback_mode'],
            'is_virtual' => $request->boolean('is_virtual', $channel->is_virtual),
            'fallback_video_id' => $validated['fallback_video_id'] ?? $channel->fallback_video_id,
            'station_ident_video_id' => $validated['station_ident_video_id'] ?? $channel->station_ident_video_id,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'is_published' => $request->boolean('is_published'),
        ]);

        if (isset($validated['categories'])) {
            $channel->categories()->sync($validated['categories']);
        }

        // Update playout priority rules if channel is virtual or rules are submitted
        if ($channel->isVirtual() || $request->filled('search_keywords')) {
            $this->virtualChannelService->updatePlayoutPriorityRules($channel, $validated);
        }

        return redirect()->route('admin.channels.show', $channel->id)
            ->with('success', "Channel '{$channel->name}' updated successfully.");
    }

    /**
     * Fast toggle for broadcast on-air status.
     */
    public function toggleStatus(Request $request, Channel $channel): RedirectResponse
    {
        $action = $request->input('action', 'toggle_publish');
        $this->virtualChannelService->toggleBroadcastStatus($channel, $action);

        $statusText = $channel->fresh()->is_published ? 'On-Air (Published)' : 'Off-Air Slate (Unpublished)';

        return redirect()->back()->with('success', "Broadcast status for [{$channel->name}] switched to {$statusText}.");
    }

    /**
     * Soft-delete a channel.
     */
    public function destroy(Channel $channel): RedirectResponse
    {
        $name = $channel->name;
        $channel->delete();

        return redirect()->route('admin.channels.index')
            ->with('success', "Channel '{$name}' was archived/soft-deleted successfully.");
    }
}
