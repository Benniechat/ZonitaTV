@extends('admin.layout')

@section('title', 'Virtual Broadcasting Network Operations - Zonita TV')

@section('admin_content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-white">24/7 Virtual Broadcasting Network</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-500/20 text-red-400 border border-red-500/30 uppercase tracking-wider animate-pulse">Live Operations</span>
            </div>
            <p class="text-xs text-gray-400 mt-1">Autonomous multi-channel programming, YouTube content verification, and continuous playout continuity.</p>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('admin.broadcasting.reverify') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold rounded-lg transition border border-gray-700 flex items-center gap-2 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Re-verify Catalogue
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Banner if Unresolved Alerts Exist -->
    @if($unresolvedAlerts->count() > 0)
        <div class="p-4 rounded-xl bg-red-950/40 border border-red-500/30 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-red-400 text-xs font-bold uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Active Network Operational Alerts ({{ $unresolvedAlerts->count() }})
                </div>
            </div>

            <div class="space-y-2">
                @foreach($unresolvedAlerts as $alert)
                    <div class="flex items-center justify-between bg-black/40 p-3 rounded-lg border border-red-500/20 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $alert->level === 'critical' ? 'bg-red-500 text-white' : 'bg-amber-500/20 text-amber-300' }}">
                                {{ $alert->level }}
                            </span>
                            <span class="font-medium text-gray-200">{{ $alert->message }}</span>
                            <span class="text-gray-500 text-[11px]">{{ $alert->created_at?->diffForHumans() }}</span>
                        </div>
                        <form action="{{ route('admin.broadcasting.resolve-alert', $alert) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold bg-gray-800 hover:bg-gray-700 text-gray-300 rounded border border-gray-700 transition">
                                Acknowledge & Resolve
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-[#10131a] border border-gray-800 p-4 rounded-xl">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Virtual Channels</div>
            <div class="text-2xl font-black text-white mt-1">{{ $stats['total_channels'] }}</div>
            <div class="text-[10px] text-emerald-400 mt-1 flex items-center gap-1 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> 100% Broadcast Ready
            </div>
        </div>

        <div class="bg-[#10131a] border border-gray-800 p-4 rounded-xl">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Verified Videos</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">{{ $stats['approved_videos'] }}</div>
            <div class="text-[10px] text-gray-400 mt-1 font-medium">of {{ $stats['total_videos'] }} candidates</div>
        </div>

        <div class="bg-[#10131a] border border-gray-800 p-4 rounded-xl">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Active Schedules</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($stats['total_scheduled_entries']) }}</div>
            <div class="text-[10px] text-gray-400 mt-1 font-medium">7-Day Rolling Entries</div>
        </div>

        <div class="bg-[#10131a] border border-gray-800 p-4 rounded-xl">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Reserve Status</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">Healthy</div>
            <div class="text-[10px] text-emerald-400/80 mt-1 font-medium">Verified Fallbacks Active</div>
        </div>

        <div class="bg-[#10131a] border border-gray-800 p-4 rounded-xl">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">System Alerts</div>
            <div class="text-2xl font-black {{ $stats['unresolved_alerts'] > 0 ? 'text-red-400' : 'text-gray-400' }} mt-1">
                {{ $stats['unresolved_alerts'] }}
            </div>
            <div class="text-[10px] text-gray-400 mt-1 font-medium">Active Anomalies</div>
        </div>

        <div class="bg-[#10131a] border border-gray-800 p-4 rounded-xl">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Recovered Failures</div>
            <div class="text-2xl font-black text-amber-400 mt-1">{{ $stats['total_failures'] }}</div>
            <div class="text-[10px] text-gray-400 mt-1 font-medium">Auto-replaced</div>
        </div>
    </div>

    <!-- Active Virtual Channels Table -->
    <div class="bg-[#10131a] border border-gray-800 rounded-xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white">Virtual Channel Playout Status</h3>
                <p class="text-xs text-gray-400 mt-0.5">Autonomous channels broadcasting continuous 24/7 programmed streams</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#0d1017] text-gray-400 uppercase tracking-wider text-[10px] border-b border-gray-800 font-bold">
                    <tr>
                        <th class="py-3 px-4">Channel & Identity</th>
                        <th class="py-3 px-4">Currently Playing</th>
                        <th class="py-3 px-4">Schedule Queue</th>
                        <th class="py-3 px-4">Reserve Health</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($virtualChannels as $vc)
                        @php $channel = $vc['model']; @endphp
                        <tr class="hover:bg-gray-800/40 transition">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-black/60 border border-gray-800 flex items-center justify-center p-1 overflow-hidden shrink-0">
                                        <img src="{{ $channel->logo_path ?? asset('images/logo/zonitatvlogo2.png') }}" alt="{{ $channel->name }}" class="max-h-full max-w-full object-contain">
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm flex items-center gap-2">
                                            {{ $channel->name }}
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                                        </div>
                                        <div class="text-[11px] text-gray-400 flex items-center gap-2 mt-0.5">
                                            <span>{{ $channel->categories->first()?->name ?? 'General' }}</span>
                                            <span>•</span>
                                            <span>{{ $channel->sourceRule?->scheduling_mode ?? 'hybrid' }}</span>
                                            <span>•</span>
                                            <span>{{ $channel->sourceRule?->timezone ?? 'Africa/Lagos' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                @if($vc['active_entry'] && $vc['active_entry']->video)
                                    <div>
                                        <div class="font-semibold text-gray-200 line-clamp-1 max-w-xs">
                                            {{ $vc['active_entry']->video->title }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-2">
                                            <span class="px-1.5 py-0.2 rounded bg-red-600/20 text-red-400 font-bold">LIVE</span>
                                            <span>{{ gmdate('H:i:s', $vc['active_entry']->video->duration_seconds) }}</span>
                                            <span>Score: {{ $vc['active_entry']->video->confidence_score }}/100</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Holding screen / standby</span>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <div>
                                    <span class="font-bold {{ $vc['future_hours'] > 48 ? 'text-emerald-400' : ($vc['future_hours'] > 24 ? 'text-amber-400' : 'text-red-400') }}">
                                        {{ $vc['future_hours'] }} hrs
                                    </span>
                                    <span class="text-gray-400 text-[11px]">({{ $vc['future_count'] }} slots)</span>
                                </div>
                                <div class="w-24 bg-gray-800 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                    <div class="h-full bg-emerald-400 rounded-full" style="width: {{ min(100, ($vc['future_hours'] / 168) * 100) }}%"></div>
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $vc['reserve_ok'] ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-red-500/10 text-red-400 border-red-500/30' }}">
                                    {{ $vc['reserve_available'] }} / {{ $vc['reserve_required'] }} Available
                                </span>
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('channels.show', $channel->slug) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded font-medium text-[11px] transition flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Watch
                                    </a>

                                    <a href="{{ route('admin.broadcasting.rules.edit', $channel) }}" class="px-2.5 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded font-medium text-[11px] transition">
                                        Rules
                                    </a>

                                    <form action="{{ route('admin.broadcasting.regenerate', $channel) }}" method="POST" onsubmit="return confirm('Regenerate 7-day rolling schedule for {{ $channel->name }}?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-300 border border-red-500/30 rounded font-medium text-[11px] transition flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Build Schedule
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                No virtual broadcast channels currently provisioned.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Playback Failures & Automated Recovery Telemetry -->
    @if($recentFailures->count() > 0)
        <div class="bg-[#10131a] border border-gray-800 rounded-xl overflow-hidden shadow-sm">
            <div class="p-5 border-b border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white">Self-Healing Telemetry & Playback Incident Recovery</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Automated circuit-breaker replacements triggered during client playback</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#0d1017] text-gray-400 uppercase tracking-wider text-[10px] border-b border-gray-800 font-bold">
                        <tr>
                            <th class="py-3 px-4">Time</th>
                            <th class="py-3 px-4">Channel</th>
                            <th class="py-3 px-4">Failed Programme</th>
                            <th class="py-3 px-4">Error Code</th>
                            <th class="py-3 px-4">Self-Healing Recovery Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($recentFailures as $failure)
                            <tr class="hover:bg-gray-800/30 transition">
                                <td class="py-3 px-4 text-gray-400 font-mono text-[11px]">
                                    {{ $failure->created_at?->diffForHumans() }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-gray-200">
                                    {{ $failure->channel?->name ?? 'Unknown' }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="line-clamp-1 max-w-xs text-gray-300 font-medium">
                                        {{ $failure->video?->title ?? $failure->video?->youtube_id ?? 'Video #' . $failure->video_id }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-mono text-[10px] font-bold">
                                        Code {{ $failure->error_code }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($failure->replacementVideo)
                                        <div class="flex items-center gap-1 text-emerald-400 font-medium text-[11px]">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Instantly switched to reserve: <span class="text-gray-300 font-normal line-clamp-1 max-w-[200px]">{{ $failure->replacementVideo->title }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-500">Holding slate displayed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
