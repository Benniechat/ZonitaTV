<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Programme;
use App\Models\ScheduleEntry;
use App\Services\RecommendationService;
use App\Services\Zonita\XmltvExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GuideController extends Controller
{
    public function __construct(
        protected RecommendationService $recommendationService,
        protected XmltvExportService $xmltvService
    ) {}

    /**
     * Interactive Electronic Programme Guide (EPG) with 7-Day Timeline and Multi-Timezone Support.
     */
    public function index(Request $request): View
    {
        // 1. Timezone detection & normalization
        $requestedTz = $request->input('tz', 'Africa/Lagos');
        $validTimezones = \DateTimeZone::listIdentifiers();
        $timezone = in_array($requestedTz, $validTimezones, true) ? $requestedTz : 'Africa/Lagos';

        // 2. Selected Day & 7-Day Rolling Horizon Tabs
        $nowTz = Carbon::now($timezone);
        $selectedDateStr = $request->input('day', $nowTz->toDateString());
        try {
            $selectedDate = Carbon::parse($selectedDateStr, $timezone)->startOfDay();
        } catch (\Throwable) {
            $selectedDate = $nowTz->copy()->startOfDay();
        }

        $dayTabs = [];
        for ($i = 0; $i < 7; $i++) {
            $tabDate = $nowTz->copy()->addDays($i)->startOfDay();
            $label = match ($i) {
                0 => 'Today',
                1 => 'Tomorrow',
                default => $tabDate->format('D, M j'),
            };

            $dayTabs[] = [
                'date' => $tabDate->toDateString(),
                'label' => $label,
                'day_name' => $tabDate->format('l'),
                'is_selected' => $tabDate->toDateString() === $selectedDate->toDateString(),
            ];
        }

        // 3. Daypart Filtering
        $daypart = $request->input('daypart', 'all');
        $slotStart = $selectedDate->copy();
        $slotEnd = $selectedDate->copy()->endOfDay();

        match ($daypart) {
            'morning' => [$slotStart = $selectedDate->copy()->setTime(6, 0), $slotEnd = $selectedDate->copy()->setTime(12, 0)],
            'afternoon' => [$slotStart = $selectedDate->copy()->setTime(12, 0), $slotEnd = $selectedDate->copy()->setTime(18, 0)],
            'primetime' => [$slotStart = $selectedDate->copy()->setTime(18, 0), $slotEnd = $selectedDate->copy()->setTime(23, 0)],
            'night' => [$slotStart = $selectedDate->copy()->setTime(23, 0), $slotEnd = $selectedDate->copy()->addDay()->setTime(6, 0)],
            default => null,
        };

        // Convert slot to UTC for DB querying
        $slotStartUtc = $slotStart->copy()->setTimezone('UTC');
        $slotEndUtc = $slotEnd->copy()->setTimezone('UTC');

        // 4. Categories & Channels
        $selectedCategory = $request->input('category');
        $categories = Category::orderBy('name')->get();

        $channelsQuery = Channel::where('is_active', true)
            ->where('is_published', true)
            ->with(['categories', 'primaryStream'])
            ->when($selectedCategory, fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('slug', $selectedCategory)))
            ->orderByRaw('CASE WHEN channel_number IS NOT NULL THEN 0 ELSE 1 END, CAST(channel_number AS UNSIGNED) ASC, display_order ASC, name ASC');

        $channels = $channelsQuery->get();

        // 5. On-Air Live Show & Schedule Lineup per Channel
        $nowUtc = now('UTC');
        $epgGrid = [];

        foreach ($channels as $ch) {
            // Live right now
            $currentLive = ScheduleEntry::where('channel_id', $ch->id)
                ->where('start_time', '<=', $nowUtc)
                ->where('end_time', '>', $nowUtc)
                ->with('video')
                ->first();

            // Lineup for selected day & daypart
            $lineup = ScheduleEntry::where('channel_id', $ch->id)
                ->where('end_time', '>=', $slotStartUtc)
                ->where('start_time', '<=', $slotEndUtc)
                ->orderBy('start_time')
                ->with('video')
                ->take(15)
                ->get()
                ->map(function ($entry) use ($timezone) {
                    $entryStart = Carbon::parse($entry->start_time)->setTimezone($timezone);
                    $entryEnd = Carbon::parse($entry->end_time)->setTimezone($timezone);

                    return [
                        'id' => $entry->id,
                        'title' => $entry->title,
                        'format_type' => $entry->format_type,
                        'duration_seconds' => $entry->duration_seconds,
                        'duration_minutes' => round($entry->duration_seconds / 60),
                        'start_time' => $entryStart,
                        'end_time' => $entryEnd,
                        'start_formatted' => $entryStart->format('h:i A'),
                        'end_formatted' => $entryEnd->format('h:i A'),
                        'is_current' => now('UTC')->between(Carbon::parse($entry->start_time), Carbon::parse($entry->end_time)),
                        'is_fallback' => (bool) $entry->is_fallback,
                    ];
                });

            $epgGrid[] = [
                'channel' => $ch,
                'current_live' => $currentLive,
                'lineup' => $lineup,
            ];
        }

        // Live now programmes fallback for legacy widgets
        $liveProgrammes = Programme::with(['channel', 'category'])
            ->where('start_time', '<=', $nowUtc)
            ->where('end_time', '>=', $nowUtc)
            ->when($selectedCategory, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $selectedCategory)))
            ->orderBy('start_time')
            ->get();

        $upcomingProgrammes = Programme::with(['channel', 'category'])
            ->where('start_time', '>', $nowUtc)
            ->where('start_time', '<=', $nowUtc->copy()->addHours(12))
            ->when($selectedCategory, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $selectedCategory)))
            ->orderBy('start_time')
            ->get();

        $supportedTimezones = [
            'Africa/Lagos' => 'West Africa Time (WAT) - Lagos',
            'UTC' => 'Coordinated Universal Time (UTC)',
            'Europe/London' => 'London (GMT/BST)',
            'America/New_York' => 'US Eastern (EST/EDT) - New York',
            'America/Chicago' => 'US Central (CST/CDT) - Chicago',
            'America/Los_Angeles' => 'US Pacific (PST/PDT) - Los Angeles',
            'Africa/Johannesburg' => 'South Africa Time (SAST)',
            'Africa/Nairobi' => 'East Africa Time (EAT) - Nairobi',
            'Asia/Dubai' => 'Gulf Standard Time (GST) - Dubai',
        ];

        return view('guide.index', compact(
            'epgGrid',
            'dayTabs',
            'selectedDate',
            'timezone',
            'supportedTimezones',
            'daypart',
            'categories',
            'selectedCategory',
            'liveProgrammes',
            'upcomingProgrammes'
        ));
    }

    /**
     * Personalized Watch Guide.
     */
    public function personalized(Request $request): View
    {
        $user = $request->user();
        $recommendedChannels = $this->recommendationService->rankChannelsForUser($user, 12);
        $favourites = $user->favouriteChannels()->with('categories')->get();

        return view('guide.personalized', compact('user', 'recommendedChannels', 'favourites'));
    }

    /**
     * XMLTV Public Feed (Direct XML output for IPTV apps, TiviMate, Smart IPTV).
     */
    public function xmltv(Request $request): Response
    {
        $channelSlug = $request->input('channel');
        $days = min(30, max(1, (int) $request->input('days', 7)));
        $tz = $request->input('tz', 'UTC');

        $channels = null;
        if ($channelSlug) {
            $channels = Channel::where('slug', $channelSlug)->get();
        }

        $xml = $this->xmltvService->generateXml(
            $channels,
            now('UTC')->subHours(6),
            now('UTC')->addDays($days),
            $tz
        );

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * XMLTV Download Endpoint (.xml attachment).
     */
    public function exportXmltv(Request $request): Response
    {
        $response = $this->xmltv($request);
        $filename = 'zonita-epg-'.now()->format('Y-m-d').'.xml';

        return $response->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
