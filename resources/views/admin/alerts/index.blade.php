@extends('admin.layout')

@section('title', 'Master System Alerts & Notifications - Zoniva TV')

@section('admin_content')

<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                    Automated Alert Dispatcher
                </span>
                <span class="text-xs text-gray-500">Infrastructure Monitoring & Triage</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white">System Alerts & Notifications</h1>
            <p class="text-xs text-gray-400 mt-0.5">Automated dead-stream alerts, circuit breaker incidents, copyright disputes, and broadcast logs.</p>
        </div>

        <div class="flex items-center gap-3">
            @if($counts['active'] > 0)
                <form action="{{ route('admin.alerts.resolve-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-xs font-semibold text-gray-200 transition">
                        Mark All Resolved ({{ $counts['active'] }})
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.community.index') }}" class="px-4 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/30 text-xs font-semibold text-emerald-400 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                WhatsApp Broadcasts
            </a>
        </div>
    </div>

    <!-- Alert KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Active Unresolved Alerts</span>
            <div class="text-3xl font-extrabold text-white mt-1">{{ $counts['active'] }}</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">Requiring engineer triage</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Critical Outages</span>
            <div class="text-3xl font-extrabold text-rose-500 mt-1">{{ $counts['critical'] }}</div>
            <span class="text-[10px] text-rose-400 font-semibold mt-2 inline-block">Stream failures & dead feeds</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Warnings & Takedowns</span>
            <div class="text-3xl font-extrabold text-amber-400 mt-1">{{ $counts['warning'] }}</div>
            <span class="text-[10px] text-amber-400 font-semibold mt-2 inline-block">DMCA & copyright notices</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Resolved Incidents</span>
            <div class="text-3xl font-extrabold text-emerald-400 mt-1">{{ $counts['resolved'] }}</div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-2 inline-block">Healthy restoration confirmed</span>
        </div>

    </div>

    <!-- Filter Bar & Alerts List -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="inline-flex rounded-xl bg-gray-900 border border-gray-800 p-1">
                <a href="{{ route('admin.alerts.index', ['status' => 'active']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $status === 'active' ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Active Alerts ({{ $counts['active'] }})
                </a>
                <a href="{{ route('admin.alerts.index', ['status' => 'resolved']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $status === 'resolved' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white' }}">
                    Resolved ({{ $counts['resolved'] }})
                </a>
                <a href="{{ route('admin.alerts.index', ['status' => 'all']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $status === 'all' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white' }}">
                    All Records ({{ $counts['total'] }})
                </a>
            </div>

            <!-- Severity Filter -->
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.alerts.index', ['level' => 'critical', 'status' => $status]) }}" 
                   class="px-2.5 py-1 text-xs rounded-lg {{ $level === 'critical' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-gray-400 hover:text-white' }}">
                    Critical
                </a>
                <a href="{{ route('admin.alerts.index', ['level' => 'warning', 'status' => $status]) }}" 
                   class="px-2.5 py-1 text-xs rounded-lg {{ $level === 'warning' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-gray-400 hover:text-white' }}">
                    Warning
                </a>
                <a href="{{ route('admin.alerts.index', ['level' => 'info', 'status' => $status]) }}" 
                   class="px-2.5 py-1 text-xs rounded-lg {{ $level === 'info' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'text-gray-400 hover:text-white' }}">
                    Info
                </a>
            </div>
        </div>

        @if($alerts->isEmpty())
            <div class="py-12 text-center text-xs text-gray-500">
                No alerts logged matching current criteria.
            </div>
        @else
            <div class="space-y-3">
                @foreach($alerts as $alert)
                    <div class="p-4 rounded-xl bg-gray-950/70 border border-gray-800/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase mt-0.5
                                @if($alert->level === 'critical') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                @elseif($alert->level === 'warning') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                @else bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 @endif">
                                {{ $alert->level }}
                            </span>
                            <div>
                                <div class="text-xs font-bold text-white mb-0.5">
                                    {{ $alert->message }}
                                </div>
                                <div class="flex items-center gap-2 text-[10px] text-gray-400">
                                    <span class="uppercase font-mono">{{ str_replace('_', ' ', $alert->alert_type) }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $alert->created_at->diffForHumans() }}</span>
                                    @if($alert->channel)
                                        <span>&bull;</span>
                                        <a href="{{ route('admin.channels.show', $alert->channel->id) }}" class="text-red-400 hover:underline">
                                            {{ $alert->channel->name }} (CH {{ $alert->channel->channel_number }})
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if(! $alert->is_resolved)
                                <form action="{{ route('admin.alerts.resolve', $alert->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600/90 hover:bg-emerald-600 text-xs font-semibold text-white transition">
                                        Mark Resolved
                                    </button>
                                </form>
                            @else
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold bg-gray-900 text-emerald-400 border border-emerald-500/20">
                                    Resolved {{ $alert->resolved_at?->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $alerts->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
