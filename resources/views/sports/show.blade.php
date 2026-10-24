@extends('layouts.app')

@section('title', $fixture->title . ' - Watch on Zonita Sports')
@section('meta_description', 'Live football match coverage for ' . $fixture->title . '. Verified SportyTV broadcast schedule, live countdown, and official viewing options.')

@section('content')

@php
    $sporty = $fixture->sportyBroadcast;
    $isLive = $fixture->is_live_now;
    $mode = $sporty?->operating_mode ?? 'mode_a_link';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-gray-400">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <span>/</span>
        <a href="{{ route('sports.index') }}" class="hover:text-white transition">Sports</a>
        <span>/</span>
        <span class="text-gray-200">{{ $fixture->title }}</span>
    </nav>

    <!-- Main Match Stage Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Viewing Stage & Controls (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Stage Area -->
            <div class="relative w-full aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl border border-gray-800 flex items-center justify-center">
                
                @if($mode === 'mode_b_embed' && $sporty?->feed_endpoint)
                    <!-- Mode B: Authorised Embed Player (Realtime Lock) -->
                    <div class="relative w-full h-full">
                        <iframe src="{{ $sporty->feed_endpoint }}" class="w-full h-full border-0" allowfullscreen allow="autoplay; encrypted-media"></iframe>
                        <!-- Transparent interaction shield to prevent manual timeline scrubbing -->
                        <div class="absolute inset-0 z-10 cursor-default"></div>
                        <div class="absolute top-3 left-3 pointer-events-none z-20 flex items-center gap-2">
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-md bg-black/85 backdrop-blur-md border border-gray-800 text-[11px] font-bold text-white shadow-xl">
                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                <span>LIVE &bull; REAL-TIME BROADCAST</span>
                            </div>
                            <div class="hidden sm:flex items-center gap-1 px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md border border-gray-800/80 text-[10px] font-semibold text-gray-300 shadow-lg">
                                <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Seeking Locked</span>
                            </div>
                        </div>
                    </div>
                @elseif($mode === 'mode_c_rebroadcast' && $sporty?->feed_endpoint)
                    <!-- Mode C: Authorised Rebroadcast Stream (Zero Scrubbing / Pure Live) -->
                    <div class="relative w-full h-full bg-black flex items-center justify-center group" id="sports-player-wrapper">
                        <video id="sports-player" class="w-full h-full object-contain pointer-events-none" autoplay playsinline poster="{{ $thumbnail }}"></video>
                        <div class="absolute inset-0 z-10 cursor-pointer" id="sports-click-shield"></div>

                        <div class="absolute top-3 left-3 pointer-events-none z-20 flex items-center gap-2">
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-md bg-black/85 backdrop-blur-md border border-gray-800 text-[11px] font-bold text-white shadow-xl">
                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                <span>ON-AIR LIVE &bull; REAL-TIME</span>
                            </div>
                            <div class="hidden sm:flex items-center gap-1 px-2.5 py-1 rounded-md bg-black/80 backdrop-blur-md border border-gray-800/80 text-[10px] font-semibold text-gray-300 shadow-lg">
                                <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Seeking Locked</span>
                            </div>
                        </div>

                        <div class="absolute bottom-3 right-3 z-20 flex items-center gap-2">
                            <button type="button" id="sports-mute-btn" class="p-2 rounded-xl bg-black/80 hover:bg-black border border-gray-800 text-white backdrop-blur-md transition shadow-lg text-xs" title="Toggle Audio">
                                Audio Toggle
                            </button>
                            <button type="button" id="sports-fs-btn" class="p-2 rounded-xl bg-black/80 hover:bg-black border border-gray-800 text-white backdrop-blur-md transition shadow-lg text-xs" title="Fullscreen">
                                Fullscreen
                            </button>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const v = document.getElementById('sports-player');
                            const muteBtn = document.getElementById('sports-mute-btn');
                            const fsBtn = document.getElementById('sports-fs-btn');
                            const wrapper = document.getElementById('sports-player-wrapper');
                            const shield = document.getElementById('sports-click-shield');

                            const toggleMute = () => { if (v) v.muted = !v.muted; };
                            const toggleFs = () => {
                                if (!document.fullscreenElement) { wrapper?.requestFullscreen?.(); }
                                else { document.exitFullscreen?.(); }
                            };

                            muteBtn?.addEventListener('click', toggleMute);
                            shield?.addEventListener('click', toggleMute);
                            fsBtn?.addEventListener('click', toggleFs);
                            shield?.addEventListener('dblclick', toggleFs);
                        });
                    </script>
                @else
                    <!-- Mode A: Official-Link Discovery Stage -->
                    <div class="relative w-full h-full bg-gradient-to-br from-red-950/40 via-[#0d1017] to-black p-8 sm:p-12 flex flex-col justify-between">
                        
                        <!-- Top Pill -->
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                Mode A: Official Broadcaster Discovery
                            </span>

                            @if($isLive)
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-red-600 text-white animate-pulse">
                                    LIVE IN PLAY
                                </span>
                            @endif
                        </div>

                        <!-- Center Face-Off Display -->
                        <div class="flex items-center justify-around py-4">
                            <!-- Home -->
                            <div class="flex flex-col items-center text-center max-w-[140px]">
                                <img src="{{ $fixture->homeTeam?->logo }}" class="w-20 h-20 sm:w-24 sm:h-24 object-contain drop-shadow-xl mb-3">
                                <h3 class="text-sm sm:text-base font-extrabold text-white">{{ $fixture->homeTeam?->name }}</h3>
                            </div>

                            <!-- VS / Scores -->
                            <div class="flex flex-col items-center px-4">
                                @if($isLive && ($fixture->home_score !== null))
                                    <div class="text-4xl sm:text-5xl font-black text-white font-mono tracking-wider drop-shadow">
                                        {{ $fixture->home_score }} - {{ $fixture->away_score }}
                                    </div>
                                    <span class="text-xs text-red-400 font-bold uppercase mt-1">{{ $fixture->match_clock ?: 'Live Clock' }}</span>
                                @else
                                    <span class="text-2xl sm:text-3xl font-black text-gray-600 tracking-widest mb-1">VS</span>
                                    <span class="text-xs font-mono font-bold text-amber-400">{{ $fixture->lagos_formatted }}</span>
                                @endif
                            </div>

                            <!-- Away -->
                            <div class="flex flex-col items-center text-center max-w-[140px]">
                                <img src="{{ $fixture->awayTeam?->logo }}" class="w-20 h-20 sm:w-24 sm:h-24 object-contain drop-shadow-xl mb-3">
                                <h3 class="text-sm sm:text-base font-extrabold text-white">{{ $fixture->awayTeam?->name }}</h3>
                            </div>
                        </div>

                        <!-- Bottom Action Callout -->
                        <div class="bg-gray-900/80 border border-gray-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                <h4 class="text-xs font-bold text-white">Confirmed Broadcaster: SportyTV</h4>
                                <p class="text-[11px] text-gray-400 mt-0.5">Stream is licensed and provided by SportyTV for Nigerian viewers.</p>
                            </div>

                            @if($sporty && $sporty->isConfirmed())
                                <a href="{{ $sporty->viewing_url }}" target="_blank" rel="noopener noreferrer" 
                                   class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-red-600/30 flex items-center gap-2 shrink-0">
                                    <span>Watch on SportyTV</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @endif
                        </div>

                    </div>
                @endif

            </div>

            <!-- Match Information Card -->
            <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-8 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase text-red-500 tracking-wider block mb-1">
                            {{ $fixture->competition?->name }} &bull; Matchday {{ $fixture->matchday }}
                        </span>
                        <h1 class="text-2xl font-black text-white">{{ $fixture->title }}</h1>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-gray-900 border border-gray-700 text-xs font-bold text-gray-300">
                        {{ strtoupper($fixture->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-gray-500 uppercase text-[10px] block">Kickoff (WAT)</span>
                        <span class="font-bold text-white">{{ $fixture->lagos_kickoff ? $fixture->lagos_kickoff->format('g:i A') : 'TBD' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase text-[10px] block">Date</span>
                        <span class="font-bold text-white">{{ $fixture->lagos_kickoff ? $fixture->lagos_kickoff->format('M j, Y') : 'TBD' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase text-[10px] block">Venue</span>
                        <span class="font-bold text-white">{{ $fixture->venue ?: 'Official Stadium' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase text-[10px] block">Coverage</span>
                        <span class="font-bold text-amber-400">SportyTV Verified</span>
                    </div>
                </div>

                <!-- Transparency & Compliance Notice -->
                <div class="p-3.5 rounded-xl bg-gray-900/60 border border-gray-800 text-[11px] text-gray-400 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>
                        Zonita TV acts as an automated sports discovery engine connecting viewers with authorized sports broadcasts. Content rights belong to their respective holders. If you believe material violates your rights, you can <a href="{{ route('legal.report') }}" class="text-amber-400 hover:underline">file a content notice</a>.
                    </span>
                </div>
            </div>

        </div>

        <!-- Right Sidebar (1 Col): Competition Matrix & Upcoming Matches -->
        <div class="space-y-6">
            
            <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    More in {{ $fixture->competition?->name }}
                </h3>

                <div class="space-y-3">
                    @forelse($relatedFixtures as $rel)
                        <a href="{{ route('sports.show', $rel->id) }}" class="block p-3 rounded-xl bg-gray-900/60 hover:bg-gray-800/80 border border-gray-800 transition">
                            <div class="text-[10px] text-gray-500 mb-1">{{ $rel->lagos_formatted }}</div>
                            <div class="text-xs font-bold text-white truncate">{{ $rel->title }}</div>
                            <div class="mt-1 flex items-center justify-between text-[10px]">
                                <span class="text-gray-400 font-mono">{{ $rel->homeTeam?->tla }} vs {{ $rel->awayTeam?->tla }}</span>
                                <span class="text-amber-400 font-semibold">SportyTV &rarr;</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-gray-500 italic py-2">No other fixtures scheduled for this matchday.</p>
                    @endforelse
                </div>
            </div>

            <!-- Live 24/7 Football Channel Promotion -->
            <div class="bg-gradient-to-br from-red-950/40 to-[#121620] border border-red-500/30 rounded-2xl p-6 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-red-600/20 border border-red-500/30 flex items-center justify-center text-red-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-white">Zonita Football 24/7</h4>
                <p class="text-xs text-gray-300">Tune into the continuous 24-hour virtual linear channel featuring continuous African and European football action.</p>
                <a href="{{ route('channels.show', 'zonita-football-24-7') }}" class="inline-block px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider transition shadow">
                    Watch Channel Live
                </a>
            </div>

        </div>

    </div>

</div>

@endsection
