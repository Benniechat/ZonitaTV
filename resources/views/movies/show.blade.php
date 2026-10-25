@extends('layouts.app')

@section('title', $movie->title . ' (' . $movie->release_year . ') - Rights Audit & Film Details - Zonita TV')
@section('meta_description', $movie->synopsis)

@section('content')
<div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-gray-400">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <span>/</span>
        <a href="{{ route('movies.index') }}" class="hover:text-white transition">Movies & Cinema</a>
        <span>/</span>
        <span class="text-white truncate">{{ $movie->title }}</span>
    </nav>

    <!-- Main Movie Feature Stage -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column: Video Preview / Poster & Basic Meta (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="relative aspect-video w-full rounded-3xl overflow-hidden bg-black border border-gray-800 shadow-2xl group">
                @if($movie->video && $movie->video->youtube_id)
                    <iframe 
                        src="https://www.youtube-nocookie.com/embed/{{ $movie->video->youtube_id }}?rel=0&modestbranding=1" 
                        title="{{ $movie->title }}"
                        class="w-full h-full border-0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                @else
                    <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center p-6 text-center">
                        <p class="text-sm font-bold text-white mb-2">Linear Broadcast Stream</p>
                        @if($targetChannel)
                            <a href="{{ route('channels.show', $targetChannel->slug) }}" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs transition">
                                Watch Live on {{ $targetChannel->name }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Title & Quick Meta -->
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-md text-xs font-black uppercase tracking-wider border {{ $movie->rights_badge_color }}">
                        {{ str_replace('_', ' ', $movie->rights_classification) }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md bg-gray-900 border border-gray-800 text-xs font-semibold text-gray-300">
                        {{ $movie->release_year }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md bg-gray-900 border border-gray-800 text-xs font-semibold text-gray-300">
                        {{ $movie->runtime_minutes }} Minutes
                    </span>
                    <span class="px-2.5 py-1 rounded-md bg-gray-900 border border-gray-800 text-xs font-semibold text-amber-400">
                        {{ $movie->genre }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    {{ $movie->title }}
                </h1>

                <p class="text-sm text-gray-300 leading-relaxed">
                    {{ $movie->synopsis }}
                </p>

                @if($movie->attribution_requirements)
                    <div class="p-4 rounded-2xl bg-gray-900/60 border border-gray-800 text-xs text-gray-400 space-y-1">
                        <span class="text-white font-bold uppercase tracking-wider text-[10px]">Official Attribution:</span>
                        <p>{{ $movie->attribution_requirements }}</p>
                    </div>
                @endif
            </div>

            <!-- Playout Channel Banner -->
            @if($targetChannel)
                <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[10px] font-extrabold text-amber-400 uppercase tracking-widest flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                            Continuous 24/7 Playout Channel
                        </span>
                        <h4 class="text-base font-bold text-white">{{ $targetChannel->name }}</h4>
                        <p class="text-xs text-gray-400">Scheduled on our linear broadcast network in real-time.</p>
                    </div>

                    <a href="{{ route('channels.show', $targetChannel->slug) }}" 
                       class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs transition shrink-0 shadow-lg shadow-amber-500/20">
                        Watch Live &rarr;
                    </a>
                </div>
            @endif
        </div>

        <!-- Right Column: Formal Rights Verification & Compliance Certificate (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="rounded-3xl bg-[#11141c] border border-gray-800 p-6 sm:p-7 space-y-6 shadow-2xl">
                <!-- Certificate Header -->
                <div class="border-b border-gray-800 pb-4 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-widest">Compliance Audit</span>
                        <h3 class="text-lg font-black text-white">Broadcast Rights Certificate</h3>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                </div>

                <!-- Verification Status -->
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 text-xs">
                    <span class="text-gray-300 font-semibold">Engine Verification Status</span>
                    <span class="font-extrabold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        {{ $movie->verification_status }}
                    </span>
                </div>

                <!-- Legal Fields Table -->
                <div class="space-y-3.5 text-xs">
                    <div class="flex items-center justify-between py-1.5 border-b border-gray-800/60">
                        <span class="text-gray-400 font-medium">Source Provider</span>
                        <span class="font-bold text-white">{{ $movie->formatted_provider_name }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-800/60">
                        <span class="text-gray-400 font-medium">Licence Name</span>
                        <span class="font-bold text-white text-right max-w-[220px] truncate">{{ $movie->licence_name }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-800/60">
                        <span class="text-gray-400 font-medium">Legal / Contract Reference</span>
                        <span class="font-mono text-amber-400">{{ $movie->licence_reference ?: 'Public Domain Statutory' }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-800/60">
                        <span class="text-gray-400 font-medium">Commercial Use Permitted</span>
                        <span class="font-bold {{ $movie->commercial_use_permitted ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $movie->commercial_use_permitted ? 'Yes (Permitted)' : 'No (Restricted)' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-800/60">
                        <span class="text-gray-400 font-medium">Redistribution Permitted</span>
                        <span class="font-bold {{ $movie->redistribution_permitted ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $movie->redistribution_permitted ? 'Yes (Permitted)' : 'No (Restricted)' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-800/60">
                        <span class="text-gray-400 font-medium">Public Performance Rights</span>
                        <span class="font-bold {{ $movie->public_performance_permitted ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $movie->public_performance_permitted ? 'Yes (Cleared)' : 'No (Restricted)' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-800/60">
                        <span class="text-gray-400 font-medium">Permitted Territories</span>
                        <span class="font-bold text-white">
                            {{ implode(', ', $movie->permitted_territories ?? ['GLOBAL']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-800/60">
                        <span class="text-gray-400 font-medium">Last Rights Audit</span>
                        <span class="font-bold text-gray-300">
                            {{ $movie->last_verified_at ? $movie->last_verified_at->toFormattedDateString() : 'Active' }}
                        </span>
                    </div>
                </div>

                <!-- External Source Proof Link -->
                @if($movie->source_url)
                    <div class="pt-2">
                        <a href="{{ $movie->source_url }}" target="_blank" rel="noopener noreferrer" 
                           class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white font-semibold text-xs border border-gray-800 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            View Repository Source Document
                        </a>
                    </div>
                @endif
            </div>

            <!-- Related Network Titles -->
            @if($relatedMovies->isNotEmpty())
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white">More on {{ $targetChannel?->name ?? 'This Network' }}</h4>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($relatedMovies as $rel)
                            <a href="{{ route('movies.show', $rel->slug) }}" class="group block rounded-xl overflow-hidden bg-gray-900 border border-gray-800 hover:border-amber-400/50 transition">
                                <img src="{{ $rel->poster_url }}" alt="{{ $rel->title }}" class="w-full aspect-video object-cover">
                                <div class="p-2.5">
                                    <p class="text-xs font-bold text-white group-hover:text-amber-400 transition truncate">{{ $rel->title }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $rel->release_year }} • {{ $rel->runtime_minutes }}m</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
