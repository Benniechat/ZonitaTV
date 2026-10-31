@extends('admin.layout')

@section('title', 'Rights Compliance & Governance Audit - Zoniva TV')

@section('admin_content')

<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
                    Statutory Compliance
                </span>
                <span class="text-xs text-gray-500">Legal Rights & Territory Audit</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white">Rights Compliance & Audit Log</h1>
            <p class="text-xs text-gray-400 mt-0.5">Continuous licensing ledger verification, expiration monitoring, territory clearance, and DMCA audit logs.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.rights.index') }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-xs font-semibold text-gray-200 transition">
                Manage Rights Ledger &rarr;
            </a>
            <a href="{{ route('admin.complaints.index') }}" class="px-4 py-2 rounded-xl bg-red-600/20 hover:bg-red-600/30 border border-red-500/30 text-xs font-semibold text-red-400 transition">
                Takedown Tickets
            </a>
        </div>
    </div>

    <!-- Compliance Score & Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Compliance Health Score</span>
            <div class="text-3xl font-extrabold text-white mt-1">{{ $complianceRate }}%</div>
            <div class="w-full h-1.5 rounded-full bg-gray-900 mt-3 overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $complianceRate }}%"></div>
            </div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-2 inline-block">
                {{ $compliantChannelsCount }} / {{ $totalChannels }} Channels Legally Cleared
            </span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Expiring Within 7 Days</span>
            <div class="text-3xl font-extrabold text-amber-400 mt-1">{{ $expiring7Days }}</div>
            <span class="text-[10px] text-amber-400 font-semibold mt-2 inline-block">Urgent renewal required</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Expiring Within 30 Days</span>
            <div class="text-3xl font-extrabold text-indigo-400 mt-1">{{ $expiring30Days }}</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">On renewal radar</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Expired / Restricted Rights</span>
            <div class="text-3xl font-extrabold text-rose-500 mt-1">{{ $expiredCount }}</div>
            <span class="text-[10px] text-rose-400 font-semibold mt-2 inline-block">De-listed from directory</span>
        </div>

    </div>

    <!-- Rights Audit Ledger Table -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        
        <!-- Table Filter Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-base font-bold text-white">Licensing Rights Ledger Audit</h3>
                <p class="text-xs text-gray-400">Audit status of individual channel broadcast permissions</p>
            </div>

            <div class="inline-flex rounded-xl bg-gray-900 border border-gray-800 p-1">
                <a href="{{ route('admin.compliance.index') }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ empty($statusFilter) ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    All ({{ $totalRights }})
                </a>
                <a href="{{ route('admin.compliance.index', ['status' => 'expiring_soon']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'expiring_soon' ? 'bg-amber-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Expiring Soon ({{ $expiring30Days }})
                </a>
                <a href="{{ route('admin.compliance.index', ['status' => 'expired']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'expired' ? 'bg-rose-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Expired ({{ $expiredCount }})
                </a>
                <a href="{{ route('admin.compliance.index', ['status' => 'cleared']) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'cleared' ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:text-white' }}">
                    Cleared ({{ $clearedCount }})
                </a>
            </div>
        </div>

        @if($rights->isEmpty())
            <div class="py-12 text-center text-xs text-gray-500">
                No rights records matching current filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3">Channel</th>
                            <th class="py-3">License Type</th>
                            <th class="py-3">Territories</th>
                            <th class="py-3">Expires At</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right">Remediation Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        @foreach($rights as $right)
                            <tr class="hover:bg-gray-900/30 transition">
                                <td class="py-3">
                                    <div class="font-bold text-white">{{ $right->channel->name ?? 'Channel #' . $right->channel_id }}</div>
                                    <div class="text-[10px] text-gray-400 flex items-center gap-1.5 mt-0.5">
                                        <span>CH {{ $right->channel->channel_number ?? str_pad((string)$right->channel_id, 2, '0', STR_PAD_LEFT) }}</span>
                                        @if($right->rights_holder_name)
                                            <span>&bull;</span>
                                            <span class="truncate max-w-[150px]">{{ $right->rights_holder_name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-800 text-gray-300 uppercase">
                                        {{ str_replace('_', ' ', $right->classification ?? $right->licence_name ?? 'direct_license') }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-400">
                                    @php $terrs = $right->permitted_territories ?? []; @endphp
                                    @if(empty($terrs) || in_array('*', $terrs))
                                        <span class="text-emerald-400 font-semibold">Worldwide</span>
                                    @else
                                        <span>{{ implode(', ', $terrs) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-gray-300 font-mono">
                                    @if($right->expires_at)
                                        <span class="{{ \Carbon\Carbon::parse($right->expires_at)->isPast() ? 'text-rose-400 font-bold' : (\Carbon\Carbon::parse($right->expires_at)->diffInDays(now()) < 30 ? 'text-amber-400 font-semibold' : '') }}">
                                            {{ \Carbon\Carbon::parse($right->expires_at)->format('Y-m-d') }}
                                        </span>
                                    @else
                                        <span class="text-emerald-400">Perpetual</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                        @if($right->status === 'cleared') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @elseif($right->status === 'pending_review') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                        @else bg-rose-500/10 text-rose-400 border border-rose-500/20 @endif">
                                        {{ ucfirst($right->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <form action="{{ route('admin.compliance.remediate', $right->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        <select name="action" class="bg-gray-900 border border-gray-700 text-[11px] text-gray-300 rounded px-2 py-1">
                                            <option value="extend_1_year">Extend 1 Year</option>
                                            <option value="mark_perpetual">Make Perpetual</option>
                                            <option value="revoke">Revoke</option>
                                        </select>
                                        <button type="submit" class="px-2.5 py-1 rounded bg-gray-800 hover:bg-gray-700 text-[11px] font-semibold text-white transition">
                                            Apply
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $rights->links() }}
            </div>
        @endif

    </div>

    <!-- Enforcement & Takedown Audit Log -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Content Complaints & DMCA Takedown Tickets -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-white">DMCA & Content Complaints</h3>
                    <p class="text-xs text-gray-400">Formal rights holder complaints & tickets</p>
                </div>
                <a href="{{ route('admin.complaints.index') }}" class="text-xs text-red-400 hover:text-red-300">View All</a>
            </div>

            @if($recentComplaints->isEmpty())
                <div class="py-8 text-center text-xs text-gray-500">
                    No active copyright complaints recorded.
                </div>
            @else
                <div class="divide-y divide-gray-800">
                    @foreach($recentComplaints as $c)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-xs font-bold text-white">{{ $c->reference_number }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-800 text-gray-300">
                                        {{ str_replace('_', ' ', $c->complaint_type) }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    By {{ $c->complainant_name }} ({{ $c->complainant_organization ?: 'Individual' }})
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                @if($c->status === 'resolved') bg-emerald-500/10 text-emerald-400
                                @elseif($c->status === 'takedown_executed') bg-rose-500/10 text-rose-400
                                @else bg-amber-500/10 text-amber-400 @endif">
                                {{ ucfirst($c->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Viewer Problem Reports -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-white">Viewer Problem Reports</h3>
                    <p class="text-xs text-gray-400">Community reports on broken streams and playback</p>
                </div>
            </div>

            @if($recentProblemReports->isEmpty())
                <div class="py-8 text-center text-xs text-gray-500">
                    No community problem reports logged.
                </div>
            @else
                <div class="divide-y divide-gray-800">
                    @foreach($recentProblemReports as $r)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-xs font-bold text-white">{{ $r->channel->name ?? 'Channel #' . $r->channel_id }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-500/10 text-red-400">
                                        {{ str_replace('_', ' ', $r->issue_type) }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-400 line-clamp-1">{{ $r->description ?: 'No additional description provided' }}</p>
                            </div>
                            <span class="text-[10px] text-gray-500 font-mono">{{ $r->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>

@endsection
