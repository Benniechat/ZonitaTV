@extends('layouts.app')

@section('title', 'Electronic Programme Guide (EPG) - Zonita TV')
@section('meta_description', 'View live and upcoming television programmes, match coverage, sermons, and documentaries across verified networks.')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-8 pb-6 border-b border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-600/10 text-red-400 text-xs font-semibold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                Electronic Programme Guide (EPG)
            </div>
            <h1 class="text-3xl font-extrabold text-white">Zonita TV Electronic Guide</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">Continuous schedules powered by XMLTV integration and channel metadata.</p>
        </div>

        <div class="flex items-center space-x-2 text-xs">
            <span class="text-gray-500">Local Time:</span>
            <span class="px-3 py-1.5 rounded-lg bg-gray-900 border border-gray-800 text-white font-mono font-semibold">
                {{ now()->format('h:i A (T)') }}
            </span>
        </div>
    </div>

    <!-- Category Filter Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-6 text-xs border-b border-gray-800/80 mb-8">
        <a href="{{ route('guide.index') }}" 
           class="px-3.5 py-2 rounded-xl border transition {{ !request('category') ? 'bg-red-600 text-white border-red-500 font-bold' : 'bg-[#12151c] text-gray-400 border-gray-800 hover:text-white' }}">
            All Categories
        </a>
        @foreach($categories as $c)
            <a href="{{ route('guide.index', ['category' => $c->slug]) }}" 
               class="px-3.5 py-2 rounded-xl border whitespace-nowrap transition {{ request('category') === $c->slug ? 'bg-red-600 text-white border-red-500 font-bold' : 'bg-[#12151c] text-gray-400 border-gray-800 hover:text-white' }}">
                {{ $c->name }}
            </a>
        @endforeach
    </div>

    <!-- Live Now Section -->
    <div class="mb-14">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                On Air Right Now
            </h2>
            <span class="text-xs text-gray-400">{{ $liveProgrammes->count() }} active broadcast channels</span>
        </div>

        @if($liveProgrammes->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($liveProgrammes as $prog)
                    @php
                        $now = now();
                        $start = $prog->start_time;
                        $end = $prog->end_time;
                        $totalDuration = max(1, $start->diffInSeconds($end));
                        $elapsed = max(0, $start->diffInSeconds($now));
                        $progressPercent = min(100, max(5, round(($elapsed / $totalDuration) * 100)));
                    @endphp

                    <div class="group bg-[#12151c] hover:bg-[#161a22] border border-gray-800/80 hover:border-gray-700 rounded-2xl p-5 transition flex flex-col justify-between shadow-lg">
                        <div>
                            <!-- Header with Channel & Times -->
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="flex items-center space-x-2.5">
                                    <img src="{{ asset($prog->channel->logo_path ?? '/images/logo/zonitatvLogo.png') }}"
                                         alt="{{ $prog->channel->name ?? '' }}" 
                                         class="w-8 h-8 object-contain rounded-lg bg-gray-900 border border-gray-800 p-0.5">
                                    <span class="text-xs font-bold text-gray-300">{{ $prog->channel->name ?? 'Channel' }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-600 text-white uppercase tracking-wider">LIVE</span>
                            </div>

                            <!-- Programme Info -->
                            <h3 class="font-bold text-white text-base mb-1 group-hover:text-red-400 transition line-clamp-1">
                                {{ $prog->title }}
                            </h3>
                            <p class="text-xs text-gray-400 line-clamp-2 leading-relaxed mb-4">
                                {{ $prog->description }}
                            </p>
                        </div>

                        <!-- Timeline Progress & Action -->
                        <div>
                            <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1.5 font-mono">
                                <span>{{ $prog->start_time->format('h:i A') }}</span>
                                <span class="text-gray-500 font-sans">{{ $prog->duration_minutes }} min</span>
                                <span>{{ $prog->end_time->format('h:i A') }}</span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-800 h-1.5 rounded-full overflow-hidden mb-4">
                                <div class="bg-red-500 h-full rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                            </div>

                            @if($prog->channel)
                                <a href="{{ route('channels.show', $prog->channel->slug) }}" 
                                   class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 bg-red-600/10 hover:bg-red-600 border border-red-500/20 hover:border-red-600 text-red-400 hover:text-white text-xs font-semibold rounded-xl transition">
                                    <span>Watch Broadcast</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center bg-[#12151c] rounded-2xl border border-gray-800">
                <p class="text-xs text-gray-400">No scheduled programmes currently running in this category.</p>
            </div>
        @endif
    </div>

    <!-- Upcoming Schedules -->
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                Coming Up Next (12 Hours)
            </h2>
        </div>

        @if($upcomingProgrammes->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($upcomingProgrammes->take(18) as $prog)
                    <div class="bg-[#101217] border border-gray-800/80 rounded-2xl p-5 hover:border-gray-700 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-emerald-400 font-semibold font-mono">{{ $prog->start_time->format('h:i A') }}</span>
                                <span class="text-[11px] text-gray-500">{{ $prog->duration_minutes }} mins</span>
                            </div>

                            <h3 class="font-bold text-white text-sm mb-1 line-clamp-1">{{ $prog->title }}</h3>
                            <p class="text-xs text-red-400 font-medium mb-1.5">{{ $prog->channel->name ?? 'Channel' }}</p>
                            <p class="text-xs text-gray-400 line-clamp-2">{{ $prog->description }}</p>
                        </div>

                        <div class="pt-4 mt-3 border-t border-gray-800/80 flex items-center justify-between">
                            <span class="text-[10px] text-gray-500 uppercase">{{ $prog->channel->country_code ?? 'NGA' }}</span>
                            @if($prog->channel)
                                <a href="{{ route('channels.show', $prog->channel->slug) }}" class="text-xs text-gray-400 hover:text-white">
                                    Tune In &rarr;
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center bg-[#12151c] rounded-2xl border border-gray-800">
                <p class="text-xs text-gray-400">Upcoming schedules will populate automatically from active EPG grabbers.</p>
            </div>
        @endif
    </div>

</div>

@endsection
