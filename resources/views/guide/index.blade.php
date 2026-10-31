@extends('layouts.app')

@section('title', 'Electronic Programme Guide (EPG) - Zoniva TV')
@section('meta_description', 'Interactive 7-day Electronic Programme Guide (EPG) with continuous linear schedules, timezone switching, and XMLTV export.')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <!-- Top Header -->
    <div class="pb-6 border-b border-gray-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-600/10 text-red-400 text-xs font-semibold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                Electronic Programme Guide (EPG)
            </div>
            <h1 class="text-3xl font-extrabold text-white">Interactive Programme Guide</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">Continuous 24/7 broadcasting schedules across verified channels and virtual networks.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Timezone Switcher Form -->
            <form method="GET" action="{{ route('guide.index') }}" class="flex items-center gap-2">
                @if(request('day')) <input type="hidden" name="day" value="{{ request('day') }}"> @endif
                @if(request('daypart')) <input type="hidden" name="daypart" value="{{ request('daypart') }}"> @endif
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif

                <label class="text-xs text-gray-400 font-semibold hidden sm:inline">Timezone:</label>
                <select name="tz" onchange="this.form.submit()" class="bg-[#12151c] text-xs text-white border border-gray-700 rounded-xl px-3 py-2 focus:outline-none focus:border-red-500 font-mono">
                    @foreach($supportedTimezones as $tzCode => $tzLabel)
                        <option value="{{ $tzCode }}" {{ $timezone === $tzCode ? 'selected' : '' }}>
                            {{ $tzLabel }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- XMLTV Download -->
            <a href="{{ route('guide.xmltv.export') }}" class="px-3.5 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-xs font-semibold text-gray-200 transition flex items-center gap-1.5 shadow">
                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>XMLTV Feed (.xml)</span>
            </a>
        </div>
    </div>

    <!-- 7-Day Date Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
        @foreach($dayTabs as $tab)
            <a href="{{ route('guide.index', array_merge(request()->query(), ['day' => $tab['date']])) }}" 
               class="flex flex-col items-center justify-center min-w-[110px] px-4 py-2.5 rounded-xl border transition {{ $tab['is_selected'] ? 'bg-red-600 text-white border-red-500 shadow-lg' : 'bg-[#12151c] text-gray-400 border-gray-800 hover:text-white hover:border-gray-700' }}">
                <span class="text-xs font-bold">{{ $tab['label'] }}</span>
                <span class="text-[10px] {{ $tab['is_selected'] ? 'text-red-100' : 'text-gray-500' }}">{{ $tab['date'] }}</span>
            </a>
        @endforeach
    </div>

    <!-- Daypart & Category Filter Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-[#0d1017] border border-gray-800">
        <!-- Dayparts -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mr-2 hidden sm:inline">Time Slot:</span>
            @foreach(['all' => 'All Day', 'morning' => 'Morning (06-12)', 'afternoon' => 'Afternoon (12-18)', 'primetime' => 'Prime Time (18-23)', 'night' => 'Late Night (23-06)'] as $dKey => $dLbl)
                <a href="{{ route('guide.index', array_merge(request()->query(), ['daypart' => $dKey])) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $daypart === $dKey ? 'bg-gray-800 text-white font-bold border border-gray-700' : 'text-gray-400 hover:text-white' }}">
                    {{ $dLbl }}
                </a>
            @endforeach
        </div>

        <!-- Categories -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs">
            <a href="{{ route('guide.index', array_merge(request()->query(), ['category' => null])) }}" 
               class="px-2.5 py-1 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ !request('category') ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-400 hover:text-white' }}">
                All
            </a>
            @foreach($categories->take(6) as $c)
                <a href="{{ route('guide.index', array_merge(request()->query(), ['category' => $c->slug])) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ request('category') === $c->slug ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-400 hover:text-white' }}">
                    {{ $c->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- EPG Interactive Channel Playout Matrix -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span>Programming Matrix for {{ $selectedDate->format('l, F j, Y') }}</span>
            </h2>
            <span class="text-xs text-gray-400 font-mono">{{ $timezone }}</span>
        </div>

        <div class="space-y-3">
            @forelse($epgGrid as $row)
                @php $ch = $row['channel']; @endphp
                <div class="p-4 rounded-2xl bg-[#12151c] border border-gray-800 hover:border-gray-700 transition flex flex-col md:flex-row items-start md:items-center gap-4">
                    <!-- Channel Identity Column -->
                    <div class="w-full md:w-56 flex-shrink-0 flex items-center justify-between md:justify-start gap-3 border-b md:border-b-0 md:border-r border-gray-800/80 pb-3 md:pb-0 md:pr-4">
                        <div class="flex items-center gap-2.5">
                            <div class="relative">
                                <img src="{{ $ch->logo_url }}" alt="{{ $ch->name }}" class="w-10 h-10 object-contain rounded-xl bg-gray-900 border p-1" style="border-color: {{ $ch->accent_color }};">
                                <span class="absolute -bottom-1 -right-1 px-1 rounded text-[8px] font-mono font-bold text-white shadow" style="background-color: {{ $ch->accent_color }};">
                                    {{ $ch->display_number }}
                                </span>
                            </div>
                            <div>
                                <a href="{{ route('channels.show', $ch->slug) }}" class="font-bold text-white text-xs hover:text-red-400 transition block truncate max-w-[140px]">
                                    {{ $ch->name }}
                                </a>
                                <span class="text-[10px] text-gray-400 capitalize">{{ str_replace('_', ' ', $ch->playback_mode) }}</span>
                            </div>
                        </div>

                        <a href="{{ route('channels.show', $ch->slug) }}" class="px-2.5 py-1 rounded-lg bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white text-[11px] font-bold transition">
                            Watch &rarr;
                        </a>
                    </div>

                    <!-- Lineup Horizontal Timeline -->
                    <div class="flex-grow w-full overflow-x-auto scrollbar-thin pb-1">
                        <div class="flex items-stretch gap-2.5 min-w-full">
                            @forelse($row['lineup'] as $entry)
                                <div class="flex-shrink-0 w-56 p-2.5 rounded-xl border text-xs flex flex-col justify-between transition {{ $entry['is_current'] ? 'bg-red-950/30 border-red-500/50 shadow' : 'bg-gray-900/60 border-gray-800 hover:border-gray-700' }}">
                                    <div>
                                        <div class="flex items-center justify-between text-[10px] font-mono text-gray-400 mb-1">
                                            <span>{{ $entry['start_formatted'] }} - {{ $entry['end_formatted'] }}</span>
                                            @if($entry['is_current'])
                                                <span class="px-1.5 py-0.2 rounded bg-red-600 text-white font-bold uppercase text-[9px] animate-pulse">LIVE</span>
                                            @else
                                                <span class="text-gray-500">{{ $entry['duration_minutes'] }}m</span>
                                            @endif
                                        </div>
                                        <div class="font-bold text-white line-clamp-2 leading-tight">
                                            {{ $entry['title'] }}
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-[10px] text-gray-500">
                                        <span class="capitalize">{{ $entry['format_type'] }}</span>
                                        @if($entry['is_fallback'])
                                            <span class="text-amber-400">Intermission</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-4 px-2 text-xs text-gray-500 italic">
                                    No scheduled programs listed for this time block. Station running fallback continuous playout.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-[#12151c] rounded-2xl border border-gray-800">
                    <p class="text-xs text-gray-400">No active broadcast channels found matching the selected criteria.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- On Air Right Now Live Showcase -->
    @if($liveProgrammes->isNotEmpty())
        <div class="pt-6 border-t border-gray-800/80">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                Featured On Air Now
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($liveProgrammes->take(6) as $prog)
                    @php
                        $now = now();
                        $start = $prog->start_time;
                        $end = $prog->end_time;
                        $totalDuration = max(1, $start->diffInSeconds($end));
                        $elapsed = max(0, $start->diffInSeconds($now));
                        $progressPercent = min(100, max(5, round(($elapsed / $totalDuration) * 100)));
                    @endphp

                    <div class="p-4 rounded-xl bg-[#101217] border border-gray-800 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-gray-300">{{ $prog->channel->name ?? 'Channel' }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-600 text-white uppercase">LIVE</span>
                            </div>
                            <h3 class="font-bold text-white text-sm line-clamp-1 mb-1">{{ $prog->title }}</h3>
                            <p class="text-xs text-gray-400 line-clamp-2 mb-3">{{ $prog->description }}</p>
                        </div>
                        <div>
                            <div class="w-full bg-gray-800 h-1.5 rounded-full overflow-hidden mb-2">
                                <div class="bg-red-500 h-full rounded-full" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-gray-500 font-mono">
                                <span>{{ $prog->start_time->format('h:i A') }}</span>
                                <span>{{ $prog->end_time->format('h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

@endsection
