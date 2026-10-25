@extends('layouts.app')

@section('title', 'Islamic Movies, Hausa Kannywood, Yoruba Drama & 24/7 Channels - Zonita TV')
@section('meta_description', 'Watch 24/7 continuous Islamic television: Hausa Kannywood films, Yoruba Islamic dramas, Qur\'an recitations, lectures, and documentaries in real-time on Zonita TV.')

@section('content')
<div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

    <!-- Hero Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0c1a16] via-[#091310] to-black border border-emerald-900/60 p-6 sm:p-10 shadow-2xl">
        <div class="relative z-10 max-w-4xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                Verified Islamic Broadcasting Syndicate
            </div>

            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Islamic Cinema, Hausa Kannywood, Yoruba Drama &amp; 24/7 Channels
            </h1>

            <p class="text-gray-300 text-sm sm:text-base leading-relaxed">
                A legally compliant, AI-powered broadcasting network featuring 8 dedicated 24/7 virtual channels and an extensive directory of 28+ verified Islamic media organisations, Kannywood film studios, Yoruba Islamic filmmakers, and renowned scholars across Nigeria and the Muslim world. Broadcast in real-time with zero scrubbing or fast-forward.
            </p>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-4 border-t border-emerald-950/80">
                <div class="bg-gray-950/70 rounded-2xl p-4 border border-emerald-900/40">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Verified Providers</p>
                    <p class="text-2xl font-black text-white">{{ $stats['total_providers'] }} <span class="text-xs text-emerald-400 font-normal">Studios &amp; Networks</span></p>
                </div>
                <div class="bg-gray-950/70 rounded-2xl p-4 border border-emerald-900/40">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Verified Owners</p>
                    <p class="text-2xl font-black text-emerald-400">{{ $stats['verified_owners'] }} <span class="text-xs text-gray-400 font-normal">Confirmed</span></p>
                </div>
                <div class="bg-gray-950/70 rounded-2xl p-4 border border-emerald-900/40">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Broadcast Partners</p>
                    <p class="text-2xl font-black text-amber-400">{{ $stats['active_broadcasting'] }} <span class="text-xs text-gray-400 font-normal">Active On Air</span></p>
                </div>
                <div class="bg-gray-950/70 rounded-2xl p-4 border border-emerald-900/40">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Virtual Channels</p>
                    <p class="text-2xl font-black text-cyan-400">{{ $stats['virtual_channels_count'] }} <span class="text-xs text-gray-400 font-normal">24/7 Linear</span></p>
                </div>
                <div class="bg-gray-950/70 rounded-2xl p-4 border border-emerald-900/40 col-span-2 sm:col-span-1">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Scheduled Events</p>
                    <p class="text-2xl font-black text-purple-400">{{ number_format($stats['total_upcoming_schedules']) }} <span class="text-xs text-gray-400 font-normal">7-Day Rolling</span></p>
                </div>
            </div>
        </div>

        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- 8 Live 24/7 Virtual Channels Grid -->
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-black text-white flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                    8 Dedicated 24/7 Islamic Virtual Channels On Air
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">Continuous wall-clock linear broadcasting with automated programme transition and zero fast-forward.</p>
            </div>
            <a href="{{ route('channels.index') }}" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 transition flex items-center gap-1">
                Explore All Network Channels &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $channelCards = [
                    'zonita-islamic-cinema' => [
                        'badge' => 'ISLAMIC CINEMA',
                        'badge_color' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        'btn_color' => 'bg-amber-500 hover:bg-amber-400 text-black',
                        'accent' => 'hover:border-amber-500/50',
                    ],
                    'zonita-arewa-movies' => [
                        'badge' => 'AREWA & KANNYWOOD',
                        'badge_color' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                        'btn_color' => 'bg-emerald-500 hover:bg-emerald-400 text-black',
                        'accent' => 'hover:border-emerald-500/50',
                    ],
                    'zonita-yoruba-islamic' => [
                        'badge' => 'YORUBA DRAMA',
                        'badge_color' => 'bg-teal-500/20 text-teal-400 border-teal-500/30',
                        'btn_color' => 'bg-teal-500 hover:bg-teal-400 text-black',
                        'accent' => 'hover:border-teal-500/50',
                    ],
                    'zonita-quran' => [
                        'badge' => 'QURAN & HARAMAIN',
                        'badge_color' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
                        'btn_color' => 'bg-cyan-500 hover:bg-cyan-400 text-black',
                        'accent' => 'hover:border-cyan-500/50',
                    ],
                    'zonita-islamic-knowledge' => [
                        'badge' => 'KNOWLEDGE & TAFSIR',
                        'badge_color' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                        'btn_color' => 'bg-indigo-500 hover:bg-indigo-400 text-white',
                        'accent' => 'hover:border-indigo-500/50',
                    ],
                    'zonita-islamic-documentaries' => [
                        'badge' => 'ISLAMIC HISTORY & DOCS',
                        'badge_color' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
                        'btn_color' => 'bg-rose-500 hover:bg-rose-400 text-white',
                        'accent' => 'hover:border-rose-500/50',
                    ],
                    'zonita-nasheed' => [
                        'badge' => 'NASHEED & SPIRITUAL',
                        'badge_color' => 'bg-violet-500/20 text-violet-400 border-violet-500/30',
                        'btn_color' => 'bg-violet-500 hover:bg-violet-400 text-white',
                        'accent' => 'hover:border-violet-500/50',
                    ],
                    'zonita-islamic-family' => [
                        'badge' => 'FAMILY & ETHICS',
                        'badge_color' => 'bg-lime-500/20 text-lime-400 border-lime-500/30',
                        'btn_color' => 'bg-lime-500 hover:bg-lime-400 text-black',
                        'accent' => 'hover:border-lime-500/50',
                    ],
                ];
            @endphp

            @foreach($channelCards as $slug => $theme)
                @if($channel = $islamicChannels->get($slug))
                    @php
                        $sched = $channelSchedules[$slug] ?? null;
                        $currentProg = $sched['current'] ?? null;
                        $nextProg = $sched['next'] ?? null;
                    @endphp
                    <div class="group relative rounded-2xl overflow-hidden bg-[#10141a] border border-gray-800 {{ $theme['accent'] }} transition duration-300 shadow-xl flex flex-col justify-between">
                        <!-- Thumbnail / Banner -->
                        <div class="relative h-44 w-full overflow-hidden bg-black">
                            <img src="{{ $channel->banner_url }}" alt="{{ $channel->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#10141a] via-[#10141a]/40 to-transparent"></div>
                            <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase border {{ $theme['badge_color'] }} backdrop-blur-md">
                                    {{ $theme['badge'] }}
                                </span>
                            </div>
                            <div class="absolute top-3 right-3 flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-red-600/90 text-white text-[10px] font-black uppercase tracking-wider shadow">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                LIVE
                            </div>
                        </div>

                        <!-- Channel Meta & EPG Info -->
                        <div class="p-5 space-y-4 flex-grow flex flex-col justify-between">
                            <div class="space-y-2">
                                <h3 class="text-base font-extrabold text-white group-hover:text-emerald-400 transition leading-snug">
                                    {{ $channel->name }}
                                </h3>
                                <p class="text-xs text-gray-400 line-clamp-2">{{ $channel->description }}</p>

                                <!-- Now Playing Bar -->
                                <div class="bg-gray-950/80 rounded-xl p-2.5 border border-gray-800/80 space-y-1">
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-emerald-400 font-bold uppercase tracking-wider flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            On Air Now
                                        </span>
                                        @if($currentProg)
                                            <span class="text-gray-500">{{ \Carbon\Carbon::parse($currentProg->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($currentProg->end_time)->format('H:i') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-semibold text-gray-200 truncate">
                                        {{ $currentProg ? $currentProg->title : 'Continuous Linear Playout' }}
                                    </p>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between">
                                <span class="text-[11px] text-gray-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Realtime Sync
                                </span>
                                <a href="{{ route('channels.show', $channel->slug) }}" class="px-4 py-2 rounded-xl {{ $theme['btn_color'] }} font-extrabold text-xs transition shadow-md">
                                    Tune In Live &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Verified Provider Directory & Rights Filter Section -->
    <div class="space-y-6 pt-4">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Verified Islamic Content Providers &amp; Rights Registry
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">Discover and audit Kannywood film production studios, Yoruba Islamic filmmakers, international media houses, and scholars.</p>
            </div>
            
            <!-- Quick Filter Badges -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('islamic.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ !request()->hasAny(['type', 'channel', 'lang']) ? 'bg-emerald-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }} transition">
                    All Providers ({{ $stats['total_providers'] }})
                </a>
                <a href="{{ route('islamic.index', ['channel' => 'zonita-arewa-movies']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request('channel') === 'zonita-arewa-movies' ? 'bg-emerald-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }} transition">
                    Hausa / Kannywood
                </a>
                <a href="{{ route('islamic.index', ['channel' => 'zonita-yoruba-islamic']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request('channel') === 'zonita-yoruba-islamic' ? 'bg-emerald-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }} transition">
                    Yoruba Islamic
                </a>
                <a href="{{ route('islamic.index', ['type' => 'individual_scholar']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request('type') === 'individual_scholar' ? 'bg-emerald-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }} transition">
                    Scholars &amp; Lectures
                </a>
                <a href="{{ route('islamic.index', ['channel' => 'zonita-quran']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request('channel') === 'zonita-quran' ? 'bg-emerald-500 text-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }} transition">
                    Qur'an &amp; Haramain
                </a>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-[#10141d] rounded-2xl p-5 border border-gray-800 shadow-lg">
            <form action="{{ route('islamic.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Search Directory</label>
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by studio, filmmaker, city, or title..." 
                            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500 transition">
                        @if(request('q'))
                            <a href="{{ route('islamic.index') }}" class="absolute right-3 top-2 text-gray-400 hover:text-white text-xs">&times;</a>
                        @endif
                    </div>
                </div>

                <!-- Provider Type -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Provider Type</label>
                    <select name="type" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 transition">
                        <option value="">All Provider Types</option>
                        <option value="production_company" {{ request('type') === 'production_company' ? 'selected' : '' }}>Production Company</option>
                        <option value="television_network" {{ request('type') === 'television_network' ? 'selected' : '' }}>Television Network</option>
                        <option value="independent_filmmaker" {{ request('type') === 'independent_filmmaker' ? 'selected' : '' }}>Independent Filmmaker</option>
                        <option value="media_organisation" {{ request('type') === 'media_organisation' ? 'selected' : '' }}>Media Organisation</option>
                        <option value="individual_scholar" {{ request('type') === 'individual_scholar' ? 'selected' : '' }}>Individual Scholar</option>
                        <option value="quranic_institution" {{ request('type') === 'quranic_institution' ? 'selected' : '' }}>Qur'anic Institution</option>
                        <option value="nasheed_artist" {{ request('type') === 'nasheed_artist' ? 'selected' : '' }}>Nasheed Artist</option>
                    </select>
                </div>

                <!-- Language Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Primary Language</label>
                    <select name="lang" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 transition">
                        <option value="">All Languages</option>
                        <option value="Hausa" {{ request('lang') === 'Hausa' ? 'selected' : '' }}>Hausa (Kannywood)</option>
                        <option value="Yoruba" {{ request('lang') === 'Yoruba' ? 'selected' : '' }}>Yoruba</option>
                        <option value="Arabic" {{ request('lang') === 'Arabic' ? 'selected' : '' }}>Arabic</option>
                        <option value="English" {{ request('lang') === 'English' ? 'selected' : '' }}>English</option>
                    </select>
                </div>

                <!-- Submit / Reset -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-black font-extrabold text-xs py-2.5 px-4 rounded-xl transition shadow">
                        Filter Registry
                    </button>
                    @if(request()->hasAny(['q', 'type', 'channel', 'lang', 'status', 'classification']))
                        <a href="{{ route('islamic.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs py-2.5 px-3 rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Providers List Grid -->
        @if($providers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($providers as $provider)
                    <div class="rounded-2xl bg-[#10141d] border border-gray-800/80 p-5 space-y-4 hover:border-gray-700 transition shadow-lg flex flex-col justify-between">
                        <div class="space-y-3">
                            <!-- Top row: Type & Verification Badge -->
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase {{ $provider->badge_class }}">
                                    {{ $provider->source_classification }}
                                </span>
                                <span class="text-[11px] font-bold text-gray-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $provider->verification_status === 'VERIFIED_OWNER' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                    {{ str_replace('_', ' ', $provider->verification_status) }}
                                </span>
                            </div>

                            <!-- Provider Name & Title -->
                            <div>
                                <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                                    {{ $provider->name }}
                                    @if($provider->country_code)
                                        <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-300 font-normal">{{ $provider->country_code }}</span>
                                    @endif
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    <span class="capitalize">{{ str_replace('_', ' ', $provider->provider_type) }}</span>
                                    @if($provider->headquarters_city)
                                        &bull; {{ $provider->headquarters_city }}
                                    @endif
                                    &bull; <span class="text-emerald-400 font-semibold">{{ $provider->primary_language }}</span>
                                </p>
                            </div>

                            <!-- Notes / Description -->
                            @if($provider->notes)
                                <p class="text-xs text-gray-300 line-clamp-3 leading-relaxed">
                                    {{ $provider->notes }}
                                </p>
                            @endif

                            <!-- Sample Titles / Catalogue Highlights -->
                            @if(!empty($provider->sample_titles))
                                <div class="space-y-1">
                                    <p class="text-[11px] font-bold uppercase text-gray-500">Notable Productions / Works:</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($provider->sample_titles as $title)
                                            <span class="px-2 py-0.5 rounded bg-gray-900 border border-gray-800 text-[11px] text-gray-300 font-medium">
                                                {{ $title }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Footer Actions & Metadata -->
                        <div class="pt-4 border-t border-gray-800/80 space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400">Licensing Status:</span>
                                <span class="font-bold {{ in_array($provider->licensing_status, ['ACTIVE_BROADCASTING', 'AGREEMENT_SIGNED']) ? 'text-emerald-400' : 'text-amber-400' }}">
                                    {{ str_replace('_', ' ', $provider->licensing_status) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-2">
                                @if($provider->youtube_channel_url)
                                    <a href="{{ $provider->youtube_channel_url }}" target="_blank" rel="noopener noreferrer" 
                                        class="flex-1 text-center py-2 px-3 rounded-xl bg-gray-900 hover:bg-gray-800 border border-gray-800 text-xs font-bold text-gray-300 hover:text-white transition flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-red-500 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                        Inspect Source
                                    </a>
                                @endif

                                @if($provider->contact_email)
                                    <a href="mailto:{{ $provider->contact_email }}?subject=Zonita%20TV%20Broadcast%20Licensing%20Inquiry%20-%20{{ urlencode($provider->name) }}" 
                                        class="py-2 px-3 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/30 text-xs font-bold text-emerald-400 transition flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        Rights Inquiry
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="pt-6">
                {{ $providers->links() }}
            </div>
        @else
            <div class="rounded-2xl bg-[#10141d] border border-gray-800 p-12 text-center space-y-3">
                <p class="text-gray-400 text-sm">No Islamic content providers match your current search criteria.</p>
                <a href="{{ route('islamic.index') }}" class="inline-block px-4 py-2 rounded-xl bg-emerald-500 text-black font-extrabold text-xs">
                    Clear Filters
                </a>
            </div>
        @endif
    </div>

    <!-- Rights & Legal Compliance Disclosure -->
    <div class="rounded-2xl bg-[#0e121a] border border-gray-800/80 p-6 space-y-3">
        <h4 class="text-sm font-extrabold text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Broadcast Rights &amp; Regulatory Compliance Statement
        </h4>
        <p class="text-xs text-gray-400 leading-relaxed">
            Zonita TV operates strictly in accordance with copyright law and the terms of service of authorized media platforms. Public availability of video content on third-party services does not imply blanket permission for broadcast or redistribution. Zonita TV maintains rigorous verification records, restricts discovery leads from linear playout, attributes all verified copyright owners, and provides an immediate notice-and-takedown protocol. For copyright claims, rights representation, or direct licensing agreements, please contact <a href="mailto:rights@zonitatv.com" class="text-emerald-400 underline">rights@zonitatv.com</a> or visit our <a href="{{ route('legal.content-rights') }}" class="text-emerald-400 underline">Content Rights &amp; Licensing Portal</a>.
        </p>
    </div>

</div>
@endsection
