@extends('layouts.app')

@section('title', 'Gospel Movie & Drama Channel Directory - Zonita TV')
@section('meta_description', 'Discover 60 leading gospel movie production ministries and Christian film channels across Nigeria, Ghana, and Africa for 24/7 broadcasting.')

@section('content')
<div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <!-- Hero Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#121622] via-[#0d1017] to-black border border-gray-800/80 p-6 sm:p-10 shadow-2xl">
        <div class="relative z-10 max-w-4xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                African Christian Cinema Registry
            </div>

            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Gospel Film Productions &amp; Ministries Directory
            </h1>

            <p class="text-gray-300 text-sm sm:text-base leading-relaxed">
                A verified directory of 60 gospel film productions, evangelical drama ministries, and Christian filmmakers across Nigeria and Africa. Curated to populate continuous 24/7 linear channels with full-length features, biblical dramas, deliverance movies, and family faith cinema.
            </p>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-800/80">
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Total Ministries</p>
                    <p class="text-2xl font-black text-white">{{ $stats['total_channels'] }} <span class="text-xs text-amber-400 font-normal">Productions</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Core Full-Length</p>
                    <p class="text-2xl font-black text-emerald-400">{{ $stats['priority_1'] }} <span class="text-xs text-gray-400 font-normal">Priority 1</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">English Subtitled</p>
                    <p class="text-2xl font-black text-blue-400">{{ $stats['english_subtitled_count'] }} <span class="text-xs text-gray-400 font-normal">Global Reach</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Active Broadcasting</p>
                    <p class="text-2xl font-black text-purple-400">{{ $stats['active_syndicated'] }} <span class="text-xs text-gray-400 font-normal">On Air 24/7</span></p>
                </div>
            </div>
        </div>

        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Live 24/7 Gospel Playout Channels On Air -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                    Live 24/7 Christian Drama Channels On Air
                </h2>
                <p class="text-xs text-gray-400">Broadcasting continuous gospel feature films in real-time with zero fast-forward or rewind.</p>
            </div>
            <a href="{{ route('channels.index') }}" class="text-xs font-bold text-amber-400 hover:text-amber-300 transition">View All Channels &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Channel 1: African Gospel Film Network (AGFN) -->
            @if(isset($gospelChannels['african-gospel-cinema-tv']))
                @php $c = $gospelChannels['african-gospel-cinema-tv']; @endphp
                <div class="group relative rounded-2xl overflow-hidden bg-[#11141c] border border-gray-800 hover:border-amber-500/50 transition duration-300 shadow-xl flex flex-col justify-between">
                    <div class="relative h-44 w-full overflow-hidden">
                        <img src="{{ $c->banner_url }}" alt="{{ $c->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#11141c] via-[#11141c]/40 to-transparent"></div>
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md text-[10px] font-extrabold text-amber-400 border border-amber-500/30">
                                24/7 GOSPEL CINEMA
                            </span>
                        </div>
                    </div>
                    <div class="p-5 space-y-3 flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white group-hover:text-amber-400 transition">{{ $c->name }}</h3>
                            <p class="text-xs text-gray-400 line-clamp-2 mt-1">{{ $c->description }}</p>
                        </div>
                        <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between">
                            <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live Real-Time Playout
                            </span>
                            <a href="{{ route('channels.show', $c->slug) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs transition shadow-lg shadow-amber-500/20">
                                Tune In Live &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Channel 2: Mount Zion Gospel Drama TV -->
            @if(isset($gospelChannels['mount-zion-gospel-drama-tv']))
                @php $c = $gospelChannels['mount-zion-gospel-drama-tv']; @endphp
                <div class="group relative rounded-2xl overflow-hidden bg-[#11141c] border border-gray-800 hover:border-purple-500/50 transition duration-300 shadow-xl flex flex-col justify-between">
                    <div class="relative h-44 w-full overflow-hidden">
                        <img src="{{ $c->banner_url }}" alt="{{ $c->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#11141c] via-[#11141c]/40 to-transparent"></div>
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md text-[10px] font-extrabold text-purple-400 border border-purple-500/30">
                                MOUNT ZION & DAMILOLA
                            </span>
                        </div>
                    </div>
                    <div class="p-5 space-y-3 flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white group-hover:text-purple-400 transition">{{ $c->name }}</h3>
                            <p class="text-xs text-gray-400 line-clamp-2 mt-1">{{ $c->description }}</p>
                        </div>
                        <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between">
                            <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live Real-Time Playout
                            </span>
                            <a href="{{ route('channels.show', $c->slug) }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs transition shadow-lg shadow-purple-600/20">
                                Tune In Live &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Channel 3: Prem TV Gospel Movies -->
            @if(isset($gospelChannels['prem-tv-gospel-movies']))
                @php $c = $gospelChannels['prem-tv-gospel-movies']; @endphp
                <div class="group relative rounded-2xl overflow-hidden bg-[#11141c] border border-gray-800 hover:border-emerald-500/50 transition duration-300 shadow-xl flex flex-col justify-between">
                    <div class="relative h-44 w-full overflow-hidden">
                        <img src="{{ $c->banner_url }}" alt="{{ $c->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#11141c] via-[#11141c]/40 to-transparent"></div>
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md text-[10px] font-extrabold text-emerald-400 border border-emerald-500/30">
                                FEMI ADEBILE / FEJOSBABA
                            </span>
                        </div>
                    </div>
                    <div class="p-5 space-y-3 flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white group-hover:text-emerald-400 transition">{{ $c->name }}</h3>
                            <p class="text-xs text-gray-400 line-clamp-2 mt-1">{{ $c->description }}</p>
                        </div>
                        <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between">
                            <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live Real-Time Playout
                            </span>
                            <a href="{{ route('channels.show', $c->slug) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs transition shadow-lg shadow-emerald-600/20">
                                Tune In Live &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Filter and Search Controls -->
    <div class="space-y-4 pt-4 border-t border-gray-800">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search Form -->
            <form action="{{ route('gospel.index') }}" method="GET" class="relative flex-grow max-w-md">
                @if(request('priority')) <input type="hidden" name="priority" value="{{ request('priority') }}"> @endif
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                @if(request('lang')) <input type="hidden" name="lang" value="{{ request('lang') }}"> @endif
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search ministries, founders, cities..."
                       class="w-full bg-[#12151c] text-sm text-white placeholder-gray-500 pl-10 pr-4 py-3 rounded-2xl border border-gray-700/80 focus:outline-none focus:border-amber-400">
                <svg class="w-5 h-5 text-gray-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>

            <!-- Priority Filter Pills -->
            <div class="flex items-center gap-2 overflow-x-auto text-xs">
                <span class="text-gray-400 shrink-0 font-semibold">Priority:</span>
                <a href="{{ route('gospel.index', array_merge(request()->except(['priority', 'page']), [])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ !request('priority') ? 'bg-amber-500 text-black font-bold border-amber-400' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    All 60 Ministries
                </a>
                <a href="{{ route('gospel.index', array_merge(request()->except(['priority', 'page']), ['priority' => 1])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('priority') == 1 ? 'bg-amber-500 text-black font-bold border-amber-400' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Priority 1: Core Full-Length
                </a>
                <a href="{{ route('gospel.index', array_merge(request()->except(['priority', 'page']), ['priority' => 2])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('priority') == 2 ? 'bg-amber-500 text-black font-bold border-amber-400' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Priority 2: Active Producers
                </a>
                <a href="{{ route('gospel.index', array_merge(request()->except(['priority', 'page']), ['priority' => 3])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('priority') == 3 ? 'bg-amber-500 text-black font-bold border-amber-400' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Priority 3: Regional & Emerging
                </a>
            </div>
        </div>

        <!-- Language / Status Filter -->
        <div class="flex items-center gap-2 overflow-x-auto text-xs pt-1">
            <span class="text-gray-400 shrink-0 font-semibold">Language:</span>
            <a href="{{ route('gospel.index', array_merge(request()->except(['lang', 'page']), [])) }}"
               class="px-3 py-1 rounded-lg border transition {{ !request('lang') ? 'bg-gray-800 text-white font-bold border-gray-700' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-white' }}">
                All Languages
            </a>
            <a href="{{ route('gospel.index', array_merge(request()->except(['lang', 'page']), ['lang' => 'English'])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('lang') === 'English' ? 'bg-blue-500/20 text-blue-300 font-bold border-blue-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-blue-400' }}">
                English Primary
            </a>
            <a href="{{ route('gospel.index', array_merge(request()->except(['lang', 'page']), ['lang' => 'Yoruba'])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('lang') === 'Yoruba' ? 'bg-purple-500/20 text-purple-300 font-bold border-purple-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-purple-400' }}">
                Yoruba with English Subtitles
            </a>
        </div>
    </div>

    <!-- Production Ministries Directory Grid -->
    @if($productions->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($productions as $prod)
                <div class="group rounded-2xl overflow-hidden bg-[#12151c] border border-gray-800 hover:border-amber-500/50 transition duration-300 shadow-xl flex flex-col justify-between p-5 space-y-4">
                    
                    <!-- Top Bar: Name & Priority Badge -->
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider border {{ $prod->priority_badge_color }}">
                                Priority {{ $prod->lead_priority }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $prod->status_badge_color }}">
                                {{ $prod->formatted_status }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-white group-hover:text-amber-400 transition leading-snug">
                            {{ $prod->name }}
                        </h3>

                        @if($prod->founder_or_minister)
                            <p class="text-xs text-amber-400/90 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $prod->founder_or_minister }}
                            </p>
                        @endif

                        @if($prod->headquarters_city)
                            <p class="text-[11px] text-gray-400">
                                Location: <span class="text-gray-300 font-semibold">{{ $prod->headquarters_city }}, {{ $prod->country_code }}</span>
                            </p>
                        @endif
                    </div>

                    <!-- Middle Content: Genres & Features -->
                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between text-gray-400 text-[11px] border-t border-gray-800/60 pt-2.5">
                            <span>Language: <strong class="text-gray-200">{{ $prod->primary_language }}</strong></span>
                            <span>Est. Catalogue: <strong class="text-amber-400">{{ $prod->catalogue_size_est }}+</strong></span>
                        </div>

                        <!-- Sub-genres tags -->
                        @if(!empty($prod->sub_genres))
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach($prod->sub_genres as $genre)
                                    <span class="px-2 py-0.5 rounded-md bg-gray-900 border border-gray-800 text-[10px] text-gray-300 font-medium">
                                        {{ $genre }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Sample feature titles -->
                        @if(!empty($prod->sample_feature_titles))
                            <div class="p-2.5 rounded-xl bg-gray-950/70 border border-gray-800/80 text-[11px] space-y-1">
                                <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Notable Features:</span>
                                <p class="text-gray-200 font-medium">{{ implode(' • ', $prod->sample_feature_titles) }}</p>
                            </div>
                        @endif

                        @if($prod->notes)
                            <p class="text-[11px] text-gray-400 line-clamp-2 italic leading-relaxed">
                                "{{ $prod->notes }}"
                            </p>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between gap-2 flex-wrap">
                        <a href="{{ route('channels.show', $prod->slug) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs transition shadow-md shadow-amber-500/20">
                            <span class="w-2 h-2 rounded-full bg-black animate-pulse"></span>
                            Watch Live &rarr;
                        </a>

                        <div class="flex items-center gap-2">
                            <a href="{{ $prod->youtube_channel_url }}" target="_blank" rel="noopener noreferrer" 
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-600/10 hover:bg-red-600 text-red-400 hover:text-white font-bold text-xs border border-red-600/30 transition">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                YouTube
                            </a>

                            <a href="mailto:licensing@zonitatv.com?subject=Zonita%20TV%20Broadcasting%20Syndication:%20{{ urlencode($prod->name) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white font-semibold text-xs border border-gray-700 transition">
                                Rights
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="pt-6">
            {{ $productions->links() }}
        </div>
    @else
        <div class="text-center py-16 bg-[#12151c] rounded-3xl border border-gray-800/80 p-8">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
            <h3 class="text-lg font-bold text-white mb-1">No Matching Ministries Found</h3>
            <p class="text-xs text-gray-400 max-w-md mx-auto mb-4">Try clearing filters or search terms to browse all 60 gospel film productions.</p>
            <a href="{{ route('gospel.index') }}" class="px-5 py-2 rounded-xl bg-amber-500 text-black font-bold text-xs">Reset All Filters</a>
        </div>
    @endif

    <!-- Broadcasting Rights Protocol Card -->
    <div class="rounded-3xl bg-gray-900/40 border border-gray-800/80 p-6 sm:p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h4 class="text-base font-bold text-white">Zonita TV Gospel Syndication Protocol</h4>
                <p class="text-xs text-gray-400">Transforming public video channels into licensed television broadcast streams.</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 leading-relaxed">
            The presence of a gospel movie on YouTube provides a discovery pathway, not permission for television retransmission. For every partner ministry, Zonita TV establishes written, non-exclusive syndication agreements specifying: (1) 24/7 continuous linear streaming rights, (2) commercial sponsorship clearance, (3) permitted territorial boundaries across Nigeria and global diaspora audiences, and (4) promotional clip rights. Ministries receive broader distribution, brand protection, and agreed revenue-sharing arrangements.
        </p>
    </div>

</div>
@endsection
