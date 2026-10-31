<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentCatalogueController extends Controller
{
    /**
     * Display a listing of catalogue items across videos, movies, and series.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type', 'videos');
        $query = $request->query('q');
        $status = $request->query('status');
        $categorySlug = $request->query('category');
        $showTrashed = $request->boolean('trashed');

        $categories = Category::orderBy('name')->get();

        $metrics = [
            'total_videos' => Video::count(),
            'total_movies' => Movie::count(),
            'total_series' => Series::count(),
            'total_episodes' => Episode::count(),
            'trashed_videos' => Video::onlyTrashed()->count(),
        ];

        if ($type === 'movies') {
            $movieQuery = Movie::with('video')->latest();

            if ($showTrashed) {
                $movieQuery->onlyTrashed();
            }

            if ($query) {
                $movieQuery->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('synopsis', 'like', "%{$query}%")
                        ->orWhere('genre', 'like', "%{$query}%");
                });
            }

            if ($status) {
                $movieQuery->where('verification_status', $status);
            }

            $items = $movieQuery->paginate(20)->withQueryString();
        } elseif ($type === 'series') {
            $seriesQuery = Series::with(['seasons.episodes', 'category'])->latest();

            if ($showTrashed) {
                $seriesQuery->onlyTrashed();
            }

            if ($query) {
                $seriesQuery->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('synopsis', 'like', "%{$query}%")
                        ->orWhere('genre', 'like', "%{$query}%");
                });
            }

            if ($status) {
                $seriesQuery->where('status', $status);
            }

            $items = $seriesQuery->paginate(20)->withQueryString();
        } else {
            // Default: videos
            $videoQuery = Video::with(['movies', 'episodes'])->latest();

            if ($showTrashed) {
                $videoQuery->onlyTrashed();
            }

            if ($query) {
                $videoQuery->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('youtube_id', 'like', "%{$query}%")
                        ->orWhere('channel_title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            }

            if ($status) {
                $videoQuery->where('availability_status', $status);
            }

            if ($categorySlug) {
                $videoQuery->where('category_slug', $categorySlug);
            }

            $items = $videoQuery->paginate(20)->withQueryString();
        }

        return view('admin.catalogue.index', compact(
            'items',
            'type',
            'query',
            'status',
            'categorySlug',
            'showTrashed',
            'categories',
            'metrics'
        ));
    }

    /**
     * Show the form for creating a new catalogue video entry.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.catalogue.create', compact('categories'));
    }

    /**
     * Store a newly created video in the catalogue.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'youtube_id' => 'required|string|max:32|unique:videos,youtube_id',
            'description' => 'nullable|string',
            'channel_title' => 'nullable|string|max:255',
            'thumbnail_url' => 'nullable|url|max:1000',
            'media_url' => 'nullable|url|max:1000',
            'duration_seconds' => 'required|integer|min:0',
            'aspect_ratio' => 'required|string|in:16:9,4:3,21:9',
            'resolution' => 'required|string|in:1080p,720p,4k,480p,360p',
            'content_rating' => 'required|string|in:G,PG,13+,16+,18+',
            'advisory_tags' => 'nullable|string', // comma-separated input
            'language_code' => 'required|string|max:10',
            'country_code' => 'required|string|size:3',
            'rights_basis' => 'required|string|max:50',
            'format_type' => 'required|string|in:feature,short,clip,stream,episode',
            'category_slug' => 'nullable|string|max:50',
            'is_embeddable' => 'boolean',
            'is_evergreen' => 'boolean',
            'availability_status' => 'required|string|in:discovered,approved,scheduled,restricted,disabled,removed',
        ]);

        $advisoryTags = null;
        if (! empty($validated['advisory_tags'])) {
            $advisoryTags = array_values(array_filter(array_map('trim', explode(',', $validated['advisory_tags']))));
        }

        $video = Video::create([
            'title' => $validated['title'],
            'youtube_id' => $validated['youtube_id'],
            'description' => $validated['description'] ?? null,
            'channel_title' => $validated['channel_title'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'media_url' => $validated['media_url'] ?? null,
            'duration_seconds' => $validated['duration_seconds'],
            'aspect_ratio' => $validated['aspect_ratio'],
            'resolution' => $validated['resolution'],
            'content_rating' => $validated['content_rating'],
            'advisory_tags' => $advisoryTags,
            'language_code' => strtolower($validated['language_code']),
            'country_code' => strtoupper($validated['country_code']),
            'rights_basis' => $validated['rights_basis'],
            'format_type' => $validated['format_type'],
            'category_slug' => $validated['category_slug'] ?? null,
            'is_embeddable' => $request->boolean('is_embeddable', true),
            'is_evergreen' => $request->boolean('is_evergreen', false),
            'availability_status' => $validated['availability_status'],
            'confidence_score' => 100,
            'last_verified_at' => now(),
        ]);

        return redirect()->route('admin.catalogue.show', $video->id)
            ->with('success', "Content asset '{$video->title}' was successfully registered in the catalogue.");
    }

    /**
     * Display the specified catalogue item.
     */
    public function show(int $id): View
    {
        $video = Video::withTrashed()
            ->with(['movies', 'episodes.season.series', 'verificationLogs', 'complaints'])
            ->findOrFail($id);

        return view('admin.catalogue.show', compact('video'));
    }

    /**
     * Show the form for editing the catalogue video.
     */
    public function edit(int $id): View
    {
        $video = Video::withTrashed()->findOrFail($id);
        $categories = Category::orderBy('name')->get();

        return view('admin.catalogue.edit', compact('video', 'categories'));
    }

    /**
     * Update the specified catalogue video.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $video = Video::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'channel_title' => 'nullable|string|max:255',
            'thumbnail_url' => 'nullable|url|max:1000',
            'media_url' => 'nullable|url|max:1000',
            'duration_seconds' => 'required|integer|min:0',
            'aspect_ratio' => 'required|string|in:16:9,4:3,21:9',
            'resolution' => 'required|string|in:1080p,720p,4k,480p,360p',
            'content_rating' => 'required|string|in:G,PG,13+,16+,18+',
            'advisory_tags' => 'nullable|string',
            'language_code' => 'required|string|max:10',
            'country_code' => 'required|string|size:3',
            'rights_basis' => 'required|string|max:50',
            'format_type' => 'required|string|in:feature,short,clip,stream,episode',
            'category_slug' => 'nullable|string|max:50',
            'is_embeddable' => 'boolean',
            'is_evergreen' => 'boolean',
            'availability_status' => 'required|string|in:discovered,approved,scheduled,restricted,disabled,removed',
        ]);

        $advisoryTags = null;
        if (! empty($validated['advisory_tags'])) {
            $advisoryTags = array_values(array_filter(array_map('trim', explode(',', $validated['advisory_tags']))));
        }

        $video->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'channel_title' => $validated['channel_title'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'media_url' => $validated['media_url'] ?? null,
            'duration_seconds' => $validated['duration_seconds'],
            'aspect_ratio' => $validated['aspect_ratio'],
            'resolution' => $validated['resolution'],
            'content_rating' => $validated['content_rating'],
            'advisory_tags' => $advisoryTags,
            'language_code' => strtolower($validated['language_code']),
            'country_code' => strtoupper($validated['country_code']),
            'rights_basis' => $validated['rights_basis'],
            'format_type' => $validated['format_type'],
            'category_slug' => $validated['category_slug'] ?? null,
            'is_embeddable' => $request->boolean('is_embeddable'),
            'is_evergreen' => $request->boolean('is_evergreen'),
            'availability_status' => $validated['availability_status'],
        ]);

        return redirect()->route('admin.catalogue.show', $video->id)
            ->with('success', 'Catalogue item metadata updated successfully.');
    }

    /**
     * Soft delete an item from active catalogue.
     */
    public function destroy(int $id): RedirectResponse
    {
        $video = Video::findOrFail($id);
        $video->delete();

        return redirect()->route('admin.catalogue.index')
            ->with('success', "Content item '{$video->title}' was soft-deleted from active catalogue.");
    }

    /**
     * Restore a soft-deleted catalogue item.
     */
    public function restore(int $id): RedirectResponse
    {
        $video = Video::onlyTrashed()->findOrFail($id);
        $video->restore();

        return redirect()->route('admin.catalogue.show', $video->id)
            ->with('success', "Content item '{$video->title}' was restored to active catalogue.");
    }
}
