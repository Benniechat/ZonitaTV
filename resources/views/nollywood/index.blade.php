@extends('layouts.app')

@section('title', '60 Nollywood YouTube Channels & Production Brands - Zonita TV')
@section('meta_description', 'Discover 60 leading Nollywood movie channels, filmmaker-owned studios, Yoruba cinema destinations, and movie aggregators for 24/7 linear broadcasting.')

@section('content')
<div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <!-- Hero Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#161220] via-[#100d17] to-black border border-gray-800/80 p-6 sm:p-10 shadow-2xl">
        <div class="relative z-10 max-w-4xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold uppercase tracking-wider">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm2 0v8h12V6H4zm2 2h3v4H6V8zm5 0h3v4h-3V8z"/></svg>
                Zonita Nollywood Rights &amp; Acquisition Pipeline
            </div>

            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                60 Nollywood Movie Channels &amp; Production Brands
            </h1>

            <p class="text-gray-300 text-sm sm:text-base leading-relaxed">
                A verified directory of 60 contemporary Nollywood filmmaker-owned channels, Yoruba cinema destinations, Pan-African film libraries, and studio aggregators across Nigeria and Africa. Curated to power continuous 24/7 linear channels with full-length romantic dramas, box-office comedies, and cultural epics.
            </p>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-800/80">
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Total Channels</p>
                    <p class="text-2xl font-black text-white">{{ $stats['total_channels'] }} <span class="text-xs text-rose-400 font-normal">Brands</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Top Filmmakers</p>
                    <p class="text-2xl font-black text-amber-400">{{ $stats['priority_1'] }} <span class="text-xs text-gray-400 font-normal">Priority 1</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Yoruba Cinema</p>
                    <p class="text-2xl font-black text-emerald-400">{{ $stats['yoruba_count'] }} <span class="text-xs text-gray-400 font-normal">Studios</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">On Air 24/7</p>
                    <p class="text-2xl font-black text-purple-400">{{ $stats['on_air_count'] }} <span class="text-xs text-gray-400 font-normal">Broadcasting</span></p>
                </div>
            </div>
        </div>

        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Live 24/7 Featured Nollywood Playout Channels On Air -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                    Featured 24/7 Nollywood Broadcast Channels On Air
                </h2>
                <p class="text-xs text-gray-400">Broadcasting continuous Nollywood feature films in real-time with zero fast-forward or rewind.</p>
            </div>
            <a href="{{ route('channels.index', ['category' => 'movies']) }}" class="text-xs font-bold text-rose-400 hover:text-rose-300 transition">View All in Channels &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $topPicks = ['ruth-kadiri-247', 'uchenna-mbunabo-tv', 'maurice-sam-tv', 'apatatv-plus'];
            @endphp
            @foreach($topPicks as $slug)
                @if($c = $featuredChannels->get($slug))
                    <div class="group relative rounded-2xl overflow-hidden bg-[#11141c] border border-gray-800 hover:border-rose-500/50 transition duration-300 shadow-xl flex flex-col justify-between">
                        <div class="relative h-44 w-full overflow-hidden">
                            <img src="{{ $c->banner_url }}" alt="{{ $c->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#11141c] via-[#11141c]/40 to-transparent"></div>
                            <div class="absolute top-3 left-3 flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md text-[10px] font-extrabold text-rose-400 border border-rose-500/30">
                                    24/7 NOLLYWOOD
                                </span>
                            </div>
                            <div class="absolute top-3 right-3 flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-red-600/90 text-white text-[10px] font-black uppercase tracking-wider shadow">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                LIVE
                            </div>
                        </div>
                        <div class="p-5 space-y-3 flex-grow flex flex-col justify-between">
                            <div>
                                <h3 class="text-base font-bold text-white group-hover:text-rose-400 transition">{{ $c->name }}</h3>
                                <p class="text-xs text-gray-400 line-clamp-2 mt-1">{{ $c->description }}</p>
                            </div>
                            <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between">
                                <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Real-Time Linear
                                </span>
                                <a href="{{ route('channels.show', $c->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs transition shadow-lg shadow-rose-600/20">
                                    Watch Live &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Filter and Search Controls -->
    <div class="space-y-4 pt-4 border-t border-gray-800">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search Form -->
            <form action="{{ route('nollywood.index') }}" method="GET" class="relative flex-grow max-w-md">
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                @if(request('priority')) <input type="hidden" name="priority" value="{{ request('priority') }}"> @endif
                @if(request('lang')) <input type="hidden" name="lang" value="{{ request('lang') }}"> @endif
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search Nollywood channels, actors, creators..."
                       class="w-full bg-[#12151c] text-sm text-white placeholder-gray-500 pl-10 pr-4 py-3 rounded-2xl border border-gray-700/80 focus:outline-none focus:border-rose-400">
                <svg class="w-5 h-5 text-gray-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>

            <!-- Category Pills -->
            <div class="flex items-center gap-2 overflow-x-auto text-xs">
                <span class="text-gray-400 shrink-0 font-semibold">Section:</span>
                <a href="{{ route('nollywood.index', array_merge(request()->except(['category', 'page']), [])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ !request('category') ? 'bg-rose-600 text-white font-bold border-rose-500' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    All 60 Channels
                </a>
                <a href="{{ route('nollywood.index', array_merge(request()->except(['category', 'page']), ['category' => 'popular_nollywood'])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('category') === 'popular_nollywood' ? 'bg-rose-600 text-white font-bold border-rose-500' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Popular Nollywood
                </a>
                <a href="{{ route('nollywood.index', array_merge(request()->except(['category', 'page']), ['category' => 'yoruba_movies'])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('category') === 'yoruba_movies' ? 'bg-rose-600 text-white font-bold border-rose-500' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Yoruba Cinema
                </a>
                <a href="{{ route('nollywood.index', array_merge(request()->except(['category', 'page']), ['category' => 'aggregator_library'])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('category') === 'aggregator_library' ? 'bg-rose-600 text-white font-bold border-rose-500' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Movie Libraries
                </a>
                <a href="{{ route('nollywood.index', array_merge(request()->except(['category', 'page']), ['category' => 'producer_studio'])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('category') === 'producer_studio' ? 'bg-rose-600 text-white font-bold border-rose-500' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Studios &amp; Networks
                </a>
            </div>
        </div>

        <!-- Priority / Language Filter -->
        <div class="flex items-center gap-2 overflow-x-auto text-xs pt-1">
            <span class="text-gray-400 shrink-0 font-semibold">Priority:</span>
            <a href="{{ route('nollywood.index', array_merge(request()->except(['priority', 'page']), [])) }}"
               class="px-3 py-1 rounded-lg border transition {{ !request('priority') ? 'bg-gray-800 text-white font-bold border-gray-700' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-white' }}">
                All Priorities
            </a>
            <a href="{{ route('nollywood.index', array_merge(request()->except(['priority', 'page']), ['priority' => 1])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('priority') == 1 ? 'bg-amber-500/20 text-amber-300 font-bold border-amber-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-amber-400' }}">
                Priority 1: Core Filmmakers
            </a>
            <a href="{{ route('nollywood.index', array_merge(request()->except(['priority', 'page']), ['priority' => 2])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('priority') == 2 ? 'bg-blue-500/20 text-blue-300 font-bold border-blue-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-blue-400' }}">
                Priority 2: Active Producers
            </a>
            <a href="{{ route('nollywood.index', array_merge(request()->except(['priority', 'page']), ['priority' => 3])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('priority') == 3 ? 'bg-purple-500/20 text-purple-300 font-bold border-purple-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-purple-400' }}">
                Priority 3: Aggregators
            </a>

            <span class="text-gray-400 shrink-0 font-semibold ml-3">Language:</span>
            <a href="{{ route('nollywood.index', array_merge(request()->except(['lang', 'page']), [])) }}"
               class="px-3 py-1 rounded-lg border transition {{ !request('lang') ? 'bg-gray-800 text-white font-bold border-gray-700' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-white' }}">
                All Languages
            </a>
            <a href="{{ route('nollywood.index', array_merge(request()->except(['lang', 'page']), ['lang' => 'English'])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('lang') === 'English' ? 'bg-rose-500/20 text-rose-300 font-bold border-rose-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-rose-400' }}">
                English
            </a>
            <a href="{{ route('nollywood.index', array_merge(request()->except(['lang', 'page']), ['lang' => 'Yoruba'])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('lang') === 'Yoruba' ? 'bg-emerald-500/20 text-emerald-300 font-bold border-emerald-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-emerald-400' }}">
                Yoruba
            </a>
            <a href="{{ route('nollywood.index', array_merge(request()->except(['lang', 'page']), ['lang' => 'Igbo'])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('lang') === 'Igbo' ? 'bg-yellow-500/20 text-yellow-300 font-bold border-yellow-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-yellow-400' }}">
                Igbo
            </a>
        </div>
    </div>

    <!-- Production Channels Directory Grid -->
    @if($channels->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($channels as $nolly)
                <div class="group rounded-2xl overflow-hidden bg-[#12151c] border border-gray-800 hover:border-rose-500/50 transition duration-300 shadow-xl flex flex-col justify-between p-5 space-y-4">
                    
                    <!-- Top Bar: Category & Priority Badge -->
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider border {{ $nolly->priority_badge_color }}">
                                Priority {{ $nolly->lead_priority }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $nolly->status_badge_color }}">
                                {{ $nolly->formatted_status }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-white group-hover:text-rose-400 transition leading-snug">
                            {{ $nolly->name }}
                        </h3>

                        @if($nolly->creator_or_lead)
                            <p class="text-xs text-rose-400/90 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $nolly->creator_or_lead }}
                            </p>
                        @endif

                        <p class="text-[11px] text-gray-400">
                            Category: <span class="text-gray-300 font-semibold">{{ $nolly->category_label }}</span>
                        </p>
                    </div>

                    <!-- Middle Content: Genres & Features -->
                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between text-gray-400 text-[11px] border-t border-gray-800/60 pt-2.5">
                            <span>Language: <strong class="text-gray-200">{{ $nolly->primary_language }}</strong></span>
                            <span>Est. Catalogue: <strong class="text-rose-400">{{ $nolly->catalogue_size_est }}+</strong></span>
                        </div>

                        <!-- Sub-genres tags -->
                        @if(!empty($nolly->sub_genres))
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach($nolly->sub_genres as $genre)
                                    <span class="px-2 py-0.5 rounded-md bg-gray-900 border border-gray-800 text-[10px] text-gray-300 font-medium">
                                        {{ $genre }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Sample feature titles -->
                        @if(!empty($nolly->sample_feature_titles))
                            <div class="p-2.5 rounded-xl bg-gray-950/70 border border-gray-800/80 text-[11px] space-y-1">
                                <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Notable Releases:</span>
                                <p class="text-gray-200 font-medium">{{ implode(' • ', $nolly->sample_feature_titles) }}</p>
                            </div>
                        @endif

                        @if($nolly->notes)
                            <p class="text-[11px] text-gray-400 line-clamp-2 italic leading-relaxed">
                                "{{ $nolly->notes }}"
                            </p>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between gap-2 flex-wrap">
                        <a href="{{ route('channels.show', $nolly->slug) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs transition shadow-md shadow-rose-600/20">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            Watch Live &rarr;
                        </a>

                        <div class="flex items-center gap-2">
                            <a href="{{ $nolly->youtube_channel_url }}" target="_blank" rel="noopener noreferrer" 
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-600/10 hover:bg-red-600 text-red-400 hover:text-white font-bold text-xs border border-red-600/30 transition">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                YouTube
                            </a>

                            <a href="mailto:licensing@zonitatv.com?subject=Zonita%20TV%20Nollywood%20Acquisition:%20{{ urlencode($nolly->name) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white font-semibold text-xs border border-gray-700 transition">
                                Rights
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="pt-6">
            {{ $channels->links() }}
        </div>
    @else
        <div class="text-center py-16 bg-[#12151c] rounded-2xl border border-gray-800">
            <p class="text-gray-400 font-medium">No Nollywood channels matched your filter criteria.</p>
            <a href="{{ route('nollywood.index') }}" class="inline-block mt-3 px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-500 transition">
                Reset All Filters
            </a>
        </div>
    @endif

</div>
@endsection
