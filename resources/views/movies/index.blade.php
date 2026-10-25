@extends('layouts.app')

@section('title', 'Movie & Archival Cinema Directory - Zonita TV')
@section('meta_description', 'Discover verified public-domain films, historical cinema, educational documentaries, and authorized Nollywood productions broadcasting 24/7 on Zonita TV.')

@section('content')
<div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <!-- Hero Header & Rights Transparency Notice -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-900 via-[#0e121b] to-black border border-gray-800/80 p-6 sm:p-10 shadow-2xl">
        <div class="relative z-10 max-w-4xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Zonita Movie Discovery & Rights Registry
            </div>

            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                24/7 Movie Channels & Verified Film Directory
            </h1>

            <p class="text-gray-300 text-sm sm:text-base leading-relaxed">
                A legally compliant, automated catalogue of classic cinema, historical motion pictures, cultural documentaries, and authorized African feature films. Every title is verified for continuous broadcast eligibility, public performance rights, and commercial compliance.
            </p>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-800/80">
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Total Catalogue</p>
                    <p class="text-2xl font-black text-white">{{ $stats['total_movies'] }} <span class="text-xs text-amber-400 font-normal">Films</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Public Domain</p>
                    <p class="text-2xl font-black text-emerald-400">{{ $stats['public_domain'] }} <span class="text-xs text-gray-400 font-normal">Verified</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">African & Nollywood</p>
                    <p class="text-2xl font-black text-purple-400">{{ $stats['authorized_african'] }} <span class="text-xs text-gray-400 font-normal">Licensed</span></p>
                </div>
                <div class="bg-gray-950/60 rounded-2xl p-4 border border-gray-800/60">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Rights Cleared</p>
                    <p class="text-2xl font-black text-amber-400">{{ $stats['rights_cleared_percent'] }}% <span class="text-xs text-gray-400 font-normal">Compliance</span></p>
                </div>
            </div>
        </div>

        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- 3 Dedicated 24/7 Movie Playout Channels -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                    24/7 Dedicated Movie Networks On Air
                </h2>
                <p class="text-xs text-gray-400">Continuous realtime linear broadcast channels streaming right now on Zonita TV.</p>
            </div>
            <a href="{{ route('channels.index') }}" class="text-xs font-bold text-amber-400 hover:text-amber-300 transition">View All Channels &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Channel 1: Zonita Classics -->
            @if(isset($movieChannels['zonita-classics']))
                @php $c = $movieChannels['zonita-classics']; @endphp
                <div class="group relative rounded-2xl overflow-hidden bg-[#11141c] border border-gray-800 hover:border-amber-500/50 transition duration-300 shadow-xl flex flex-col justify-between">
                    <div class="relative h-44 w-full overflow-hidden">
                        <img src="{{ $c->banner_url }}" alt="{{ $c->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#11141c] via-[#11141c]/40 to-transparent"></div>
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md text-[10px] font-extrabold text-amber-400 border border-amber-500/30">
                                PUBLIC DOMAIN CLASSICS
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

            <!-- Channel 2: Zonita African Cinema -->
            @if(isset($movieChannels['zonita-african-cinema']))
                @php $c = $movieChannels['zonita-african-cinema']; @endphp
                <div class="group relative rounded-2xl overflow-hidden bg-[#11141c] border border-gray-800 hover:border-purple-500/50 transition duration-300 shadow-xl flex flex-col justify-between">
                    <div class="relative h-44 w-full overflow-hidden">
                        <img src="{{ $c->banner_url }}" alt="{{ $c->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#11141c] via-[#11141c]/40 to-transparent"></div>
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md text-[10px] font-extrabold text-purple-400 border border-purple-500/30">
                                NOLLYWOOD & AFRICAN CINEMA
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

            <!-- Channel 3: Zonita Documentaries -->
            @if(isset($movieChannels['zonita-documentaries']))
                @php $c = $movieChannels['zonita-documentaries']; @endphp
                <div class="group relative rounded-2xl overflow-hidden bg-[#11141c] border border-gray-800 hover:border-blue-500/50 transition duration-300 shadow-xl flex flex-col justify-between">
                    <div class="relative h-44 w-full overflow-hidden">
                        <img src="{{ $c->banner_url }}" alt="{{ $c->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#11141c] via-[#11141c]/40 to-transparent"></div>
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md text-[10px] font-extrabold text-blue-400 border border-blue-500/30">
                                ARCHIVAL & NATURE DOCS
                            </span>
                        </div>
                    </div>
                    <div class="p-5 space-y-3 flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white group-hover:text-blue-400 transition">{{ $c->name }}</h3>
                            <p class="text-xs text-gray-400 line-clamp-2 mt-1">{{ $c->description }}</p>
                        </div>
                        <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between">
                            <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live Real-Time Playout
                            </span>
                            <a href="{{ route('channels.show', $c->slug) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs transition shadow-lg shadow-blue-600/20">
                                Tune In Live &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="space-y-4 pt-4 border-t border-gray-800">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search Input -->
            <form action="{{ route('movies.index') }}" method="GET" class="relative flex-grow max-w-md">
                @if(request('rights')) <input type="hidden" name="rights" value="{{ request('rights') }}"> @endif
                @if(request('provider')) <input type="hidden" name="provider" value="{{ request('provider') }}"> @endif
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search titles, directors, genres, or synopsis..."
                       class="w-full bg-[#12151c] text-sm text-white placeholder-gray-500 pl-10 pr-4 py-3 rounded-2xl border border-gray-700/80 focus:outline-none focus:border-amber-400">
                <svg class="w-5 h-5 text-gray-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>

            <!-- Repository / Source Select -->
            <div class="flex items-center gap-2 overflow-x-auto text-xs">
                <span class="text-gray-400 shrink-0 font-semibold">Repository:</span>
                <a href="{{ route('movies.index', array_merge(request()->except(['provider', 'page']), [])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ !request('provider') ? 'bg-amber-500 text-black font-bold border-amber-400' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    All Repositories
                </a>
                <a href="{{ route('movies.index', array_merge(request()->except(['provider', 'page']), ['provider' => 'internet_archive'])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('provider') === 'internet_archive' ? 'bg-amber-500 text-black font-bold border-amber-400' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Internet Archive
                </a>
                <a href="{{ route('movies.index', array_merge(request()->except(['provider', 'page']), ['provider' => 'library_of_congress'])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('provider') === 'library_of_congress' ? 'bg-amber-500 text-black font-bold border-amber-400' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Library of Congress
                </a>
                <a href="{{ route('movies.index', array_merge(request()->except(['provider', 'page']), ['provider' => 'nollywood_distributor'])) }}"
                   class="px-3 py-1.5 rounded-xl border transition {{ request('provider') === 'nollywood_distributor' ? 'bg-amber-500 text-black font-bold border-amber-400' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    Nollywood Syndicates
                </a>
            </div>
        </div>

        <!-- Rights Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto text-xs pt-1">
            <span class="text-gray-400 shrink-0 font-semibold">Rights Clearance:</span>
            <a href="{{ route('movies.index', array_merge(request()->except(['rights', 'page']), [])) }}"
               class="px-3 py-1 rounded-lg border transition {{ !request('rights') ? 'bg-gray-800 text-white font-bold border-gray-700' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-white' }}">
                All Legal Classifications
            </a>
            <a href="{{ route('movies.index', array_merge(request()->except(['rights', 'page']), ['rights' => 'public_domain'])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('rights') === 'public_domain' ? 'bg-emerald-500/20 text-emerald-300 font-bold border-emerald-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-emerald-400' }}">
                Verified Public Domain
            </a>
            <a href="{{ route('movies.index', array_merge(request()->except(['rights', 'page']), ['rights' => 'authorized_distributor'])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('rights') === 'authorized_distributor' ? 'bg-purple-500/20 text-purple-300 font-bold border-purple-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-purple-400' }}">
                Authorized Distributor Agreement
            </a>
            <a href="{{ route('movies.index', array_merge(request()->except(['rights', 'page']), ['rights' => 'creative_commons'])) }}"
               class="px-3 py-1 rounded-lg border transition {{ request('rights') === 'creative_commons' ? 'bg-blue-500/20 text-blue-300 font-bold border-blue-500/50' : 'bg-gray-950 text-gray-400 border-gray-800/80 hover:text-blue-400' }}">
                Creative Commons (CC BY)
            </a>
        </div>
    </div>

    <!-- Movie Catalogue Grid -->
    @if($movies->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($movies as $movie)
                <div class="group relative rounded-2xl overflow-hidden bg-[#12151c] border border-gray-800 hover:border-amber-500/50 transition duration-300 shadow-xl flex flex-col justify-between">
                    <!-- Poster Thumbnail -->
                    <div class="relative aspect-[16/9] w-full overflow-hidden bg-gray-950">
                        <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#12151c] via-transparent to-black/30"></div>

                        <!-- Top Badges -->
                        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5 z-10">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider border {{ $movie->rights_badge_color }} backdrop-blur-md">
                                {{ str_replace('_', ' ', $movie->rights_classification) }}
                            </span>
                        </div>

                        <!-- Runtime & Year -->
                        <div class="absolute bottom-2.5 right-2.5 flex items-center gap-1.5 z-10 text-[11px] font-bold text-white bg-black/80 px-2 py-0.5 rounded backdrop-blur-md border border-gray-800">
                            <span>{{ $movie->release_year }}</span>
                            <span class="text-gray-500">•</span>
                            <span>{{ $movie->runtime_minutes }} min</span>
                        </div>
                    </div>

                    <!-- Meta Content -->
                    <div class="p-4 space-y-2.5 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1">
                                <span class="text-amber-400 font-semibold">{{ $movie->genre }}</span>
                                <span class="truncate max-w-[120px]">{{ $movie->formatted_provider_name }}</span>
                            </div>

                            <h3 class="text-base font-bold text-white group-hover:text-amber-400 transition leading-snug line-clamp-1">
                                {{ $movie->title }}
                            </h3>

                            <p class="text-xs text-gray-400 line-clamp-2 mt-1.5 leading-relaxed">
                                {{ $movie->synopsis }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between gap-2">
                            <a href="{{ route('movies.show', $movie->slug) }}" class="text-xs font-semibold text-gray-400 hover:text-white transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Audit Certificate
                            </a>

                            @if($movie->target_channel_slug)
                                <a href="{{ route('channels.show', $movie->target_channel_slug) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-[11px] transition shadow">
                                    Watch On Air
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-6">
            {{ $movies->links() }}
        </div>
    @else
        <div class="text-center py-16 bg-[#12151c] rounded-3xl border border-gray-800/80 p-8">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
            <h3 class="text-lg font-bold text-white mb-1">No Matching Film Titles Found</h3>
            <p class="text-xs text-gray-400 max-w-md mx-auto mb-4">Try adjusting your repository filter or search query to explore verified archival films.</p>
            <a href="{{ route('movies.index') }}" class="px-5 py-2 rounded-xl bg-amber-500 text-black font-bold text-xs">Reset All Filters</a>
        </div>
    @endif

    <!-- Broadcast Rights & Public Performance Principles Panel -->
    <div class="rounded-3xl bg-gray-900/40 border border-gray-800/80 p-6 sm:p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <h4 class="text-base font-bold text-white">Broadcast Governance: Distinguishing Watching vs. Retransmission</h4>
                <p class="text-xs text-gray-400">Zonita TV operates under strict intellectual property governance.</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 leading-relaxed">
            A film being freely viewable online does not automatically confer broadcasting, linear redistribution, or public performance rights. Zonita TV’s Automated Movie Discovery & Rights Engine isolates titles into three legally verified categories: (1) <strong>Statutory Public Domain Works</strong> whose copyright terms have expired under applicable national laws; (2) <strong>Open Archival Works</strong> licensed under Creative Commons Attribution for public display; and (3) <strong>Direct Syndication Agreements</strong> with independent filmmakers and Nollywood production houses for African broadcast.
        </p>
    </div>

</div>
@endsection
