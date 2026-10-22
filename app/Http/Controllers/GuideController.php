<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Programme;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    protected RecommendationService $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request)
    {
        $selectedCategory = $request->input('category');
        $categories = Category::orderBy('name')->get();

        $now = now();
        $liveProgrammes = Programme::with(['channel', 'category'])
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->when($selectedCategory, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $selectedCategory)))
            ->orderBy('start_time')
            ->get();

        $upcomingProgrammes = Programme::with(['channel', 'category'])
            ->where('start_time', '>', $now)
            ->where('start_time', '<=', $now->copy()->addHours(12))
            ->when($selectedCategory, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $selectedCategory)))
            ->orderBy('start_time')
            ->get();

        return view('guide.index', compact('liveProgrammes', 'upcomingProgrammes', 'categories', 'selectedCategory'));
    }

    public function personalized(Request $request)
    {
        $user = $request->user();
        $recommendedChannels = $this->recommendationService->rankChannelsForUser($user, 12);
        $favourites = $user->favouriteChannels()->with('categories')->get();

        return view('guide.personalized', compact('user', 'recommendedChannels', 'favourites'));
    }
}
