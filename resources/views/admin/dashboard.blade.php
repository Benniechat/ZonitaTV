@extends('admin.layout')

@section('title', 'Zoniva TV - Executive Operations & Control Hub')

@section('admin_content')

<div class="space-y-8">
    
    <!-- Executive Operations Header & System Health Status -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 pb-6 border-b border-gray-800">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                    @if($metrics['unhealthy_streams'] === 0) bg-emerald-500/10 border border-emerald-500/20 text-emerald-400
                    @else bg-amber-500/10 border border-amber-500/20 text-amber-400 @endif">
                    <span class="w-2 h-2 rounded-full @if($metrics['unhealthy_streams'] === 0) bg-emerald-500 animate-pulse @else bg-amber-500 animate-ping @endif"></span>
                    {{ $metrics['unhealthy_streams'] === 0 ? 'All Systems Operational' : $metrics['unhealthy_streams'] . ' Stream Disruption Detected' }}
                </span>
                <span class="text-xs text-gray-500 font-mono">Master Control Room &bull; Port {{ request()->getPort() }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Executive Control Hub</h1>
            <p class="text-xs text-gray-400 mt-1">Unified monitoring for playout automation, live stream uptime, rights compliance, and viewer engagement.</p>
        </div>

        <!-- Action Shortcuts -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.analytics.index') }}" class="px-3.5 py-2 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-500/30 text-xs font-semibold text-indigo-300 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Analytics
            </a>
            <a href="{{ route('admin.compliance.index') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/30 text-xs font-semibold text-emerald-300 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Compliance
            </a>
            <a href="{{ route('admin.broadcasting.index') }}" class="px-3.5 py-2 rounded-xl bg-red-600/20 hover:bg-red-600/30 border border-red-500/30 text-xs font-semibold text-red-300 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Playout
            </a>
            <a href="{{ route('admin.channels.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition">
                + New Channel
            </a>
        </div>
    </div>

    <!-- Primary Operations KPI Quad -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Stream Health & Uptime -->
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400 font-medium">Broadcast Stream Health</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $metrics['uptime_percentage'] >= 90 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                    {{ $metrics['uptime_percentage'] }}% Uptime
                </span>
            </div>
            <div class="text-2xl font-extrabold text-white mt-2">
                {{ $metrics['healthy_channels'] }} / {{ $metrics['total_channels'] }}
            </div>
            <div class="w-full h-1.5 rounded-full bg-gray-900 mt-3 overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $metrics['uptime_percentage'] }}%"></div>
            </div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">
                {{ $metrics['unhealthy_streams'] }} failing or degraded streams
            </span>
        </div>

        <!-- Active Live Broadcasts -->
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Active Linear Playouts</span>
            <div class="text-2xl font-extrabold text-white mt-2 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                {{ $metrics['active_live_broadcasts'] }}
            </div>
            <div class="flex items-center gap-2 mt-3 text-[10px] text-gray-400">
                <span>{{ $metrics['virtual_channels'] }} Virtual Channels</span>
                <span>&bull;</span>
                <span>{{ $metrics['published_channels'] }} Directory Published</span>
            </div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-1 inline-block">24/7 Playout Progression Active</span>
        </div>

        <!-- Today's Audience Activity -->
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Today's Playback Starts</span>
            <div class="text-2xl font-extrabold text-indigo-400 mt-2">
                {{ number_format($metrics['playback_starts_today']) }}
            </div>
            <div class="flex items-center gap-2 mt-3 text-[10px] text-gray-400">
                <span>{{ number_format($metrics['total_views_today']) }} Total Interactions</span>
                <span>&bull;</span>
                <span>{{ number_format($metrics['total_page_views']) }} Total Views</span>
            </div>
            <a href="{{ route('admin.analytics.index') }}" class="text-[10px] text-indigo-400 hover:underline mt-1 inline-block">View Audience Analytics &rarr;</a>
        </div>

        <!-- Rights Compliance Rate -->
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400 font-medium">Rights Compliance</span>
                <span class="text-xs font-extrabold text-emerald-400">{{ $metrics['compliance_score'] }}%</span>
            </div>
            <div class="text-2xl font-extrabold text-white mt-2">
                {{ $metrics['pending_rights_review'] }} Pending
            </div>
            <div class="flex items-center gap-2 mt-3 text-[10px] text-gray-400">
                <span class="text-rose-400 font-semibold">{{ $metrics['expired_rights'] }} Expired</span>
                <span>&bull;</span>
                <span>{{ $metrics['subscribers_count'] }} Newsletter Opt-ins</span>
            </div>
            <a href="{{ route('admin.compliance.index') }}" class="text-[10px] text-emerald-400 hover:underline mt-1 inline-block">Audit Rights Ledger &rarr;</a>
        </div>

    </div>

    <!-- On-Air Now Playout Monitor -->
    @if(isset($onAirSchedules) && $onAirSchedules->isNotEmpty())
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                    Live Playout Monitor (On-Air Right Now)
                </h3>
                <p class="text-xs text-gray-400">Real-time broadcast execution across virtual and linear stations</p>
            </div>
            <a href="{{ route('admin.broadcasting.index') }}" class="text-xs text-red-400 hover:text-red-300">View Master Playout &rarr;</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($onAirSchedules as $sched)
                <div class="p-3.5 rounded-xl bg-gray-950/70 border border-gray-800/80 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-black border border-gray-800 flex items-center justify-center p-1.5 flex-shrink-0">
                        @if($sched->channel?->logo_url)
                            <img src="{{ $sched->channel->logo_url }}" alt="{{ $sched->channel->name }}" class="w-full h-full object-contain">
                        @else
                            <span class="text-xs font-bold text-red-500">{{ substr($sched->channel?->name ?? 'CH', 0, 2) }}</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-grow">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-xs font-bold text-white truncate">{{ $sched->channel?->name }}</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-red-600/20 text-red-400 font-bold">ON-AIR</span>
                        </div>
                        <p class="text-xs text-gray-300 font-medium truncate">{{ $sched->title ?: ($sched->video?->title ?: 'Live Broadcast') }}</p>
                        <span class="text-[10px] text-gray-500 font-mono">
                            {{ $sched->start_time->format('H:i') }} - {{ $sched->end_time->format('H:i') }} UTC
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Secondary Grids: Catalogue & Community Feedback -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Recently Added Channels -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Recently Added Channels</h3>
                <a href="{{ route('admin.channels.index') }}" class="text-xs text-red-400 hover:text-red-300">View All</a>
            </div>

            <div class="divide-y divide-gray-800">
                @foreach($recentChannels as $ch)
                    <div class="py-3 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <img src="{{ $ch->logo_url }}" alt="{{ $ch->name }}" class="w-8 h-8 object-contain rounded bg-gray-900 p-1" onerror="this.onerror=null; this.src='{{ asset(config('channels.default_logo', 'images/logo/zonitatvlogo2.png')) }}';">
                            <div>
                                <h4 class="text-xs font-bold text-white">{{ $ch->name }}</h4>
                                <span class="text-[10px] text-gray-400">CH {{ $ch->channel_number ?? str_pad((string)$ch->id, 2, '0', STR_PAD_LEFT) }} &bull; {{ $ch->country_code }}</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $ch->is_published ? 'bg-emerald-500/10 text-emerald-400' : 'bg-gray-800 text-gray-400' }}">
                                {{ $ch->is_published ? 'Published' : 'Draft' }}
                            </span>
                            <a href="{{ route('admin.channels.edit', $ch->id) }}" class="text-xs text-gray-400 hover:text-white">Edit</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Problem Reports -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Community Problem Reports</h3>
                <span class="text-xs px-2 py-0.5 rounded bg-red-600/20 text-red-400 font-semibold">{{ $metrics['open_reports'] }} New</span>
            </div>

            @if($recentReports->isNotEmpty())
                <div class="divide-y divide-gray-800">
                    @foreach($recentReports as $rep)
                        <div class="py-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-white">{{ $rep->channel->name ?? 'Unknown Channel' }}</span>
                                <span class="text-[10px] text-amber-400 uppercase font-semibold">{{ $rep->issue_type }}</span>
                            </div>
                            <p class="text-xs text-gray-400">{{ $rep->description ?: 'Viewer flagged this channel for investigation.' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-500 py-6 text-center">No open problem reports logged.</p>
            @endif
        </div>

    </div>

</div>

@endsection
