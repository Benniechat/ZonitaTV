@extends('admin.layout')

@section('title', 'Viewer Support & Problem Reports - Zoniva TV')

@section('admin_content')

<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold">
                    Viewer Support Triage
                </span>
                <span class="text-xs text-gray-500">Community Feedback & Incident Desk</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white">Problem Reports & Support Tickets</h1>
            <p class="text-xs text-gray-400 mt-0.5">Triage user-submitted stream issues, broken feeds, audio sync defects, and channel errors.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.alerts.index') }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-xs font-semibold text-gray-200 transition">
                System Alerts &rarr;
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">New Incoming Reports</span>
            <div class="text-3xl font-extrabold text-rose-500 mt-1">{{ $counts['new'] }}</div>
            <span class="text-[10px] text-rose-400 font-semibold mt-2 inline-block">Requires initial inspection</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Under Investigation</span>
            <div class="text-3xl font-extrabold text-amber-400 mt-1">{{ $counts['investigating'] }}</div>
            <span class="text-[10px] text-amber-400 font-semibold mt-2 inline-block">Assigned to engineers</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Resolved Issues</span>
            <div class="text-3xl font-extrabold text-emerald-400 mt-1">{{ $counts['resolved'] }}</div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-2 inline-block">Repaired and verified</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Total Tickets Handled</span>
            <div class="text-3xl font-extrabold text-white mt-1">{{ $counts['total'] }}</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">{{ $counts['dismissed'] }} dismissed as false-positives</span>
        </div>

    </div>

    <!-- Filter Bar & Reports Table -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        
        <!-- Status Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="inline-flex rounded-xl bg-gray-900 border border-gray-800 p-1">
                <a href="{{ route('admin.support.index', ['status' => 'all']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $status === 'all' ? 'bg-amber-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    All Tickets ({{ $counts['total'] }})
                </a>
                <a href="{{ route('admin.support.index', ['status' => 'new']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $status === 'new' ? 'bg-rose-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    New ({{ $counts['new'] }})
                </a>
                <a href="{{ route('admin.support.index', ['status' => 'investigating']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $status === 'investigating' ? 'bg-amber-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Investigating ({{ $counts['investigating'] }})
                </a>
                <a href="{{ route('admin.support.index', ['status' => 'resolved']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $status === 'resolved' ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Resolved ({{ $counts['resolved'] }})
                </a>
            </div>

            <!-- Issue Type Filter -->
            <form method="GET" action="{{ route('admin.support.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <select name="issue_type" onchange="this.form.submit()" class="bg-gray-900 border border-gray-800 rounded-xl px-3 py-1.5 text-xs text-gray-300">
                    <option value="">All Issue Types</option>
                    <option value="broken_stream" {{ $issueType === 'broken_stream' ? 'selected' : '' }}>Broken Stream</option>
                    <option value="sound_issue" {{ $issueType === 'sound_issue' ? 'selected' : '' }}>Sound Issue</option>
                    <option value="wrong_channel" {{ $issueType === 'wrong_channel' ? 'selected' : '' }}>Wrong Channel</option>
                    <option value="inappropriate" {{ $issueType === 'inappropriate' ? 'selected' : '' }}>Inappropriate Content</option>
                    <option value="other" {{ $issueType === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </form>
        </div>

        @if($reports->isEmpty())
            <div class="py-12 text-center text-xs text-gray-500">
                No problem reports logged in this queue.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3">Ticket ID</th>
                            <th class="py-3">Channel</th>
                            <th class="py-3">Issue Type</th>
                            <th class="py-3">Description</th>
                            <th class="py-3">Reported By</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        @foreach($reports as $rep)
                            <tr class="hover:bg-gray-900/30 transition">
                                <td class="py-3 font-mono text-gray-400">#{{ $rep->id }}</td>
                                <td class="py-3 font-bold text-white">
                                    <a href="{{ route('admin.support.show', $rep->id) }}" class="hover:text-red-400 transition">
                                        {{ $rep->channel->name ?? 'Channel #' . $rep->channel_id }}
                                    </a>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-800 text-amber-400 uppercase">
                                        {{ str_replace('_', ' ', $rep->issue_type) }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-300 max-w-xs truncate">
                                    {{ $rep->description ?: 'No comment supplied' }}
                                </td>
                                <td class="py-3 text-gray-400">
                                    {{ $rep->email ?: ($rep->user?->email ?: 'Anonymous Viewer') }}
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                        @if($rep->status === 'new') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                        @elseif($rep->status === 'investigating') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                        @elseif($rep->status === 'resolved') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @else bg-gray-800 text-gray-400 @endif">
                                        {{ ucfirst($rep->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.support.show', $rep->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-white font-semibold text-[11px] transition">
                                        Triage &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $reports->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
