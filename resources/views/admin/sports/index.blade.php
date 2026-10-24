@extends('admin.layout')

@section('title', 'Sports Master Control Room - Zonita TV Admin')

@section('admin_content')
<div class="space-y-8">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-800 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/20 border border-red-500/30 text-red-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-red-500 {{ $stats['backend_connected'] ? 'animate-pulse' : '' }}"></span>
                MCR Automation Active
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Sports Master Control Room (MCR)</h1>
            <p class="text-xs text-gray-400 mt-1">Autonomous live football discovery, SportyTV verification, and 24/7 linear channel transmission engine.</p>
        </div>

        <!-- Automation Manual Trigger Actions -->
        <div class="flex flex-wrap items-center gap-2.5">
            <form action="{{ route('admin.sports.sync-fixtures') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-700 hover:border-gray-500 text-gray-200 text-xs font-semibold flex items-center gap-2 transition">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync Fixtures
                </button>
            </form>

            <form action="{{ route('admin.sports.verify-broadcasts') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-700 hover:border-gray-500 text-gray-200 text-xs font-semibold flex items-center gap-2 transition">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verify SportyTV
                </button>
            </form>

            <form action="{{ route('admin.sports.sync-schedule') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-red-600/30 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sync 24/7 EPG
                </button>
            </form>
        </div>
    </div>

    <!-- Telemetry Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-4">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Tracked</span>
            <p class="text-2xl font-black text-white mt-1">{{ $stats['total_fixtures'] }}</p>
            <span class="text-[10px] text-gray-500">Fixtures in database</span>
        </div>

        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-4">
            <span class="text-[11px] font-bold text-red-400 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-ping"></span>
                Live Now
            </span>
            <p class="text-2xl font-black text-red-400 mt-1">{{ $stats['live_fixtures'] }}</p>
            <span class="text-[10px] text-gray-500">Active matches</span>
        </div>

        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-4">
            <span class="text-[11px] font-bold text-blue-400 uppercase tracking-wider">Upcoming</span>
            <p class="text-2xl font-black text-white mt-1">{{ $stats['upcoming_fixtures'] }}</p>
            <span class="text-[10px] text-gray-500">Scheduled next 72h</span>
        </div>

        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-4">
            <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">SportyTV Verified</span>
            <p class="text-2xl font-black text-white mt-1">{{ $stats['confirmed_sportytv'] }}</p>
            <span class="text-[10px] text-gray-500">Confirmed broadcasts</span>
        </div>

        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-4">
            <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Active Sessions</span>
            <p class="text-2xl font-black text-white mt-1">{{ $stats['active_sessions'] }}</p>
            <span class="text-[10px] text-gray-500">MCR transmission state</span>
        </div>

        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-4">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Encoder Backend</span>
            <div class="mt-1 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $stats['backend_connected'] ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                <span class="text-sm font-black text-white uppercase">{{ $backendStatus['backend_type'] ?? 'MOCK' }}</span>
            </div>
            <span class="text-[10px] text-gray-500">{{ $stats['backend_connected'] ? 'Operational' : 'Offline' }}</span>
        </div>
    </div>

    <!-- Active Broadcast Sessions (Live Transmission Master Controls) -->
    <div class="bg-[#121620] border border-gray-800/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                    Live Transmission Sessions & Master Control
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Active transmission sessions currently routed to Zonita TV viewers or linear channel 82.</p>
            </div>
        </div>

        @if($activeSessions->isNotEmpty())
            <div class="divide-y divide-gray-800">
                @foreach($activeSessions as $session)
                    <div class="p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6 hover:bg-gray-800/20 transition">
                        <!-- Match Info -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider {{ $session->current_state === 'on_air' ? 'bg-red-600 text-white' : 'bg-blue-600/20 text-blue-400 border border-blue-500/30' }}">
                                    {{ str_replace('_', ' ', $session->current_state) }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $session->operating_mode === 'mode_c_rebroadcast' ? 'bg-purple-900/50 text-purple-300 border border-purple-500/30' : ($session->operating_mode === 'mode_b_embed' ? 'bg-amber-900/50 text-amber-300 border border-amber-500/30' : 'bg-gray-800 text-gray-300') }}">
                                    {{ str_replace('_', ' ', $session->operating_mode) }}
                                </span>
                                <span class="text-xs font-mono text-gray-500">Session #{{ $session->id }}</span>
                            </div>

                            <h3 class="text-lg font-black text-white">
                                {{ $session->fixture?->homeTeam?->name }} vs {{ $session->fixture?->awayTeam?->name }}
                            </h3>
                            <p class="text-xs text-gray-400">
                                {{ $session->fixture?->competition?->name }} &bull; Current Scene: <code class="text-red-400 bg-black/40 px-1.5 py-0.5 rounded font-mono text-[11px]">{{ $session->current_scene }}</code>
                            </p>
                        </div>

                        <!-- Emergency Overrides -->
                        <div class="flex items-center gap-3 shrink-0">
                            <!-- Emergency Fallback -->
                            <form action="{{ route('admin.sports.emergency-fallback', $session->id) }}" method="POST" onsubmit="return confirm('Immediately switch playout to emergency holding loop?');">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600/20 hover:bg-amber-600/30 border border-amber-500/40 text-amber-400 text-xs font-bold transition flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Emergency Fallback
                                </button>
                            </form>

                            <!-- Force Mode A Kill Switch -->
                            @if($session->operating_mode !== 'mode_a_link')
                                <form action="{{ route('admin.sports.force-mode-a', $session->id) }}" method="POST" onsubmit="return confirm('REVOKE RETRANSMISSION: Force this session into Mode A (Official Link Only)?');">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-600/20 hover:bg-red-600/30 border border-red-500/40 text-red-400 text-xs font-bold transition flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        Kill Stream & Force Mode A
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('sports.show', $session->fixture_id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-700 text-gray-300 text-xs font-semibold hover:border-gray-500 transition">
                                Public View &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-gray-500 text-xs">
                No active broadcast sessions currently transmitting. Playout engine is running in continuous 24/7 evergreen standby mode.
            </div>
        @endif
    </div>

    <!-- Discovered Fixtures & Operating Mode Controls -->
    <div class="bg-[#121620] border border-gray-800/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-white">Discovered Fixtures & Operating Mode Controls</h2>
                <p class="text-xs text-gray-400 mt-0.5">Automated fixture ledger with SportyTV destination verification and statutory Mode gates.</p>
            </div>
            <a href="{{ route('sports.index') }}" target="_blank" class="text-xs text-red-400 hover:text-red-300 font-semibold flex items-center gap-1 transition">
                Open Sports Hub &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-950/40">
                        <th class="p-4">Competition</th>
                        <th class="p-4">Teams</th>
                        <th class="p-4">Kickoff (WAT)</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">SportyTV Destination</th>
                        <th class="p-4">Active Mode</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($recentFixtures as $fixture)
                        <tr class="hover:bg-gray-800/20 transition">
                            <td class="p-4 font-semibold text-gray-300 whitespace-nowrap">
                                {{ $fixture->competition?->name }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <div class="font-bold text-white">{{ $fixture->homeTeam?->name }} vs {{ $fixture->awayTeam?->name }}</div>
                                <span class="text-[10px] text-gray-500">{{ $fixture->venue ?? 'Main Stadium' }}</span>
                            </td>
                            <td class="p-4 font-mono text-gray-300 whitespace-nowrap">
                                {{ $fixture->lagos_kickoff->format('d M, H:i') }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                @if($fixture->is_live_now)
                                    <span class="px-2 py-0.5 rounded-full bg-red-600/20 text-red-400 text-[10px] font-black uppercase">
                                        Live {{ $fixture->minute ? $fixture->minute . "'" : '' }}
                                    </span>
                                @elseif($fixture->status === 'finished')
                                    <span class="px-2 py-0.5 rounded-full bg-gray-800 text-gray-400 text-[10px] font-semibold uppercase">
                                        FT ({{ $fixture->home_score }}-{{ $fixture->away_score }})
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-blue-900/30 text-blue-400 text-[10px] font-semibold">
                                        Upcoming
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                @if($fixture->sportyBroadcast?->isConfirmed())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-950/60 text-emerald-400 border border-emerald-500/30 font-bold text-[10px]">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Confirmed
                                    </span>
                                @else
                                    <span class="text-gray-500 text-[10px]">Pending Check</span>
                                @endif
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <form action="{{ route('admin.sports.toggle-mode', $fixture->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    <select name="mode" onchange="this.form.submit()" class="bg-gray-950 border border-gray-800 text-gray-300 rounded-lg text-[11px] py-1 px-2 focus:ring-red-500 focus:border-red-500">
                                        <option value="mode_a_link" {{ $fixture->sportyBroadcast?->operating_mode === 'mode_a_link' ? 'selected' : '' }}>Mode A (Official Link)</option>
                                        <option value="mode_b_embed" {{ $fixture->sportyBroadcast?->operating_mode === 'mode_b_embed' ? 'selected' : '' }}>Mode B (Embedded Player)</option>
                                        <option value="mode_c_rebroadcast" {{ $fixture->sportyBroadcast?->operating_mode === 'mode_c_rebroadcast' ? 'selected' : '' }}>Mode C (Authorized Rebroadcast)</option>
                                    </select>
                                </form>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <a href="{{ route('sports.show', $fixture->id) }}" target="_blank" class="px-3 py-1 rounded-lg bg-gray-900 border border-gray-800 hover:border-gray-700 text-gray-300 text-xs font-semibold transition">
                                    View &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Broadcast Failures & Telemetry Logs -->
    <div class="bg-[#121620] border border-gray-800/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-gray-800">
            <h2 class="text-base font-bold text-white">Broadcast Failure Telemetry & Health Audit</h2>
            <p class="text-xs text-gray-400 mt-0.5">Recorded playback hiccups, stream drops, probe failures, and automatic recovery events.</p>
        </div>

        @if($recentFailures->isNotEmpty())
            <div class="divide-y divide-gray-800">
                @foreach($recentFailures as $failure)
                    <div class="p-4 flex items-center justify-between gap-4 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full {{ $failure->resolved ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            <div>
                                <span class="font-bold text-white font-mono text-[11px]">{{ $failure->failure_type }}</span>
                                <p class="text-gray-400 text-[11px]">{{ $failure->error_message }}</p>
                            </div>
                        </div>
                        <div class="text-right text-gray-500 text-[11px]">
                            <span>Session #{{ $failure->broadcast_session_id }}</span> &bull;
                            <span>{{ $failure->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-gray-500 text-xs">
                Zero stream failures recorded. Master Control Room operations are performing with 100% telemetry health.
            </div>
        @endif
    </div>

</div>
@endsection
