<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\SportsCompetition;
use App\Models\SportsFixture;
use App\Services\Sports\MatchThumbnailService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SportsController extends Controller
{
    public function __construct(
        protected MatchThumbnailService $thumbnails
    ) {}

    /**
     * Display the primary Zonita Sports Live hub.
     */
    public function index(Request $request): View
    {
        $selectedComp = $request->input('competition');

        $competitions = SportsCompetition::where('is_active', true)
            ->withCount('fixtures')
            ->orderBy('display_order')
            ->get();

        $liveFixtures = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->live()
            ->when($selectedComp, fn ($q) => $q->where('competition_id', $selectedComp))
            ->get();

        $startingSoon = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->upcoming()
            ->where('utc_kickoff', '<=', now()->addHours(6))
            ->when($selectedComp, fn ($q) => $q->where('competition_id', $selectedComp))
            ->take(6)
            ->get();

        $upcoming = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->upcoming()
            ->where('utc_kickoff', '>', now()->addHours(6))
            ->when($selectedComp, fn ($q) => $q->where('competition_id', $selectedComp))
            ->take(12)
            ->get();

        $sportsChannel = Channel::where('slug', config('sports.channel_slug', 'zonita-football-24-7'))->first();

        return view('sports.index', compact(
            'competitions',
            'liveFixtures',
            'startingSoon',
            'upcoming',
            'selectedComp',
            'sportsChannel'
        ));
    }

    /**
     * Dedicated Live Now view.
     */
    public function live(): View
    {
        $fixtures = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->live()
            ->get();

        return view('sports.live', compact('fixtures'));
    }

    /**
     * Upcoming matches view with countdowns.
     */
    public function upcoming(Request $request): View
    {
        $fixtures = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->upcoming()
            ->paginate(15);

        return view('sports.upcoming', compact('fixtures'));
    }

    /**
     * Today's sports programme guide.
     */
    public function schedule(): View
    {
        $fixtures = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->whereDate('utc_kickoff', now()->toDateString())
            ->orderBy('utc_kickoff')
            ->get();

        return view('sports.schedule', compact('fixtures'));
    }

    /**
     * Match details & viewing stage page.
     */
    public function show(SportsFixture $fixture): View
    {
        $fixture->load(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast']);

        $thumbnail = $this->thumbnails->resolveMatchThumbnail($fixture);

        $relatedFixtures = SportsFixture::with(['competition', 'homeTeam', 'awayTeam', 'sportyBroadcast'])
            ->where('competition_id', $fixture->competition_id)
            ->where('id', '!=', $fixture->id)
            ->upcoming()
            ->take(4)
            ->get();

        return view('sports.show', compact('fixture', 'thumbnail', 'relatedFixtures'));
    }
}
