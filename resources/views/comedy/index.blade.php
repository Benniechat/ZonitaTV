@extends('layouts.app')

@section('title', 'Zonita Comedy TV — 24/7 Nigerian Stand-Up, Skits & Animated Comedy')

@section('content')
<div class="min-h-screen bg-[#0a0c10] text-gray-100 pb-20">
    <!-- Hero Header -->
    <div class="relative bg-gradient-to-b from-[#131722] via-[#0d1017] to-[#0a0c10] border-b border-gray-800/80 pt-10 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Zonita Comedy Playout Network
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">
                        African Comedy & <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Humour 24/7</span>
                    </h1>
                    <p class="mt-3 text-sm sm:text-base text-gray-400 max-w-3xl leading-relaxed">
                        Discover 70 top Nigerian stand-up comedians, viral skit makers, sitcom studios, and animation houses broadcasting non-stop laughter across 10 dedicated 24/7 virtual comedy channels.
                    </p>
                </div>

                <!-- Live Network Stats -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="bg-[#151922] border border-gray-800 rounded-xl px-4 py-2.5 text-center min-w-[100px]">
                        <span class="block text-xl font-black text-white">{{ $stats['total_creators'] ?? 70 }}</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Creators</span>
                    </div>
                    <div class="bg-[#151922] border border-gray-800 rounded-xl px-4 py-2.5 text-center min-w-[100px]">
                        <span class="block text-xl font-black text-amber-400">{{ $stats['virtual_channels_count'] ?? 10 }}</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">24/7 Channels</span>
                    </div>
                    <div class="bg-[#151922] border border-gray-800 rounded-xl px-4 py-2.5 text-center min-w-[100px]">
                        <span class="block text-xl font-black text-orange-400">{{ number_format($stats['est_catalogue_total'] ?? 18000) }}+</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Skits & Specials</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 10 Dedicated Virtual 24/7 Comedy Broadcast Channels -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                    10 Dedicated 24/7 Virtual Comedy Channels
                </h2>
                <p class="text-xs sm:text-sm text-gray-400 mt-1">
                    Continuous linear television broadcasts synchronized in UTC with zero scrubbing and real-time electronic programme guides.
                </p>
            </div>
            <a href="{{ route('guide.index') }}" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-amber-400 hover:text-amber-300 transition">
                <span>View Full EPG Guide &rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            @forelse($virtualChannels as $vChannel)
                @php
                    $currentEntry = $vChannel->scheduleEntries->first();
                    $currentTitle = $currentEntry?->title ?? '24/7 Non-Stop Comedy Broadcast';
                @endphp
                <div class="bg-[#121620] border border-gray-800/80 hover:border-amber-500/50 rounded-2xl overflow-hidden transition-all duration-200 flex flex-col justify-between group shadow-lg hover:shadow-amber-500/10">
                    <div class="relative aspect-video bg-gray-900 overflow-hidden">
                        <img src="{{ $vChannel->banner_url ?? 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=800&q=80' }}" 
                             alt="{{ $vChannel->name }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121620] via-black/40 to-transparent"></div>
                        
                        <div class="absolute top-2 left-2 flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-black/70 backdrop-blur-md border border-white/10 text-[10px] font-bold text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            LIVE ON AIR
                        </div>

                        <div class="absolute bottom-2 left-2 right-2">
                            <h3 class="text-sm font-black text-white truncate drop-shadow">{{ $vChannel->name }}</h3>
                        </div>
                    </div>

                    <div class="p-3.5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="text-[11px] text-gray-400 line-clamp-2 mb-2 leading-relaxed">
                                {{ $vChannel->description }}
                            </div>
                            
                            <div class="bg-[#0b0d13] p-2 rounded-xl border border-gray-800 mb-3">
                                <span class="block text-[9px] font-bold uppercase tracking-wider text-amber-400">Now Playing</span>
                                <p class="text-xs font-semibold text-gray-200 truncate mt-0.5" title="{{ $currentTitle }}">
                                    {{ $currentTitle }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('channels.show', $vChannel->slug) }}" 
                           class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black text-xs font-black transition shadow-md shadow-orange-500/20 group-hover:shadow-amber-500/30">
                            <span>Watch Live Now</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-gray-400">
                    Virtual comedy channels are synchronizing.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14">
        <div class="bg-[#121620] border border-gray-800 rounded-2xl p-4 sm:p-6 mb-8">
            <form action="{{ route('comedy.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 justify-between items-stretch lg:items-center">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Search comedian, character (e.g. Sabinus, Taaooma, Tegwolo, Layi Wasabi, AY)..." 
                           class="w-full bg-[#0a0c10] text-sm text-gray-200 placeholder-gray-500 pl-10 pr-4 py-2.5 rounded-xl border border-gray-700 focus:outline-none focus:border-amber-500 transition">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Category Pills -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('comedy.index', request()->except(['category', 'page'])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ !request('category') ? 'bg-amber-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                        All ({{ $stats['total_creators'] }})
                    </a>
                    <a href="{{ route('comedy.index', array_merge(request()->except('page'), ['category' => 'standup_comedy'])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('category') === 'standup_comedy' ? 'bg-amber-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                        Stand-Up ({{ $stats['standup_count'] }})
                    </a>
                    <a href="{{ route('comedy.index', array_merge(request()->except('page'), ['category' => 'skit_creators'])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('category') === 'skit_creators' ? 'bg-amber-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                        Viral Skits ({{ $stats['skit_count'] }})
                    </a>
                    <a href="{{ route('comedy.index', array_merge(request()->except('page'), ['category' => 'animated_comedy'])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('category') === 'animated_comedy' ? 'bg-amber-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                        Animation & Toons
                    </a>
                    <a href="{{ route('comedy.index', array_merge(request()->except('page'), ['category' => 'production_studios'])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('category') === 'production_studios' ? 'bg-amber-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                        Studios & Sitcoms
                    </a>
                    <a href="{{ route('comedy.index', array_merge(request()->except('page'), ['priority' => 1])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('priority') == 1 ? 'bg-red-500 text-white' : 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20' }}">
                        ★ Headliners ({{ $stats['top_priority_count'] }})
                    </a>
                </div>
            </form>
        </div>

        <!-- Section Heading -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-black text-white tracking-tight">
                    Comedy Creator & Performance Directory ({{ $creators->total() }})
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Official channels, catalog sizes, performance genres, sample acts, and rights-licensing governance.
                </p>
            </div>
            @if(request()->hasAny(['q', 'category', 'priority', 'lang']))
                <a href="{{ route('comedy.index') }}" class="text-xs font-bold text-gray-400 hover:text-white transition">
                    Clear Filters &times;
                </a>
            @endif
        </div>

        <!-- Creators Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($creators as $creator)
                <div class="bg-[#121620] border border-gray-800/80 hover:border-gray-700 rounded-2xl p-5 flex flex-col justify-between transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5 group">
                    <div>
                        <!-- Header / Badges -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-amber-500/20 to-orange-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 font-black text-base shrink-0 group-hover:scale-105 transition">
                                    {{ mb_substr($creator->name, 0, 2) }}
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-white group-hover:text-amber-400 transition leading-snug">
                                        {{ $creator->name }}
                                    </h3>
                                    @if($creator->creator_or_brand && $creator->creator_or_brand !== $creator->name)
                                        <p class="text-xs text-gray-400 truncate max-w-[200px]">
                                            {{ $creator->creator_or_brand }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if($creator->lead_priority === 1)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 shrink-0">
                                    ★ Top Tier
                                </span>
                            @endif
                        </div>

                        <!-- Category & Language Bar -->
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-gray-800 text-gray-300">
                                {{ $creator->category_label }}
                            </span>
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-gray-900 border border-gray-800 text-gray-400">
                                {{ $creator->primary_language }}
                            </span>
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-gray-900 border border-gray-800 text-amber-400/90">
                                {{ $creator->catalogue_size_est }}+ Videos
                            </span>
                        </div>

                        <!-- Notes / Profile -->
                        <p class="text-xs text-gray-400 line-clamp-2 mb-3 leading-relaxed">
                            {{ $creator->notes }}
                        </p>

                        <!-- Sub-genres Tags -->
                        @if($creator->sub_genres && count($creator->sub_genres) > 0)
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach(array_slice($creator->sub_genres, 0, 3) as $genre)
                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-gray-900/80 text-gray-400 border border-gray-800">
                                        #{{ $genre }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Sample Skits / Acts -->
                        @if($creator->sample_skit_titles && count($creator->sample_skit_titles) > 0)
                            <div class="bg-[#0b0e14] p-2.5 rounded-xl border border-gray-800/80 mb-3">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Sample Sketches & Specials</span>
                                <ul class="text-[11px] text-gray-300 space-y-0.5">
                                    @foreach(array_slice($creator->sample_skit_titles, 0, 2) as $title)
                                        <li class="truncate flex items-center gap-1.5">
                                            <span class="w-1 h-1 rounded-full bg-amber-400 shrink-0"></span>
                                            <span class="truncate">{{ $title }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Footer with Links and Licensing Status -->
                    <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $creator->status_badge_color }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ $creator->formatted_status }}
                        </span>

                        <a href="{{ $creator->youtube_channel_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center gap-1 text-xs font-bold text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-700 px-3 py-1.5 rounded-xl transition">
                            <span>YouTube</span>
                            <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-[#121620] rounded-2xl border border-gray-800">
                    <p class="text-base font-bold text-gray-300">No comedy creators found matching your search.</p>
                    <p class="text-xs text-gray-500 mt-1">Try another search keyword or reset the category filters.</p>
                    <a href="{{ route('comedy.index') }}" class="inline-block mt-4 px-4 py-2 bg-amber-500 text-black font-bold text-xs rounded-xl">
                        View All 70 Creators
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($creators->hasPages())
            <div class="mt-8">
                {{ $creators->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
