@props(['fixture'])

@php
    $sporty = $fixture->sportyBroadcast;
    $isLive = $fixture->is_live_now;
    $mode = $sporty?->operating_mode ?? 'mode_a_link';

    $modeBadge = match($mode) {
        'mode_c_rebroadcast' => ['label' => 'Zonita Broadcast', 'color' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
        'mode_b_embed' => ['label' => 'Embedded Player', 'color' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
        default => ['label' => 'Official SportyTV Link', 'color' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'],
    };
@endphp

<div class="group relative bg-[#131620] hover:bg-[#181d2a] border border-gray-800/80 hover:border-gray-700 rounded-2xl overflow-hidden shadow-lg transition-all duration-300 flex flex-col justify-between">
    
    <!-- Top Metadata Bar -->
    <div class="px-4 py-3 bg-gray-900/60 border-b border-gray-800/60 flex items-center justify-between">
        <div class="flex items-center gap-2">
            @if($fixture->competition?->emblem_url)
                <img src="{{ $fixture->competition->emblem_url }}" class="w-4 h-4 object-contain" onerror="this.style.display='none';">
            @endif
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 truncate max-w-[140px]">
                {{ $fixture->competition?->name }}
            </span>
        </div>

        @if($isLive)
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-600 text-white animate-pulse">
                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                LIVE {{ $fixture->match_clock ?: 'NOW' }}
            </span>
        @else
            <span class="text-[10px] font-medium text-gray-400">
                {{ $fixture->utc_kickoff ? $fixture->utc_kickoff->diffForHumans() : 'Upcoming' }}
            </span>
        @endif
    </div>

    <!-- Match Stage: Teams & Versus -->
    <div class="p-5 flex items-center justify-between gap-3">
        
        <!-- Home Team -->
        <div class="flex-1 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-900 border border-gray-800 p-2 flex items-center justify-center mb-2 group-hover:scale-105 transition shadow">
                <img src="{{ $fixture->homeTeam?->logo }}" alt="{{ $fixture->homeTeam?->name }}" class="max-h-full max-w-full object-contain" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-xs font-black text-gray-400\'>{{ substr($fixture->homeTeam?->name ?? 'HT', 0, 3) }}</span>';">
            </div>
            <h4 class="text-xs font-bold text-white line-clamp-1">{{ $fixture->homeTeam?->name }}</h4>
            <span class="text-[10px] text-gray-500 font-mono">{{ $fixture->homeTeam?->tla }}</span>
        </div>

        <!-- Center Score / VS / Countdown -->
        <div class="flex flex-col items-center justify-center shrink-0 px-2">
            @if($isLive && ($fixture->home_score !== null || $fixture->away_score !== null))
                <div class="px-3 py-1 rounded-xl bg-black border border-red-500/40 text-lg font-black text-white font-mono shadow-inner tracking-wider">
                    {{ $fixture->home_score ?? 0 }} - {{ $fixture->away_score ?? 0 }}
                </div>
                <span class="text-[9px] font-bold text-red-400 uppercase mt-1">In Play</span>
            @else
                <span class="text-xs font-black text-gray-600 uppercase tracking-widest mb-1">VS</span>
                <div class="text-[11px] font-bold text-amber-400 font-mono text-center">
                    {{ $fixture->lagos_kickoff ? $fixture->lagos_kickoff->format('H:i') : '' }}
                    <span class="text-[9px] text-gray-500 block font-normal">WAT</span>
                </div>
            @endif
        </div>

        <!-- Away Team -->
        <div class="flex-1 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-900 border border-gray-800 p-2 flex items-center justify-center mb-2 group-hover:scale-105 transition shadow">
                <img src="{{ $fixture->awayTeam?->logo }}" alt="{{ $fixture->awayTeam?->name }}" class="max-h-full max-w-full object-contain" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-xs font-black text-gray-400\'>{{ substr($fixture->awayTeam?->name ?? 'AT', 0, 3) }}</span>';">
            </div>
            <h4 class="text-xs font-bold text-white line-clamp-1">{{ $fixture->awayTeam?->name }}</h4>
            <span class="text-[10px] text-gray-500 font-mono">{{ $fixture->awayTeam?->tla }}</span>
        </div>

    </div>

    <!-- Kickoff Date & Venue -->
    <div class="px-5 py-2 text-center text-[11px] text-gray-400 border-t border-gray-800/40">
        <span class="font-medium text-gray-300">{{ $fixture->lagos_formatted }}</span>
        @if($fixture->venue)
            <span class="block text-[10px] text-gray-500 truncate mt-0.5">&bull; {{ $fixture->venue }}</span>
        @endif
    </div>

    <!-- Footer Controls -->
    <div class="p-4 bg-gray-900/40 border-t border-gray-800 flex items-center justify-between gap-3">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ $modeBadge['color'] }}">
            {{ $modeBadge['label'] }}
        </span>

        <div class="flex items-center gap-2">
            <a href="{{ route('sports.show', $fixture->id) }}" 
               class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold transition">
                Details
            </a>

            @if($sporty && $sporty->isConfirmed())
                <a href="{{ $sporty->viewing_url }}" target="_blank" rel="noopener noreferrer" 
                   class="px-3.5 py-1.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition flex items-center gap-1 shadow">
                    <span>Watch</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif
        </div>
    </div>

</div>
