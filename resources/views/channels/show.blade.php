@extends('layouts.app')

@section('title', $channel->name . ' - Watch on Zoniva TV')
@section('meta_description', $channel->tagline ?? $channel->description)

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-gray-400 mb-4">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <span>/</span>
        <a href="{{ route('channels.index') }}" class="hover:text-white transition">Channels</a>
        <span>/</span>
        <span class="text-gray-200">{{ $channel->name }}</span>
    </nav>

    <!-- Zonita Virtual Television Pilot Stations Quick-Bar -->
    <div class="mb-6 flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider shrink-0 mr-1">Virtual Stations:</span>
        @php
            $pilotSlugs = [
                'zonita-football-24-7' => 'Football 24/7',
                'zonita-gospel-naija' => 'Gospel Naija',
                'zonita-music-world' => 'Music World',
                'zonita-documentaries' => 'Documentaries',
                'zonita-tech-pulse' => 'Tech Pulse',
            ];
        @endphp
        @foreach($pilotSlugs as $pSlug => $pTitle)
            <a href="{{ route('channels.show', $pSlug) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold shrink-0 transition border {{ $channel->slug === $pSlug ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-600/30' : 'bg-[#12151c] border-gray-800 text-gray-400 hover:text-white hover:border-gray-700' }}">
               {{ $pTitle }}
            </a>
        @endforeach
    </div>

    <!-- Main Player Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Video Player & Primary Details (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Video Stage Area -->
            <div class="relative w-full aspect-video bg-black rounded-2xl overflow-hidden shadow-2xl border border-gray-800">
                @if($playback['allowed'])

                    @if($playback['mode'] === 'direct_stream')
                        {{-- Mode A: Authorised Direct Playback via HLS.js --}}
                        <video id="zoniva-player" autoplay  class="w-full h-full" controls autoplay playsinline poster="{{ $channel->banner_url ?? '' }}" ></video>
                        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const video = document.getElementById('zoniva-player');
                                const streamUrl = "{{ $playback['stream']->stream_url }}";
                                if (Hls.isSupported()) {
                                    const hls = new Hls({ enableWorker: true });
                                    hls.loadSource(streamUrl);
                                    hls.attachMedia(video);
                                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                    video.src = streamUrl;
                                }
                            });
                        </script>

                    @elseif($playback['mode'] === 'embed_player')
                        {{-- Mode B: Official Broadcaster Embed Player & 24/7 Linear Playout Engine --}}
                        <div class="relative w-full h-full overflow-hidden bg-black flex items-center justify-center group/embed">
                            @if(isset($linearPlayout) && !empty($linearPlayout['playlist_queue']))
                                {{-- 24/7 Continuous Automated Playout Engine --}}
                                <div x-data="{
                                        playlist: {{ json_encode($linearPlayout['playlist_queue']) }},
                                        currentIndex: 0,
                                        player: null,
                                        isMuted: false,
                                        currentProg() {
                                            return this.playlist[this.currentIndex] || {};
                                        },
                                        init() {
                                            this.loadYouTubeIframeAPI();
                                        },
                                        loadYouTubeIframeAPI() {
                                            if (!window.YT) {
                                                const tag = document.createElement('script');
                                                tag.src = 'https://www.youtube.com/iframe_api';
                                                const firstScriptTag = document.getElementsByTagName('script')[0];
                                                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                                                window.onYouTubeIframeAPIReady = () => this.initPlayer();
                                            } else {
                                                this.initPlayer();
                                            }
                                        },
                                        initPlayer() {
                                            const prog = this.currentProg();
                                            this.player = new YT.Player('linear-yt-frame', {
                                                videoId: prog.video_id,
                                                playerVars: {
                                                    'autoplay': 1,
                                                    'controls': 1,
                                                    'modestbranding': 1,
                                                    'rel': 0,
                                                    'start': prog.start_seconds || 0,
                                                    'playsinline': 1
                                                },
                                                events: {
                                                    'onReady': (e) => {
                                                        try { e.target.playVideo(); } catch(err) {}
                                                    },
                                                    'onStateChange': (e) => this.onPlayerStateChange(e),
                                                    'onError': (e) => this.handleStreamError(e)
                                                }
                                            });
                                        },
                                        handleStreamError(event) {
                                            console.warn('Playback error code:', event.data, 'Requesting verified reserve replacement from Zonita Engine...');
                                            const prog = this.currentProg();
                                            fetch('/api/playback/report-failure', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    channel_id: {{ $channel->id }},
                                                    video_id: prog.video_id,
                                                    error_code: String(event.data)
                                                })
                                            })
                                            .then(r => r.json())
                                            .then(data => {
                                                if (data.status === 'recovered' && data.replacement) {
                                                    console.log('Zonita Engine deployed reserve replacement:', data.replacement.video_id);
                                                    this.playlist[this.currentIndex] = data.replacement;
                                                    if (this.player && this.player.loadVideoById) {
                                                        this.player.loadVideoById({
                                                            videoId: data.replacement.video_id,
                                                            startSeconds: 0
                                                        });
                                                    }
                                                } else {
                                                    this.playNext();
                                                }
                                            })
                                            .catch(err => {
                                                console.error('Engine communication error, auto-advancing to next scheduled programme:', err);
                                                this.playNext();
                                            });
                                        },
                                        onPlayerStateChange(event) {
                                            // When video ends (YT.PlayerState.ENDED === 0), seamlessly roll into next scheduled programme
                                            if (event.data === 0) {
                                                this.playNext();
                                            }
                                        },
                                        playNext() {
                                            this.currentIndex = (this.currentIndex + 1) % this.playlist.length;
                                            const nextProg = this.currentProg();
                                            if (this.player && this.player.loadVideoById) {
                                                this.player.loadVideoById({
                                                    videoId: nextProg.video_id,
                                                    startSeconds: nextProg.start_seconds || 0
                                                });
                                            }
                                        },
                                        switchProgramme(idx) {
                                            this.currentIndex = idx;
                                            const prog = this.currentProg();
                                            if (this.player && this.player.loadVideoById) {
                                                this.player.loadVideoById({
                                                    videoId: prog.video_id,
                                                    startSeconds: prog.start_seconds || 0
                                                });
                                            }
                                        }
                                     }"
                                     class="relative w-full h-full overflow-hidden">

                                    <!-- YouTube Playout Container -->
                                    <div class="relative w-full h-full pointer-events-auto">
                                        <div id="linear-yt-frame" class="absolute inset-0 w-full h-full scale-[1.02] transform origin-center"></div>
                                    </div>

                                    <!-- Top-Right Live Station Bug -->
                                    <div class="absolute top-3 right-3 pointer-events-none z-20 flex items-center gap-1.5 px-3 py-1 rounded-md bg-black/85 backdrop-blur-md border border-gray-800 text-[11px] font-bold text-white shadow-xl">
                                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                        <span>24/7 LINEAR ON-AIR</span>
                                    </div>

                                    <!-- Bottom Playout On-Air Lower Third Bug -->
                                    <div class="absolute bottom-3 left-3 z-20 max-w-md hidden sm:flex items-center gap-3 px-3.5 py-2 rounded-xl bg-black/85 backdrop-blur-md border border-gray-800 text-xs shadow-2xl">
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping shrink-0"></div>
                                        <div class="truncate">
                                            <div class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold">Playing Now</div>
                                            <div class="text-white font-bold truncate text-[11px]" x-text="currentProg().title"></div>
                                        </div>
                                    </div>

                                    <!-- Alternative / Source Button -->
                                    @if($channel->official_destination_url)
                                        <div class="absolute bottom-3 right-3 z-20">
                                            <a href="{{ $channel->official_destination_url }}" target="_blank" rel="noopener noreferrer" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-black/80 hover:bg-black text-[11px] font-medium text-gray-300 hover:text-white border border-gray-700/80 backdrop-blur-md transition shadow-lg">
                                                <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></svg>
                                                <span>Source Feed</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                @php
                                    $streamUrl = $playback['stream']->stream_url ?? '';
                                    $embedCode = $playback['stream']->embed_code ?? '';
                                    $isYouTube = str_contains($streamUrl, 'youtube.com') || str_contains($streamUrl, 'youtu.be') || str_contains($embedCode, 'youtube.com');
                                    
                                    if ($isYouTube && $streamUrl && !str_contains($streamUrl, 'autoplay=')) {
                                        $separator = str_contains($streamUrl, '?') ? '&' : '?';
                                        $streamUrl .= $separator . 'autoplay=1&mute=0&controls=1&modestbranding=1&rel=0&playsinline=1&enablejsapi=1';
                                    }
                                @endphp

                                @if($isYouTube)
                                    {{-- Stealth YouTube Container: Zoomed slightly to hide top header watermark and branding overlay --}}
                                    <div class="relative w-full h-full overflow-hidden pointer-events-auto">
                                        <iframe 
                                            src="{{ $streamUrl }}" 
                                            class="absolute inset-0 w-full h-full border-0 scale-[1.02] transform origin-center" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen
                                            loading="eager">
                                        </iframe>

                                        <!-- Top-right watermark shield with Zoniva Live badge to maintain clean interface -->
                                        <div class="absolute top-3 right-3 pointer-events-none z-10 flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-black/85 backdrop-blur-md border border-gray-800 text-[11px] font-semibold text-white shadow-xl">
                                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                            <span>LIVE BROADCAST</span>
                                        </div>

                                        @if($channel->official_destination_url)
                                            <!-- Bottom subtle external feed option if embed has browser restrictions -->
                                            <div class="absolute bottom-3 right-3 z-10">
                                                <a href="{{ $channel->official_destination_url }}" target="_blank" rel="noopener noreferrer" 
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-black/80 hover:bg-black text-[11px] font-medium text-gray-300 hover:text-white border border-gray-700/80 backdrop-blur-md transition shadow-lg">
                                                    <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></svg>
                                                    <span>Alternative Stream</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($playback['stream']->embed_code)
                                    <div class="w-full h-full">
                                        {!! $playback['stream']->embed_code !!}
                                    </div>
                                @elseif($playback['stream']->stream_url)
                                    <iframe src="{{ $playback['stream']->stream_url }}" class="w-full h-full border-0" allowfullscreen allow="autoplay; encrypted-media"></iframe>
                                @endif
                            @endif
                        </div>

                    @elseif($playback['mode'] === 'zoniva_original')
                        {{-- Mode D: Zoniva Originals --}}
                        <video id="zoniva-player" class="w-full h-full" controls autoplay playsinline poster="{{ $channel->banner_url ?? '' }}"></video>
                        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const video = document.getElementById('zoniva-player');
                                const streamUrl = "{{ $playback['stream']->stream_url }}";
                                if (Hls.isSupported()) {
                                    const hls = new Hls({ enableWorker: true });
                                    hls.loadSource(streamUrl);
                                    hls.attachMedia(video);
                                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                    video.src = streamUrl;
                                }
                            });
                        </script>

                    @else
                        {{-- Mode C: Authorised External Broadcaster Destination --}}
                        <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-b from-[#141822] to-[#0b0d12]">
                            <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="h-16 w-auto mb-4 drop-shadow">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20 mb-3">
                                Official Broadcaster Viewing Destination
                            </span>
                            <h3 class="text-xl sm:text-2xl font-bold text-white mb-2">Watch {{ $channel->name }} on Official Portal</h3>
                            <p class="text-xs sm:text-sm text-gray-400 max-w-md mb-6 leading-relaxed">
                                In compliance with broadcasting licensing rules, this broadcast is delivered via {{ $channel->broadcaster_name ?: 'the broadcaster\'s official platform' }}.
                            </p>
                            <a href="{{ $playback['destination_url'] }}" target="_blank" rel="noopener noreferrer" 
                               class="inline-flex items-center gap-2 px-8 py-3.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-600/30 transition transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></svg>
                                Launch Official Stream
                            </a>
                        </div>
                    @endif

                @else
                    {{-- Rights Restricted / Expired Fallback --}}
                    <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gray-950">
                        <svg class="w-12 h-12 text-amber-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <h3 class="text-lg font-bold text-white mb-1">Viewing Unavailable</h3>
                        <p class="text-xs text-gray-400 max-w-sm">{{ $playback['reason'] ?? 'This channel is currently undergoing rights verification.' }}</p>
                    </div>
                @endif
            </div>

            <!-- Player Transparency Notice & Rights Flag -->
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 rounded-xl bg-[#12151c]/90 border border-gray-800 text-[11px] text-gray-400">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>Platform Notice:</strong> Playback delivered via official embedded player API. Trademarks & content remain property of respective broadcasters.</span>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('legal.disclaimer') }}" class="text-gray-400 hover:text-white underline">Disclaimer</a>
                    <a href="{{ route('legal.report', ['url' => url()->current(), 'ref' => $channel->slug]) }}" class="text-red-400 hover:text-red-300 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Report Stream</span>
                    </a>
                </div>
            </div>

            <!-- Channel Header & Actions -->
            <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-800">
                    <div class="flex items-center space-x-4">
                        <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="w-14 h-14 object-contain rounded-xl bg-gray-900 border border-gray-800 p-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl sm:text-2xl font-bold text-white">{{ $channel->name }}</h1>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Verified</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $channel->tagline ?: ($channel->broadcaster_name ?? 'Broadcaster') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <!-- Favourite Button -->
                        <button type="button" onclick="toggleFav({{ $channel->id }})" id="fav-btn" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-gray-900 hover:bg-gray-800 border border-gray-700 text-gray-300 transition">
                            <span id="fav-icon" class="{{ $isFavourited ? 'text-red-500' : 'text-gray-400' }}">&#9829;</span>
                            <span id="fav-text">{{ $isFavourited ? 'Saved' : 'Favourite' }}</span>
                        </button>

                        @if($channel->broadcaster_website)
                            <a href="{{ $channel->broadcaster_website }}" target="_blank" rel="noopener noreferrer" 
                               class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-xs font-semibold bg-gray-900 hover:bg-gray-800 border border-gray-700 text-gray-300 transition">
                                Website
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Description & Details -->
                <div class="pt-6 space-y-4">
                    <p class="text-sm text-gray-300 leading-relaxed">{{ $channel->description }}</p>

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-gray-500">Categories:</span>
                        @foreach($channel->categories as $c)
                            <a href="{{ route('categories.show', $c->slug) }}" class="px-2.5 py-1 rounded-md bg-gray-800 text-gray-300 hover:text-white transition">
                                {{ $c->name }}
                            </a>
                        @endforeach
                        <span class="text-gray-500 ml-2">Country:</span>
                        <span class="px-2 py-0.5 rounded bg-gray-900 text-gray-300 border border-gray-800 uppercase font-semibold">{{ $channel->country_code }}</span>
                        <span class="text-gray-500 ml-2">Language:</span>
                        <span class="px-2 py-0.5 rounded bg-gray-900 text-gray-300 border border-gray-800 uppercase">{{ $channel->language_code }}</span>
                    </div>

                    <!-- Rights & Compliance Disclosure -->
                    <div class="mt-4 p-4 rounded-xl bg-gray-900/50 border border-gray-800/80 text-[11px] text-gray-400 leading-relaxed">
                        <strong class="text-gray-300">Content Rights & Distribution Note:</strong>
                        This channel listing is indexed by Zoniva TV in compliance with our content verification framework. Zoniva TV does not alter, intercept, or rebroadcast third-party feeds without authorization.
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar: What's On Channel & Related (1 Col) -->
        <div class="space-y-6">
            
            <!-- Channel Schedule & EPG Guide -->
            <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        Programme Guide
                    </h3>
                    @if(isset($zonitaEpg) && !empty($zonitaEpg['reserve_status']))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            {{ $zonitaEpg['reserve_status']['current_reserve_count'] }} in Reserve
                        </span>
                    @endif
                </div>

                @if(isset($zonitaEpg) && $zonitaEpg['upcoming']->isNotEmpty())
                    {{-- Zonita AI Engine Scheduled Timeline --}}
                    <div class="space-y-3">
                        @if($zonitaEpg['current'])
                            <div class="p-3.5 rounded-xl bg-red-950/20 border border-red-500/30">
                                <div class="flex items-center justify-between text-[11px] text-red-400 font-semibold mb-1">
                                    <span>ON-AIR NOW</span>
                                    <span class="text-gray-400">{{ $zonitaEpg['current']->start_time->format('h:i A') }} - {{ $zonitaEpg['current']->end_time->format('h:i A') }}</span>
                                </div>
                                <h4 class="text-xs font-bold text-white">{{ $zonitaEpg['current']->title }}</h4>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] uppercase tracking-wider font-bold bg-gray-800 text-gray-300">
                                        {{ str_replace('_', ' ', $zonitaEpg['current']->format_type) }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">{{ round($zonitaEpg['current']->duration_seconds / 60) }} mins</span>
                                </div>
                            </div>
                        @endif

                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pt-1">Up Next Timeline</div>

                        <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                            @foreach($zonitaEpg['upcoming'] as $entry)
                                <div class="p-3 rounded-xl bg-gray-900/60 hover:bg-gray-900 border border-gray-800/70 transition">
                                    <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1">
                                        <span class="font-medium text-gray-300">{{ $entry->start_time->format('h:i A') }}</span>
                                        <span class="text-[10px] text-gray-500">{{ round($entry->duration_seconds / 60) }}m</span>
                                    </div>
                                    <h4 class="text-xs font-semibold text-white line-clamp-1">{{ $entry->title }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] uppercase tracking-wider font-semibold bg-gray-800/80 text-gray-400">
                                            {{ str_replace('_', ' ', $entry->format_type) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($channel->programmes->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($channel->programmes as $prog)
                            <div class="p-3 rounded-xl bg-gray-900/60 border border-gray-800/70">
                                <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1">
                                    <span>{{ $prog->start_time->format('h:i A') }} - {{ $prog->end_time->format('h:i A') }}</span>
                                    @if($prog->is_live)
                                        <span class="text-red-400 font-bold uppercase">Now</span>
                                    @endif
                                </div>
                                <h4 class="text-xs font-semibold text-white">{{ $prog->title }}</h4>
                                <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">{{ $prog->description }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-500">Electronic programme guide updating for this station.</p>
                @endif
            </div>

            <!-- Report a Problem Card -->
            <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Stream Quality</h3>
                        <p class="text-[11px] text-gray-400">Broken stream or technical issue?</p>
                    </div>
                    <button @click="open = !open" class="text-xs font-semibold text-red-400 hover:text-red-300">
                        <span x-text="open ? 'Close' : 'Report Issue'"></span>
                    </button>
                </div>

                <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-gray-800">
                    <form action="{{ route('channels.report', $channel->slug) }}" method="POST" class="space-y-3">
                        @csrf
                        <select name="issue_type" class="w-full bg-gray-900 text-xs text-gray-200 p-2.5 rounded-lg border border-gray-700">
                            <option value="broken_stream">Stream Not Playing / Offline</option>
                            <option value="sound_issue">Audio / Sound Glitch</option>
                            <option value="wrong_channel">Wrong Content Displayed</option>
                            <option value="other">Other Feedback</option>
                        </select>
                        <textarea name="description" rows="2" placeholder="Briefly describe what happened..." required 
                                  class="w-full bg-gray-900 text-xs text-gray-200 p-2.5 rounded-lg border border-gray-700"></textarea>
                        <button type="submit" class="w-full py-2 bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-white rounded-lg transition">
                            Submit Report
                        </button>
                    </form>
                </div>
            </div>

            <!-- Related Channels -->
            @if($relatedChannels->isNotEmpty())
            <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">
                    Related Channels
                </h3>

                <div class="space-y-3">
                    @foreach($relatedChannels as $rel)
                        <a href="{{ route('channels.show', $rel->slug) }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-gray-900/80 transition group">
                            <img src="{{ $rel->logo_url }}" alt="{{ $rel->name }}" class="w-10 h-10 object-contain rounded-lg bg-gray-900 border border-gray-800 p-1">
                            <div class="flex-grow">
                                <h4 class="text-xs font-bold text-white group-hover:text-red-400 transition">{{ $rel->name }}</h4>
                                <p class="text-[10px] text-gray-400">{{ $rel->categories->first()?->name ?? 'General' }} &bull; {{ $rel->country_code }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

    </div>

</div>

<script>
    function toggleFav(channelId) {
        fetch(`/channel/${channelId}/favourite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const icon = document.getElementById('fav-icon');
                const text = document.getElementById('fav-text');
                if (data.favourited) {
                    icon.classList.add('text-red-500');
                    icon.classList.remove('text-gray-400');
                    text.innerText = 'Saved';
                } else {
                    icon.classList.remove('text-red-500');
                    icon.classList.add('text-gray-400');
                    text.innerText = 'Favourite';
                }
            } else if (data.error === 'Unauthenticated') {
                alert('Please sign in or register to save channels to your favourites.');
            }
        })
        .catch(err => console.error(err));
    }
</script>

@endsection
