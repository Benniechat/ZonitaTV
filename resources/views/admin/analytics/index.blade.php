@extends('admin.layout')

@section('title', 'Audience Analytics & Viewership Reporting - Zoniva TV')

@section('admin_content')

<div class="space-y-8">
    
    <!-- Header with Filters & Export -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold">
                    Audience Intelligence
                </span>
                <span class="text-xs text-gray-500">Live Telemetry & Viewing Insights</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white">Audience Analytics & Reporting</h1>
            <p class="text-xs text-gray-400 mt-0.5">Real-time viewership metrics, playback sessions, geographic distribution, and channel leaders.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Period Filter -->
            <div class="inline-flex rounded-xl bg-gray-900 border border-gray-800 p-1">
                <a href="{{ route('admin.analytics.index', ['period' => 'today']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $period === 'today' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Today
                </a>
                <a href="{{ route('admin.analytics.index', ['period' => '7d']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $period === '7d' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Last 7 Days
                </a>
                <a href="{{ route('admin.analytics.index', ['period' => '30d']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $period === '30d' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Last 30 Days
                </a>
                <a href="{{ route('admin.analytics.index', ['period' => 'all']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $period === 'all' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    All Time
                </a>
            </div>

            <a href="{{ route('admin.analytics.export', ['period' => $period]) }}" 
               class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-xs font-semibold text-gray-200 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export JSON
            </a>
        </div>
    </div>

    <!-- Core Audience KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Total Viewing Interactions</span>
            <div class="text-3xl font-extrabold text-white mt-1">{{ number_format($totalEvents) }}</div>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-[10px] text-indigo-400 font-semibold">{{ number_format($uniqueSessions) }} Sessions</span>
                <span class="text-gray-600">&bull;</span>
                <span class="text-[10px] text-gray-400">{{ number_format($uniqueUsers) }} Logged Users</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Playback Started Events</span>
            <div class="text-3xl font-extrabold text-emerald-400 mt-1">{{ number_format($playbackStarts) }}</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">Active video stream initiations</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">EPG Guide Views</span>
            <div class="text-3xl font-extrabold text-amber-400 mt-1">{{ number_format($guideViews) }}</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">Interactive schedule browses</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Completed Playout Broadcasts</span>
            <div class="text-3xl font-extrabold text-red-500 mt-1">{{ number_format($completedBroadcasts) }}</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">{{ number_format($totalBroadcastSessions) }} viewer playout cycles</span>
        </div>

    </div>

    <!-- Secondary Grids: Top Channels & Geographic Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Top Channels Leaderboard -->
        <div class="lg:col-span-2 bg-[#12151c] border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-white">Top Performing Channels</h3>
                    <p class="text-xs text-gray-400">Ranked by playback initiation sessions</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-md bg-gray-900 text-gray-400 border border-gray-800">
                    {{ $topChannels->count() }} Stations
                </span>
            </div>

            @if($topChannels->isEmpty())
                <div class="py-12 text-center text-xs text-gray-500">
                    No playback events logged for this timeframe.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($topChannels as $item)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-950/60 border border-gray-800/80">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-mono font-bold text-gray-500 w-6">#{{ $loop->iteration }}</span>
                                <div class="w-9 h-9 rounded-lg bg-black border border-gray-800 flex items-center justify-center p-1.5 flex-shrink-0">
                                    @if($item['channel']?->logo_url)
                                        <img src="{{ $item['channel']->logo_url }}" alt="{{ $item['channel_name'] }}" class="w-full h-full object-contain">
                                    @else
                                        <span class="text-xs font-bold text-red-500">{{ substr($item['channel_name'], 0, 2) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-white">{{ $item['channel_name'] }}</h4>
                                    <div class="flex items-center gap-2 text-[10px] text-gray-400 mt-0.5">
                                        <span class="font-mono">CH {{ $item['channel_number'] }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $item['category'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-xs font-extrabold text-white">{{ number_format($item['playback_count']) }} starts</div>
                                <div class="text-[10px] text-indigo-400 font-semibold">{{ $item['share_percentage'] }}% share</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Geographic Viewership Distribution -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
            <div class="mb-4">
                <h3 class="text-base font-bold text-white">Geographic Breakdown</h3>
                <p class="text-xs text-gray-400">Viewership distribution by IP country</p>
            </div>

            @if($geoDistribution->isEmpty())
                <div class="py-12 text-center text-xs text-gray-500">
                    No geographic logs in this window.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($geoDistribution as $geo)
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-white uppercase">{{ $geo['country_code'] }}</span>
                                <span class="text-gray-400">{{ number_format($geo['count']) }} events ({{ $geo['percentage'] }}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-900 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full" style="width: {{ min(100, $geo['percentage']) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Event Distribution Summary -->
            <div class="mt-8 pt-6 border-t border-gray-800">
                <h4 class="text-xs font-bold text-gray-300 uppercase tracking-wider mb-3">Event Type Breakdown</h4>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-gray-400">
                        <span>Playback Starts</span>
                        <span class="font-semibold text-white">{{ number_format($playbackStarts) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Page Views</span>
                        <span class="font-semibold text-white">{{ number_format($pageViews) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Guide Views</span>
                        <span class="font-semibold text-white">{{ number_format($guideViews) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Favourite Toggles</span>
                        <span class="font-semibold text-white">{{ number_format($favouriteToggles) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Live Telemetry Stream Feed -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Audience Activity Feed
                </h3>
                <p class="text-xs text-gray-400">Real-time incoming events across web viewers</p>
            </div>
            <span class="text-xs text-gray-500">Auto-logged</span>
        </div>

        @if($recentEvents->isEmpty())
            <div class="py-8 text-center text-xs text-gray-500">
                No recent activity records to display.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-2.5">Time</th>
                            <th class="py-2.5">Event Type</th>
                            <th class="py-2.5">Channel</th>
                            <th class="py-2.5">Country</th>
                            <th class="py-2.5">Session / Viewer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        @foreach($recentEvents as $ev)
                            <tr class="hover:bg-gray-900/30 transition">
                                <td class="py-3 text-gray-400 font-mono">{{ $ev->created_at->diffForHumans() }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                        @if($ev->event_type === 'playback_started') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @elseif($ev->event_type === 'guide_view') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                        @elseif($ev->event_type === 'favourite_toggle') bg-red-500/10 text-red-400 border border-red-500/20
                                        @else bg-gray-800 text-gray-300 @endif">
                                        {{ str_replace('_', ' ', $ev->event_type) }}
                                    </span>
                                </td>
                                <td class="py-3 font-semibold text-white">
                                    {{ $ev->channel->name ?? 'Channel #' . $ev->channel_id }}
                                </td>
                                <td class="py-3 text-gray-400 uppercase font-mono">{{ $ev->ip_country }}</td>
                                <td class="py-3 text-gray-400">
                                    {{ $ev->user ? $ev->user->name : substr($ev->session_id ?? 'guest', 0, 10) . '...' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@endsection
