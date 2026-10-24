@extends('layouts.app')

@section('title', 'Live Football Matches - Zonita Sports')
@section('meta_description', 'Watch and follow live football fixtures happening right now, with verified SportyTV broadcast destinations and live match tracking.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-800 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/20 border border-red-500/30 text-red-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                Now Playing
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight">Live Football Matches</h1>
            <p class="text-xs text-gray-400 mt-1">Real-time live matches currently on the pitch with verified broadcast feeds.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sports.index') }}" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 hover:border-gray-700 text-gray-300 text-xs font-semibold transition">
                &larr; Sports Hub
            </a>
            <a href="{{ route('sports.upcoming') }}" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 hover:border-gray-700 text-gray-300 text-xs font-semibold transition">
                Upcoming Matches
            </a>
            <a href="{{ route('sports.schedule') }}" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 hover:border-gray-700 text-gray-300 text-xs font-semibold transition">
                Programme Guide
            </a>
        </div>
    </div>

    <!-- Live Matches Grid -->
    @if($fixtures->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($fixtures as $fixture)
                <x-match-card :fixture="$fixture" />
            @endforeach
        </div>
    @else
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-12 text-center max-w-xl mx-auto space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-red-600/10 border border-red-500/20 text-red-400 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white">No Matches Currently Live</h3>
            <p class="text-xs text-gray-400 leading-relaxed">
                There are no active football matches on-air at this exact moment. Check out the upcoming schedule or tune into our 24/7 continuous linear football station.
            </p>
            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('sports.upcoming') }}" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider transition">
                    View Upcoming Fixtures
                </a>
                <a href="{{ route('channels.show', config('sports.channel_slug', 'zonita-football-24-7')) }}" class="px-5 py-2.5 rounded-xl bg-gray-900 border border-gray-700 text-gray-200 text-xs font-semibold hover:bg-gray-800 transition">
                    Watch 24/7 Station
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
