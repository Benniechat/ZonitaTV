@extends('admin.layout')

@section('title', 'Content Complaints & Takedown Register - Zonita TV Admin')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800 pb-6">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Content Complaints & Legal Notices</h1>
            <p class="text-xs text-gray-400 mt-1">Audit register of copyright infringement reports, takedown notices, and compliance actions</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('legal.report') }}" target="_blank" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-300 bg-gray-800 hover:bg-gray-700 transition flex items-center gap-1.5">
                <span>View Public Form</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- Quick Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-[#121620] border border-gray-800 rounded-xl p-4">
            <span class="text-[11px] text-gray-400 font-medium uppercase">Total Received</span>
            <p class="text-xl font-black text-white mt-1">{{ $counts['total'] }}</p>
        </div>
        <div class="bg-[#121620] border border-amber-500/30 rounded-xl p-4">
            <span class="text-[11px] text-amber-400 font-medium uppercase">Pending Review</span>
            <p class="text-xl font-black text-amber-400 mt-1">{{ $counts['pending'] }}</p>
        </div>
        <div class="bg-[#121620] border border-blue-500/30 rounded-xl p-4">
            <span class="text-[11px] text-blue-400 font-medium uppercase">Investigating</span>
            <p class="text-xl font-black text-blue-400 mt-1">{{ $counts['under_investigation'] }}</p>
        </div>
        <div class="bg-[#121620] border border-red-500/30 rounded-xl p-4">
            <span class="text-[11px] text-red-400 font-medium uppercase">Disabled / Purged</span>
            <p class="text-xl font-black text-red-400 mt-1">{{ $counts['content_disabled'] }}</p>
        </div>
        <div class="bg-[#121620] border border-emerald-500/30 rounded-xl p-4">
            <span class="text-[11px] text-emerald-400 font-medium uppercase">Resolved</span>
            <p class="text-xl font-black text-emerald-400 mt-1">{{ $counts['resolved'] }}</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-[#121620] border border-gray-800 rounded-xl p-4">
        <form action="{{ route('admin.complaints.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ref, name, email, content..." 
                       class="w-full bg-[#0b0d13] border border-gray-800 rounded-lg px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-500">
            </div>
            <div>
                <select name="status" class="w-full bg-[#0b0d13] border border-gray-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="under_investigation" {{ request('status') === 'under_investigation' ? 'selected' : '' }}>Under Investigation</option>
                    <option value="content_disabled" {{ request('status') === 'content_disabled' ? 'selected' : '' }}>Content Disabled</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <select name="type" class="w-full bg-[#0b0d13] border border-gray-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                    <option value="">All Categories</option>
                    <option value="copyright" {{ request('type') === 'copyright' ? 'selected' : '' }}>Copyright</option>
                    <option value="unauthorized_broadcast" {{ request('type') === 'unauthorized_broadcast' ? 'selected' : '' }}>Unauthorised Broadcast</option>
                    <option value="trademark" {{ request('type') === 'trademark' ? 'selected' : '' }}>Trademark</option>
                    <option value="privacy" {{ request('type') === 'privacy' ? 'selected' : '' }}>Privacy</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black font-bold text-xs rounded-lg transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'type']))
                    <a href="{{ route('admin.complaints.index') }}" class="px-3 py-2 bg-gray-800 text-gray-300 hover:text-white rounded-lg text-xs transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Complaints Table -->
    <div class="bg-[#121620] border border-gray-800 rounded-xl overflow-hidden shadow-xl">
        @if($complaints->isEmpty())
            <div class="p-12 text-center text-gray-500 text-xs">
                <svg class="w-10 h-10 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-semibold text-gray-400">No content complaints found matching your criteria.</p>
                <p class="mt-1 text-gray-600">All submissions via the public reporting form will appear in this compliance audit queue.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#0d1017] text-gray-400 border-b border-gray-800 uppercase text-[10px] tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4">Reference</th>
                            <th class="py-3 px-4">Complainant</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Reported Target</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Date Received</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60 text-gray-300">
                        @foreach($complaints as $c)
                            <tr class="hover:bg-gray-800/30 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-amber-400">
                                    {{ $c->reference_number }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-white">{{ $c->complainant_name }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $c->complainant_email }}</div>
                                    @if($c->complainant_organization)
                                        <div class="text-[10px] text-gray-500">{{ $c->complainant_organization }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase 
                                        @if($c->complaint_type === 'copyright') bg-red-500/10 text-red-400 border border-red-500/20
                                        @elseif($c->complaint_type === 'unauthorized_broadcast') bg-orange-500/10 text-orange-400 border border-orange-500/20
                                        @else bg-blue-500/10 text-blue-400 border border-blue-500/20 @endif">
                                        {{ str_replace('_', ' ', $c->complaint_type) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs truncate">
                                    <a href="{{ $c->infringing_url }}" target="_blank" class="text-gray-300 hover:text-white underline truncate block">
                                        {{ $c->infringing_url }}
                                    </a>
                                    @if($c->content_reference)
                                        <span class="text-[11px] text-gray-500 font-mono">Ref: {{ $c->content_reference }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        @if($c->status === 'pending') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                        @elseif($c->status === 'under_investigation') bg-blue-500/20 text-blue-300 border border-blue-500/30
                                        @elseif($c->status === 'content_disabled' || $c->status === 'content_removed') bg-red-500/20 text-red-300 border border-red-500/30
                                        @elseif($c->status === 'resolved') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                        @else bg-gray-700/40 text-gray-400 @endif">
                                        {{ str_replace('_', ' ', $c->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-400 text-[11px]">
                                    {{ $c->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('admin.complaints.show', $c) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-gray-800 hover:bg-gray-700 border border-gray-700 transition">
                                        Investigate &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-800">
                {{ $complaints->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
