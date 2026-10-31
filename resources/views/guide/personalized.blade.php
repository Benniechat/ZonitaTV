@extends('layouts.app')

@section('title', 'Personalised Viewing Guide - Zoniva TV')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header & AI Engine Status -->
    <div class="mb-10 pb-6 border-b border-gray-800 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-600/10 border border-red-500/20 text-red-400 text-xs font-semibold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    AI Personalisation Engine
                </span>
                <span class="text-xs text-gray-400">Formula R = 0.40I + 0.25V + 0.15F + 0.10L + 0.10N</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                {{ $user ? $user->name . "'s Watch Guide" : 'My Personalised Guide' }}
            </h1>
            <p class="text-sm text-gray-400 mt-1 max-w-2xl">
                Continuous ranking powered by your viewing history, category interests, language choices, and real-time broadcast stream health.
            </p>
        </div>

        <!-- Quick Summary Stats -->
        <div class="flex items-center gap-3">
            <div class="bg-gray-900/80 border border-gray-800 rounded-xl px-4 py-2.5 text-center">
                <div class="text-xl font-extrabold text-white">{{ $stats['total_recommended'] ?? $recommendedChannels->count() }}</div>
                <div class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Recommended</div>
            </div>
            <div class="bg-gray-900/80 border border-gray-800 rounded-xl px-4 py-2.5 text-center">
                <div class="text-xl font-extrabold text-red-400">{{ $stats['saved_favourites_count'] ?? $favourites->count() }}</div>
                <div class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Favourites</div>
            </div>
            @if($user)
                <a href="{{ route('profile.show') }}" class="px-4 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-xs font-semibold text-gray-200 transition">
                    Tune Preferences
                </a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-xs font-semibold text-white transition">
                    Sign In to Personalise
                </a>
            @endif
        </div>
    </div>

    <div class="space-y-12">
        <!-- Main Recommended Channels Section -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">Top Recommended Stations</h2>
                    <p class="text-xs text-gray-400">Ranked by algorithm based on your engagement and stream availability</p>
                </div>
            </div>

            @if($recommendedChannels->isEmpty())
                <div class="p-8 rounded-2xl bg-gray-900/60 border border-gray-800 text-center">
                    <p class="text-gray-400 text-sm">No recommended stations found for your region at this moment.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recommendedChannels as $channel)
                        <div class="group relative bg-[#13161c] hover:bg-[#181d24] border border-gray-800 hover:border-gray-700 rounded-xl overflow-hidden shadow-lg transition-all duration-300 flex flex-col justify-between">
                            
                            <!-- Card Header: Tag & Match Score -->
                            <div class="p-4 pb-0 flex items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-red-500/10 border border-red-500/20 text-red-400">
                                    {{ $channel->recommendation_tag ?? 'Top Pick' }}
                                </span>
                                @if(isset($channel->recommendation_score))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-gray-800 text-gray-300 border border-gray-700">
                                        {{ $channel->recommendation_score }}% Match
                                    </span>
                                @endif
                            </div>

                            <!-- Channel Hero Area -->
                            <div class="p-4 flex items-start gap-4">
                                <div class="w-14 h-14 rounded-xl bg-gray-950 border border-gray-800 flex-shrink-0 flex items-center justify-center p-2 overflow-hidden shadow">
                                    @if($channel->logo_url)
                                        <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="w-full h-full object-contain">
                                    @else
                                        <span class="text-base font-extrabold text-red-500">{{ substr($channel->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-gray-800 text-gray-300 font-bold">
                                            CH {{ $channel->channel_number ?? str_pad((string)$channel->id, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <span class="text-[10px] uppercase font-semibold text-gray-400">
                                            {{ $channel->language_code ?? 'EN' }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-white text-base truncate group-hover:text-red-400 transition">
                                        <a href="{{ route('channels.show', $channel->slug) }}">
                                            {{ $channel->name }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-1 italic">
                                        {{ $channel->recommendation_reason ?? 'Verified streaming station' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Live Now Programme Bar -->
                            <div class="px-4 py-3 mx-4 mb-4 rounded-lg bg-black/40 border border-gray-800/80">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                    <span class="text-[10px] font-bold text-red-400 uppercase tracking-wider">Live Now</span>
                                </div>
                                <div class="text-xs font-semibold text-gray-200 truncate">
                                    {{ $channel->current_programme->title ?? 'Live Station Playout' }}
                                </div>
                                @if(isset($channel->upcoming_programme) && $channel->upcoming_programme)
                                    <div class="text-[11px] text-gray-500 mt-1 truncate">
                                        Next: {{ $channel->upcoming_programme->title }}
                                    </div>
                                @endif
                            </div>

                            <!-- Footer Action -->
                            <div class="p-4 pt-3 border-t border-gray-800/80 flex items-center justify-between bg-black/20">
                                <span class="text-[11px] text-gray-500 font-medium truncate max-w-[150px]">
                                    {{ $channel->categories->pluck('name')->implode(', ') ?: 'General' }}
                                </span>
                                <a href="{{ route('channels.show', $channel->slug) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-600/90 hover:bg-red-600 text-xs font-semibold text-white transition">
                                    <span>Watch Live</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </a>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Recently Watched History -->
        @if(isset($recentHistory) && $recentHistory->isNotEmpty())
        <section>
            <div class="mb-4">
                <h2 class="text-xl font-bold text-white tracking-tight">Recently Watched</h2>
                <p class="text-xs text-gray-400">Pick up where you left off</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($recentHistory as $channel)
                    <a href="{{ route('channels.show', $channel->slug) }}" class="p-3 rounded-xl bg-gray-900/60 hover:bg-gray-800 border border-gray-800 transition flex flex-col items-center text-center group">
                        <div class="w-12 h-12 rounded-lg bg-black border border-gray-800 flex items-center justify-center p-2 mb-2 overflow-hidden group-hover:scale-105 transition">
                            @if($channel->logo_url)
                                <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="w-full h-full object-contain">
                            @else
                                <span class="text-xs font-bold text-red-500">{{ substr($channel->name, 0, 2) }}</span>
                            @endif
                        </div>
                        <span class="text-xs font-semibold text-white group-hover:text-red-400 truncate w-full">{{ $channel->name }}</span>
                        <span class="text-[10px] text-gray-500 truncate w-full mt-0.5">{{ $channel->current_programme->title ?? 'Live' }}</span>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Saved Favourites -->
        @if($favourites->isNotEmpty())
        <section>
            <div class="mb-6">
                <h2 class="text-xl font-bold text-white tracking-tight">Saved Favourites</h2>
                <p class="text-xs text-gray-400">Quick access to your bookmarked channels</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($favourites as $channel)
                    <x-channel-card :channel="$channel" />
                @endforeach
            </div>
        </section>
        @endif

        <!-- Genre Spotlights -->
        @if(isset($categoryPicks) && !empty($categoryPicks))
        <section class="space-y-8">
            <div>
                <h2 class="text-xl font-bold text-white tracking-tight">Recommended by Genre</h2>
                <p class="text-xs text-gray-400">Explore curated station lineups across popular categories</p>
            </div>

            @foreach($categoryPicks as $pick)
                <div class="bg-gray-900/30 border border-gray-800/80 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            {{ $pick['category']->name }}
                        </h3>
                        <a href="{{ route('categories.show', $pick['category']->slug) }}" class="text-xs font-semibold text-red-400 hover:text-red-300">
                            View all &rarr;
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($pick['channels'] as $ch)
                            <a href="{{ route('channels.show', $ch->slug) }}" class="p-3 rounded-xl bg-gray-950/60 hover:bg-gray-900 border border-gray-800 transition flex items-center gap-3 group">
                                <div class="w-10 h-10 rounded-lg bg-black border border-gray-800 flex items-center justify-center p-1.5 flex-shrink-0">
                                    @if($ch->logo_url)
                                        <img src="{{ $ch->logo_url }}" alt="{{ $ch->name }}" class="w-full h-full object-contain">
                                    @else
                                        <span class="text-xs font-bold text-red-500">{{ substr($ch->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-white group-hover:text-red-400 truncate">{{ $ch->name }}</div>
                                    <div class="text-[10px] text-gray-500 truncate">{{ $ch->current_programme->title ?? 'Live Now' }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>
        @endif
    </div>

</div>

@endsection
