@extends('layouts.app')

@section('title', 'Sports Programme Guide & Match Schedule - Zonita Sports')
@section('meta_description', 'Full daily broadcast schedule for football matches on Zonita TV and official SportyTV feeds.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-800 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-600/20 border border-emerald-500/30 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                EPG / Programme Guide
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight">Today's Sports Programme Guide</h1>
            <p class="text-xs text-gray-400 mt-1">
                Linear timetable for {{ now()->timezone(config('sports.timezone', 'Africa/Lagos'))->format('l, d F Y') }} (Lagos WAT Time)
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sports.index') }}" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 hover:border-gray-700 text-gray-300 text-xs font-semibold transition">
                &larr; Sports Hub
            </a>
            <a href="{{ route('sports.live') }}" class="px-4 py-2 rounded-xl bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 text-xs font-semibold transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                Live Matches
            </a>
            <a href="{{ route('channels.show', config('sports.channel_slug', 'zonita-football-24-7')) }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 text-white text-xs font-bold uppercase tracking-wider shadow-md hover:brightness-110 transition">
                24/7 Channel
            </a>
        </div>
    </div>

    <!-- Timeline List -->
    @if($fixtures->isNotEmpty())
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl overflow-hidden divide-y divide-gray-800/80 shadow-xl">
            @foreach($fixtures as $fixture)
                <div class="p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-800/30 transition">
                    
                    <!-- Kickoff Time & Competition -->
                    <div class="flex items-center gap-4 min-w-[200px]">
                        <div class="text-center bg-gray-950/80 border border-gray-800 rounded-xl px-3 py-2 shrink-0">
                            <span class="text-xs font-black text-red-400 block font-mono">
                                {{ $fixture->lagos_kickoff->format('H:i') }}
                            </span>
                            <span class="text-[10px] text-gray-500 font-bold uppercase">WAT</span>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-gray-400 flex items-center gap-1.5">
                                @if($fixture->competition?->emblem_url)
                                    <img src="{{ $fixture->competition->emblem_url }}" class="w-3.5 h-3.5 object-contain">
                                @endif
                                <span>{{ $fixture->competition?->name ?? 'Football' }}</span>
                            </div>
                            <span class="text-[10px] text-gray-500">
                                @if($fixture->venue)
                                    {{ $fixture->venue }}
                                @else
                                    Matchday {{ $fixture->matchday ?? '1' }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Teams & Score / Status -->
                    <div class="flex-1 flex items-center justify-start sm:justify-center gap-6">
                        <!-- Home -->
                        <div class="flex items-center gap-3 w-40 justify-end text-right">
                            <span class="text-xs sm:text-sm font-bold text-white truncate">{{ $fixture->homeTeam?->name }}</span>
                            @if($fixture->homeTeam?->crest_url)
                                <img src="{{ $fixture->homeTeam->crest_url }}" class="w-6 h-6 object-contain shrink-0">
                            @endif
                        </div>

                        <!-- Score / VS -->
                        <div class="px-3 py-1 rounded-lg bg-gray-950 border border-gray-800 text-center shrink-0 min-w-[60px]">
                            @if($fixture->is_live_now || $fixture->status === 'finished')
                                <span class="text-xs font-mono font-black text-white">
                                    {{ $fixture->home_score ?? 0 }} - {{ $fixture->away_score ?? 0 }}
                                </span>
                            @else
                                <span class="text-[10px] font-black text-gray-500">VS</span>
                            @endif
                        </div>

                        <!-- Away -->
                        <div class="flex items-center gap-3 w-40 justify-start text-left">
                            @if($fixture->awayTeam?->crest_url)
                                <img src="{{ $fixture->awayTeam->crest_url }}" class="w-6 h-6 object-contain shrink-0">
                            @endif
                            <span class="text-xs sm:text-sm font-bold text-white truncate">{{ $fixture->awayTeam?->name }}</span>
                        </div>
                    </div>

                    <!-- Status & Action -->
                    <div class="flex items-center justify-end gap-3 min-w-[200px]">
                        @if($fixture->is_live_now)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-600/20 border border-red-500/30 text-red-400 text-[10px] font-black uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                Live {{ $fixture->minute ? $fixture->minute . "'" : '' }}
                            </span>
                        @elseif($fixture->status === 'finished')
                            <span class="px-2.5 py-1 rounded-full bg-gray-800 border border-gray-700 text-gray-400 text-[10px] font-semibold uppercase">
                                Full Time
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full bg-blue-900/30 border border-blue-800/40 text-blue-400 text-[10px] font-semibold">
                                Upcoming
                            </span>
                        @endif

                        @if($fixture->sportyBroadcast?->isConfirmed())
                            <span class="px-2 py-0.5 rounded bg-red-950/80 border border-red-800/50 text-[10px] font-black text-red-400">
                                SportyTV
                            </span>
                        @endif

                        <a href="{{ route('sports.show', $fixture->id) }}" 
                           class="px-3.5 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white text-xs font-semibold transition shrink-0">
                            Details &rarr;
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-12 text-center max-w-xl mx-auto space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-gray-900 border border-gray-800 text-gray-400 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white">No Fixtures Scheduled Today</h3>
            <p class="text-xs text-gray-400 leading-relaxed">
                There are no matches scheduled for today's date in our live tracking database. View our upcoming fixtures calendar for the rest of the week.
            </p>
            <div class="pt-2">
                <a href="{{ route('sports.upcoming') }}" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider transition">
                    View Upcoming Matches
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
