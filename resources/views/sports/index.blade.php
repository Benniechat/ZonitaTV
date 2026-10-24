@extends('layouts.app')

@section('title', 'Zonita Sports Live - Automated Football Discovery & Broadcasts')
@section('meta_description', 'Discover live football fixtures, SportyTV scheduled broadcasts, kickoff countdowns, and continuous 24/7 sports virtual television.')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <!-- Hero Header / Channel Banner -->
    <div class="relative bg-gradient-to-r from-red-950/60 via-[#121622] to-black border border-gray-800 rounded-3xl p-8 sm:p-12 overflow-hidden shadow-2xl">
        <div class="relative z-10 max-w-2xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-red-600/20 border border-red-500/30 text-red-400 text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                Zonita Sports Live Engine
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Automated Football Discovery & Verified Broadcasts
            </h1>
            <p class="text-sm text-gray-300 leading-relaxed">
                Real-time fixture schedules, confirmed SportyTV broadcast destinations, live match clocks, and continuous 24/7 sports television.
            </p>

            @if($sportsChannel)
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="{{ route('channels.show', $sportsChannel->slug) }}" 
                       class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-red-600/30 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Watch 24/7 Sports Station
                    </a>
                    <a href="{{ route('sports.schedule') }}" 
                       class="px-5 py-3 rounded-xl bg-gray-900/80 hover:bg-gray-800 border border-gray-700 text-gray-200 text-xs font-semibold transition">
                        Full Schedule Guide
                    </a>
                </div>
            @endif
        </div>

        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <svg class="w-96 h-96 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
        </div>
    </div>

    <!-- Competition Filter Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="{{ route('sports.index') }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition border {{ empty($selectedComp) ? 'bg-white text-black border-white shadow' : 'bg-[#121620] border-gray-800 text-gray-400 hover:text-white' }}">
            All Competitions
        </a>
        @foreach($competitions as $comp)
            <a href="{{ route('sports.index', ['competition' => $comp->id]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold shrink-0 transition border flex items-center gap-2 {{ $selectedComp == $comp->id ? 'bg-red-600 border-red-500 text-white shadow' : 'bg-[#121620] border-gray-800 text-gray-400 hover:text-white' }}">
                @if($comp->emblem_url)
                    <img src="{{ $comp->emblem_url }}" class="w-3.5 h-3.5 object-contain">
                @endif
                <span>{{ $comp->name }}</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/40">{{ $comp->fixtures_count }}</span>
            </a>
        @endforeach
    </div>

    <!-- Section 1: Live Now In Play -->
    @if($liveFixtures->isNotEmpty())
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-red-500 animate-ping"></span>
                    Live Matches In Progress
                </h2>
                <a href="{{ route('sports.live') }}" class="text-xs text-red-400 hover:text-red-300 font-semibold">View All Live &rarr;</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($liveFixtures as $liveFix)
                    <x-match-card :fixture="$liveFix" />
                @endforeach
            </div>
        </div>
    @endif

    <!-- Section 2: Starting Soon (Next 6 Hours) -->
    @if($startingSoon->isNotEmpty())
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Starting Soon (Today)
                </h2>
                <span class="text-xs text-gray-400">Kickoff times in West Africa Time (WAT)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($startingSoon as $soonFix)
                    <x-match-card :fixture="$soonFix" />
                @endforeach
            </div>
        </div>
    @endif

    <!-- Section 3: Upcoming Matches -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Upcoming Matchday Fixtures
            </h2>
            <a href="{{ route('sports.upcoming') }}" class="text-xs text-gray-400 hover:text-white font-semibold">Browse Catalogue &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($upcoming as $upFix)
                <x-match-card :fixture="$upFix" />
            @empty
                <div class="col-span-full py-12 text-center text-gray-500 bg-[#121620] border border-gray-800 rounded-2xl">
                    No upcoming fixtures recorded for this selection.
                </div>
            @endforelse
        </div>
    </div>

</div>

@endsection
