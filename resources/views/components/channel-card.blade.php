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
    <a href="{{ route('channels.show', $channel->slug) }}" class="relative block aspect-video w-full bg-gradient-to-br from-gray-900 to-[#101216] overflow-hidden p-6 flex items-center justify-center">
        @if($channel->logo_path)
            <img src="{{ asset($channel->logo_path) }}" alt="{{ $channel->name }}" class="max-h-16 w-auto object-contain transition-transform duration-300 group-hover:scale-110 drop-shadow-md">
        @else
            <div class="w-16 h-16 rounded-xl bg-gray-800 flex items-center justify-center text-xl font-bold text-gray-400">
                {{ substr($channel->name, 0, 2) }}
            </div>
        @endif

        <!-- Mode badge -->
        <div class="absolute top-3 right-3">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ $badgeMode['color'] }}">
                {{ $badgeMode['label'] }}
            </span>
        </div>

        <!-- Country / Language -->
        <div class="absolute bottom-3 left-3 flex items-center gap-1.5">
            <span class="px-1.5 py-0.5 rounded bg-black/60 text-[10px] font-semibold text-gray-300 uppercase backdrop-blur-sm">
                {{ $channel->country_code }}
            </span>
            <span class="px-1.5 py-0.5 rounded bg-black/60 text-[10px] font-medium text-gray-400 uppercase backdrop-blur-sm">
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
