<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelStream;
use App\Models\ContentRight;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelAdminController extends Controller
{
    public function __construct(
        protected CloudinaryService $cloudinary
    ) {}

    public function index(Request $request)
    {
        $channels = Channel::with(['categories', 'latestRights', 'primaryStream'])
            ->latest()
            ->paginate(20);

        return view('admin.channels.index', compact('channels'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.channels.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'broadcaster_name' => 'nullable|string|max:150',
            'broadcaster_website' => 'nullable|url|max:255',
            'official_destination_url' => 'required|url|max:500',
            'country_code' => 'required|string|size:3',
            'language_code' => 'required|string|max:10',
            'playback_mode' => 'required|string|in:direct_stream,embed_player,external_destination,zoniva_original',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
            'categories' => 'array',
            'stream_url' => 'nullable|url',
            'embed_code' => 'nullable|string',
            'rights_holder_name' => 'required|string|max:200',
            'rights_status' => 'required|string|in:pending_review,directory_approved,embedding_approved,redistribution_approved,restricted,expired,rejected',
        ]);

        $channel = Channel::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.rand(100, 999),
            'tagline' => $validated['tagline'],
            'description' => $validated['description'],
            'broadcaster_name' => $validated['broadcaster_name'],
            'broadcaster_website' => $validated['broadcaster_website'],
            'official_destination_url' => $validated['official_destination_url'],
            'country_code' => strtoupper($validated['country_code']),
            'language_code' => strtolower($validated['language_code']),
            'playback_mode' => $validated['playback_mode'],
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'is_published' => $request->boolean('is_published', false),
        ]);

        if (! empty($validated['categories'])) {
            $channel->categories()->sync($validated['categories']);
        }

        // Create stream endpoint if provided
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

        // Create content right record
        ContentRight::create([
            'channel_id' => $channel->id,
            'rights_holder_name' => $validated['rights_holder_name'],
            'status' => $validated['rights_status'],
            'allow_directory_listing' => in_array($validated['rights_status'], ['directory_approved', 'embedding_approved', 'redistribution_approved']),
            'allow_web_embedding' => in_array($validated['rights_status'], ['embedding_approved', 'redistribution_approved']),
            'allow_direct_hls' => $validated['rights_status'] === 'redistribution_approved',
            'effective_from' => now(),
            'reviewed_by_user_id' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.channels.index')->with('success', 'Channel created successfully.');
    }

    public function edit(Channel $channel)
    {
        $categories = Category::orderBy('name')->get();
        $channel->load(['categories', 'primaryStream', 'latestRights']);

        return view('admin.channels.edit', compact('channel', 'categories'));
    }

    public function update(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'broadcaster_name' => 'nullable|string|max:150',
            'broadcaster_website' => 'nullable|url|max:255',
            'official_destination_url' => 'required|url|max:500',
            'country_code' => 'required|string|size:3',
            'language_code' => 'required|string|max:10',
            'playback_mode' => 'required|string|in:direct_stream,embed_player,external_destination,zoniva_original',
            'categories' => 'array',
        ]);

        $channel->update([
            'name' => $validated['name'],
            'tagline' => $validated['tagline'],
            'description' => $validated['description'],
            'broadcaster_name' => $validated['broadcaster_name'],
            'broadcaster_website' => $validated['broadcaster_website'],
            'official_destination_url' => $validated['official_destination_url'],
            'country_code' => strtoupper($validated['country_code']),
            'language_code' => strtolower($validated['language_code']),
            'playback_mode' => $validated['playback_mode'],
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'is_published' => $request->boolean('is_published'),
        ]);

        if (isset($validated['categories'])) {
            $channel->categories()->sync($validated['categories']);
        }

        return redirect()->route('admin.channels.index')->with('success', 'Channel updated successfully.');
    }
}
