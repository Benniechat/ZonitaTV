<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieController extends Controller
{
    /**
     * Display the verified Zonita TV Movie & Archival Cinema Directory.
     */
    public function index(Request $request): View
    {
        $query = Movie::query()->with('video');

        // Filter by rights classification
        if ($request->filled('rights')) {
            $query->where('rights_classification', $request->query('rights'));
        }

        // Filter by source repository
        if ($request->filled('provider')) {
            $query->where('source_provider', $request->query('provider'));
        }

        // Search by title or genre
        if ($request->filled('q')) {
            $search = $request->query('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%")
                    ->orWhere('synopsis', 'like', "%{$search}%");
            });
        }

        $movies = $query->orderBy('release_year', 'desc')->paginate(12)->withQueryString();

        // 3 Dedicated Movie Playout Channels
        $movieChannels = Channel::whereIn('slug', [
            'zonita-classics',
            'zonita-african-cinema',
            'zonita-documentaries',
        ])->where('is_active', true)->get()->keyBy('slug');

        $stats = [
            'total_movies' => Movie::count(),
            'public_domain' => Movie::where('rights_classification', 'public_domain')->count(),
            'authorized_african' => Movie::where('source_provider', 'nollywood_distributor')->count(),
            'documentaries' => Movie::where('target_channel_slug', 'zonita-documentaries')->count(),
            'rights_cleared_percent' => Movie::count() > 0
                ? round((Movie::where('verification_status', 'verified')->count() / Movie::count()) * 100)
                : 100,
        ];

        return view('movies.index', compact('movies', 'movieChannels', 'stats'));
    }

    /**
     * Display a movie title and its complete rights verification audit certificate.
     */
    public function show(Movie $movie): View
    {
        $targetChannel = Channel::where('slug', $movie->target_channel_slug)->first();

        $relatedMovies = Movie::where('target_channel_slug', $movie->target_channel_slug)
            ->where('id', '!=', $movie->id)
            ->take(4)
            ->get();

        return view('movies.show', compact('movie', 'targetChannel', 'relatedMovies'));
    }
}
