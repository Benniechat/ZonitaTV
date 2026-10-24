@props(['channel'])

@php
    $mainCategory = $channel->categories->first();
    $badgeMode = match($channel->playback_mode) {
        'direct_stream' => ['label' => 'Direct Live', 'color' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
        'embed_player' => ['label' => 'Embedded Player', 'color' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
        'zoniva_original' => ['label' => 'Zoniva Original', 'color' => 'bg-red-500/10 text-red-400 border-red-500/20'],
        default => ['label' => 'Official Portal', 'color' => 'bg-purple-500/10 text-purple-400 border-purple-500/20'],
    };

    $actionLabel = match($channel->playback_mode) {
        'direct_stream' => 'Watch Live',
        'embed_player' => 'Watch Stream',
        'zoniva_original' => 'Watch Original',
        default => 'Visit Official Stream',
    };
@endphp

<div class="group relative bg-[#13161c] hover:bg-[#181d24] border border-gray-800/80 hover:border-gray-700 rounded-xl overflow-hidden shadow-lg transition-all duration-300 flex flex-col h-full">
    
    <!-- Thumbnail & Logo Area -->
    <a href="{{ route('channels.show', $channel->slug) }}" class="relative block aspect-video w-full overflow-hidden bg-gray-950 flex items-center justify-center">
        <!-- Cinematic Category / Channel Banner Backdrop -->
        <img src="{{ $channel->banner_url }}" 
             alt="{{ $channel->name }}" 
             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-110 filter brightness-[0.70] group-hover:brightness-[0.80]"
             loading="lazy"
             onerror="this.onerror=null; this.src='{{ $channel->getCategoryBannerUrl() }}';">

        <!-- Deep Atmospheric Gradient Overlays -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#13161c] via-[#13161c]/50 to-black/30 pointer-events-none"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black/30 via-transparent to-black/30 pointer-events-none"></div>

        <!-- Channel Number & Live Status -->
        <div class="absolute top-3 left-3 z-10 flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-black/75 border border-white/10 text-[10px] font-semibold text-gray-200 backdrop-blur-md shadow-md">
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
            <span>CH {{ str_pad((string)$channel->id, 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        <!-- Mode badge -->
        <div class="absolute top-3 right-3 z-10">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border backdrop-blur-md {{ $badgeMode['color'] }}">
                {{ $badgeMode['label'] }}
            </span>
        </div>

        <!-- Channel Logo Badge (Center with Frosted Glass & Monogram Fallback) -->
        <div class="relative z-10 flex items-center justify-center p-4">
            <div class="p-2 sm:p-2.5 rounded-2xl bg-black/45 backdrop-blur-md border border-white/15 shadow-2xl flex items-center justify-center transition-transform duration-300 group-hover:scale-105 min-w-[3.5rem] min-h-[3.5rem]">
                <img src="{{ $channel->logo_url }}" 
                     alt="{{ $channel->name }}" 
                     class="max-h-12 max-w-[110px] w-auto object-contain drop-shadow-md"
                     loading="lazy"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-12 h-12 rounded-xl bg-gradient-to-br from-red-600/80 to-purple-800/80 flex items-center justify-center text-sm font-extrabold text-white shadow-inner\'>{{ $channel->initials }}</div>';">
            </div>
        </div>

        <!-- Country / Language -->
        <div class="absolute bottom-3 left-3 z-10 flex items-center gap-1.5">
            <span class="px-1.5 py-0.5 rounded bg-black/70 text-[10px] font-semibold text-gray-300 uppercase backdrop-blur-md border border-white/5">
                {{ $channel->country_code }}
            </span>
            <span class="px-1.5 py-0.5 rounded bg-black/70 text-[10px] font-medium text-gray-400 uppercase backdrop-blur-md border border-white/5">
                {{ $channel->language_code }}
            </span>
        </div>
    </a>

    <!-- Card Content -->
    <div class="p-4 flex-grow flex flex-col justify-between">
        <div>
            @if($mainCategory)
                <div class="text-[11px] font-medium text-red-400 uppercase tracking-wider mb-1">
                    {{ $mainCategory->name }}
                </div>
            @endif

            <h3 class="font-bold text-white text-base leading-snug group-hover:text-red-400 transition mb-1 line-clamp-1">
                <a href="{{ route('channels.show', $channel->slug) }}">
                    {{ $channel->name }}
                </a>
            </h3>

            <p class="text-xs text-gray-400 line-clamp-2 mb-3 leading-relaxed">
                {{ $channel->tagline ?: ($channel->description ?: 'Verified television programming, broadcasts, and streaming discovery on Zoniva TV.') }}
            </p>
        </div>

        <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between">
            <span class="text-[11px] text-gray-500 font-medium">
                {{ $channel->broadcaster_name ?: 'Broadcaster' }}
            </span>

            <a href="{{ route('channels.show', $channel->slug) }}" 
               class="inline-flex items-center gap-1 text-xs font-semibold text-red-400 hover:text-red-300 transition">
                <span>{{ $actionLabel }}</span>
                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
