<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Favourite;
use App\Models\ProblemReport;
use App\Models\ViewingEvent;
use App\Services\ChannelCatalogueService;
use App\Services\LinearChannelEngineService;
use App\Services\RightsEligibilityService;
use App\Services\Zonita\ProgrammeScheduleService;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function __construct(
        protected ChannelCatalogueService $catalogueService,
        protected RightsEligibilityService $rightsService,
        protected LinearChannelEngineService $linearEngine,
        protected ProgrammeScheduleService $zonitaScheduleService
    ) {}

    public function index(Request $request)
    {
        $country = $request->header('CF-IPCountry', 'NGA');
        $filters = $request->only(['category', 'q', 'country', 'language', 'featured']);
        $channels = $this->catalogueService->getCatalogue($filters, 18, $country);
        $categories = Category::orderBy('name')->get();

        return view('channels.index', compact('channels', 'categories', 'filters'));
    }

    public function show(Request $request, string $slug)
    {
        $channel = Channel::with(['categories', 'latestRights', 'streams', 'programmes' => function ($q) {
            $q->where('end_time', '>=', now())->orderBy('start_time')->take(5);
        }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $country = $request->header('CF-IPCountry', 'NGA');
        $playback = $this->rightsService->resolvePermittedPlayback($channel, $country);

        // Record viewing event
        ViewingEvent::create([
            'user_id' => $request->user()?->id,
            'channel_id' => $channel->id,
            'session_id' => $request->session()->getId(),
            'event_type' => 'page_view',
            'ip_country' => $country,
            'created_at' => now(),
        ]);

        // Related channels
        $firstCategoryId = $channel->categories->first()?->id;
        $relatedChannels = Channel::where('id', '!=', $channel->id)
            ->where('is_active', true)
            ->where('is_published', true)
            ->when($firstCategoryId, fn ($q) => $q->whereHas('categories', fn ($cat) => $cat->where('categories.id', $firstCategoryId)))
            ->take(4)
            ->get();

        $isFavourited = $request->user() ? Favourite::where('user_id', $request->user()->id)->where('channel_id', $channel->id)->exists() : false;

        // Playout Engine Metadata for 24/7 Linear Channels
        $linearPlayout = null;
        $zonitaEpg = null;

        if ($channel->scheduleEntries()->exists()) {
            $zonitaEpg = $this->zonitaScheduleService->getEpgData($channel);
            if (! empty($zonitaEpg['playlist_queue'])) {
                $linearPlayout = [
                    'channel' => $channel,
                    'current_programme' => $zonitaEpg['current'],
                    'upcoming_programmes' => $zonitaEpg['upcoming'],
                    'playlist_queue' => $zonitaEpg['playlist_queue'],
                    'is_linear' => true,
                ];
            }
        } elseif ($channel->curation_type === 'linear_streamer' || $channel->programmes()->whereNotNull('video_id')->exists()) {
            $linearPlayout = $this->linearEngine->getActivePlayout($channel);
        }

        return view('channels.show', compact('channel', 'playback', 'relatedChannels', 'isFavourited', 'linearPlayout', 'zonitaEpg'));
    }

    public function toggleFavourite(Request $request, Channel $channel)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $favourite = Favourite::where('user_id', $user->id)->where('channel_id', $channel->id)->first();
        if ($favourite) {
            $favourite->delete();
            $favourited = false;
        } else {
            Favourite::create(['user_id' => $user->id, 'channel_id' => $channel->id]);
            $favourited = true;
        }

        return response()->json([
            'success' => true,
            'favourited' => $favourited,
            'message' => $favourited ? 'Added to favourites' : 'Removed from favourites',
        ]);
    }

    public function reportProblem(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'email' => 'nullable|email|max:150',
            'issue_type' => 'required|string|in:broken_stream,sound_issue,wrong_channel,inappropriate,other',
            'description' => 'required|string|max:1000',
        ]);

        ProblemReport::create([
            'channel_id' => $channel->id,
            'user_id' => $request->user()?->id,
            'email' => $validated['email'] ?? $request->user()?->email,
            'issue_type' => $validated['issue_type'],
            'description' => $validated['description'],
            'status' => 'new',
        ]);

        return back()->with('success', 'Thank you for reporting. Our technical team has been notified.');
    }
}
