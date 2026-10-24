@extends('layouts.app')

@section('title', 'Upcoming Football Fixtures & Kickoff Countdowns - Zonita Sports')
@section('meta_description', 'Explore upcoming football fixtures, confirmed SportyTV broadcast schedules, and live countdowns.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-800 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-600/20 border border-blue-500/30 text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Next Fixtures
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight">Upcoming Football Fixtures</h1>
            <p class="text-xs text-gray-400 mt-1">Confirmed kickoff times, SportyTV broadcast verification, and live countdown clocks.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sports.index') }}" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 hover:border-gray-700 text-gray-300 text-xs font-semibold transition">
                &larr; Sports Hub
            </a>
            <a href="{{ route('sports.live') }}" class="px-4 py-2 rounded-xl bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 text-xs font-semibold transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                Live Matches
            </a>
            <a href="{{ route('sports.schedule') }}" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 hover:border-gray-700 text-gray-300 text-xs font-semibold transition">
                Schedule Guide
            </a>
        </div>
    </div>

    <!-- Fixtures Grid -->
    @if($fixtures->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($fixtures as $fixture)
                <x-match-card :fixture="$fixture" />
            @endforeach
        </div>

        <div class="pt-6">
            {{ $fixtures->links() }}
        </div>
    @else
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-12 text-center max-w-xl mx-auto space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-blue-600/10 border border-blue-500/20 text-blue-400 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white">No Upcoming Fixtures Queued</h3>
            <p class="text-xs text-gray-400 leading-relaxed">
                The autonomous discovery engine is currently fetching upcoming match schedules. Check back shortly.
            </p>
        </div>
    @endif

</div>
@endsection
