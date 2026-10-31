@extends('admin.layout')

@section('title', 'Triage Problem Report #' . $report->id . ' - Zoniva TV')

@section('admin_content')

<div class="max-w-4xl mx-auto space-y-8">
    
    <!-- Top Bar -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono text-gray-500">Ticket #{{ $report->id }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                    @if($report->status === 'new') bg-rose-500/10 text-rose-400 border border-rose-500/20
                    @elseif($report->status === 'investigating') bg-amber-500/10 text-amber-400 border border-amber-500/20
                    @elseif($report->status === 'resolved') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                    @else bg-gray-800 text-gray-400 @endif">
                    {{ ucfirst($report->status) }}
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-white">Problem Report Triage</h1>
        </div>

        <a href="{{ route('admin.support.index') }}" class="px-3.5 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-200 transition">
            &larr; Back to Support Queue
        </a>
    </div>

    <!-- Ticket Details & Channel Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Incident Info -->
        <div class="p-6 rounded-2xl bg-[#12151c] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Incident Details</h3>
            
            <div>
                <span class="text-xs text-gray-400">Issue Category:</span>
                <div class="text-sm font-bold text-amber-400 uppercase mt-0.5">{{ str_replace('_', ' ', $report->issue_type) }}</div>
            </div>

            <div>
                <span class="text-xs text-gray-400">Viewer Comment / Description:</span>
                <div class="p-3 rounded-xl bg-gray-950 border border-gray-800 text-xs text-gray-200 mt-1 whitespace-pre-wrap leading-relaxed">
                    {{ $report->description ?: 'No comment supplied by user.' }}
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-400 pt-2 border-t border-gray-800/80">
                <span>Reporter Email:</span>
                <span class="font-semibold text-white">{{ $report->email ?: ($report->user?->email ?: 'Anonymous') }}</span>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-400">
                <span>Date Logged:</span>
                <span class="font-mono">{{ $report->created_at->format('Y-m-d H:i') }} ({{ $report->created_at->diffForHumans() }})</span>
            </div>
        </div>

        <!-- Associated Channel Diagnostics -->
        <div class="p-6 rounded-2xl bg-[#12151c] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Affected Station</h3>

            @if($report->channel)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-950 border border-gray-800">
                    <div class="w-12 h-12 rounded-lg bg-black border border-gray-800 flex items-center justify-center p-2 flex-shrink-0">
                        @if($report->channel->logo_url)
                            <img src="{{ $report->channel->logo_url }}" alt="{{ $report->channel->name }}" class="w-full h-full object-contain">
                        @else
                            <span class="text-sm font-extrabold text-red-500">{{ substr($report->channel->name, 0, 2) }}</span>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">{{ $report->channel->name }}</div>
                        <div class="text-xs text-gray-400 mt-0.5 font-mono">CH {{ $report->channel->channel_number }} &bull; Mode: {{ $report->channel->playback_mode }}</div>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-gray-400">
                        <span>Stream Health:</span>
                        <span class="font-semibold {{ $report->channel->is_technically_healthy ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $report->channel->is_technically_healthy ? 'Verified Healthy' : 'Failing / Degraded' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Broadcast Status:</span>
                        <span class="font-semibold {{ $report->channel->is_published ? 'text-emerald-400' : 'text-gray-500' }}">
                            {{ $report->channel->is_published ? 'Publicly Listed' : 'Unpublished' }}
                        </span>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-800 flex items-center gap-2">
                    <a href="{{ route('channels.show', $report->channel->slug) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-white transition">
                        View Live Stream &rarr;
                    </a>
                    <a href="{{ route('admin.channels.edit', $report->channel->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
                        Edit Channel Feeds
                    </a>
                </div>
            @else
                <p class="text-xs text-gray-500">Channel record no longer present.</p>
            @endif
        </div>

    </div>

    <!-- Triage & Status Progression Control -->
    <div class="p-6 rounded-2xl bg-[#12151c] border border-gray-800">
        <h3 class="text-base font-bold text-white mb-2">Triage & Status Action</h3>
        <p class="text-xs text-gray-400 mb-6">Transition ticket workflow and append internal engineering investigation notes.</p>

        <form action="{{ route('admin.support.update', $report->id) }}" method="POST" class="space-y-5">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Ticket Workflow Status</label>
                    <select name="status" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="new" {{ $report->status === 'new' ? 'selected' : '' }}>New (Awaiting Review)</option>
                        <option value="investigating" {{ $report->status === 'investigating' ? 'selected' : '' }}>Investigating (Assigned / In Progress)</option>
                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved (Stream Repaired / Verified)</option>
                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed (False Positive / User Device Issue)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Action Completed By</label>
                    <input type="text" readonly value="{{ auth()->user()->name }} ({{ auth()->user()->role }})" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-gray-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1.5">Append Engineering / Resolution Notes</label>
                <textarea name="admin_notes" rows="3" placeholder="Enter findings, stream replacement details, or resolution explanation..." class="w-full bg-gray-900 border border-gray-800 rounded-xl p-4 text-xs text-white focus:outline-none focus:border-red-500 placeholder-gray-600"></textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-xs font-bold text-white transition">
                    Save Triage Update
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
