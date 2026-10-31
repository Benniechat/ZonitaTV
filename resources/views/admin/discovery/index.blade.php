@extends('admin.layout')

@section('title', 'Content Discovery Workbench - Zoniva TV Administration')

@section('admin_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Content Discovery & Intake Workbench</h1>
            <p class="text-xs text-gray-400 mt-1">Staging area for incoming videos, provider feeds, and creator submissions before active broadcast.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.discovery.providers') }}" class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold text-xs rounded-xl border border-gray-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Content Providers</span>
            </a>
            <a href="{{ route('admin.discovery.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Stage Candidate</span>
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Awaiting Review</div>
            <div class="text-2xl font-black text-amber-400 mt-1">{{ number_format($metrics['staged_count']) }}</div>
            <div class="text-[10px] text-gray-500 mt-1">In Discovery Staging</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Approved & Promoted</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">{{ number_format($metrics['approved_count']) }}</div>
            <div class="text-[10px] text-emerald-500/80 mt-1">In Playout Pool</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Rejected Candidates</div>
            <div class="text-2xl font-black text-rose-400 mt-1">{{ number_format($metrics['rejected_count']) }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Irrelevant / Unlicensed</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Active Providers</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($metrics['providers_count']) }}</div>
            <div class="text-[10px] text-indigo-400 mt-1">Channels & Archives</div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="p-4 rounded-xl bg-[#10131a] border border-gray-800 space-y-3">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 border-b border-gray-800/80 pb-3">
            <a href="{{ route('admin.discovery.index', ['status' => 'staged']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'staged' ? 'bg-amber-600/20 text-amber-400 border border-amber-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Staged Queue ({{ $metrics['staged_count'] }})
            </a>
            <a href="{{ route('admin.discovery.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'approved' ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Approved ({{ $metrics['approved_count'] }})
            </a>
            <a href="{{ route('admin.discovery.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'rejected' ? 'bg-rose-600/20 text-rose-400 border border-rose-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Rejected ({{ $metrics['rejected_count'] }})
            </a>
            <a href="{{ route('admin.discovery.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'all' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                All Records
            </a>
        </div>

        <form method="GET" action="{{ route('admin.discovery.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="sm:col-span-2">
                <input type="text" name="q" value="{{ $query }}" placeholder="Search title, creator, identifier..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
            </div>
            <div>
                <select name="source_type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    <option value="">All Intake Sources</option>
                    <option value="youtube_video" {{ $sourceType === 'youtube_video' ? 'selected' : '' }}>YouTube Video</option>
                    <option value="youtube_playlist" {{ $sourceType === 'youtube_playlist' ? 'selected' : '' }}>YouTube Playlist</option>
                    <option value="internet_archive" {{ $sourceType === 'internet_archive' ? 'selected' : '' }}>Internet Archive</option>
                    <option value="creator_submission" {{ $sourceType === 'creator_submission' ? 'selected' : '' }}>Creator Submission</option>
                    <option value="direct_url" {{ $sourceType === 'direct_url' ? 'selected' : '' }}>Direct Stream URL</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Candidates Table -->
    <div class="rounded-xl border border-gray-800 bg-[#0d1017] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300">
                <thead class="bg-[#12151c] text-gray-400 font-semibold uppercase text-[10px] tracking-wider border-b border-gray-800">
                    <tr>
                        <th class="py-3 px-4">Candidate Item</th>
                        <th class="py-3 px-4">Source & Hash</th>
                        <th class="py-3 px-4">Target Channel</th>
                        <th class="py-3 px-4">Status & Rights</th>
                        <th class="py-3 px-4 text-right">Review Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse($candidates as $candidate)
                        <tr class="hover:bg-gray-800/30 transition">
                            <!-- Candidate Item -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-16 h-10 rounded bg-black overflow-hidden flex-shrink-0 border border-gray-800 relative">
                                        <img src="{{ $candidate->thumbnail_url ?: asset('images/logo/zonitatvlogo2.png') }}" alt="{{ $candidate->title }}" class="w-full h-full object-cover">
                                        <div class="absolute bottom-0 right-0 px-1 text-[8px] font-bold bg-black/80 text-white">
                                            {{ gmdate('i:s', $candidate->duration_seconds) }}
                                        </div>
                                    </div>
                                    <div class="min-w-0 max-w-xs">
                                        <a href="{{ route('admin.discovery.show', $candidate->id) }}" class="font-bold text-white hover:text-amber-400 transition truncate block">
                                            {{ $candidate->title }}
                                        </a>
                                        <span class="text-[10px] text-gray-500 block truncate">
                                            By {{ $candidate->creator_name ?: 'Independent' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Source & Hash -->
                            <td class="py-3 px-4 space-y-0.5">
                                <div class="text-[11px] font-mono text-gray-400 flex items-center gap-1">
                                    <span class="px-1.5 py-0.5 rounded bg-gray-800 text-[9px] text-gray-300 uppercase">{{ $candidate->source_type }}</span>
                                    <span>{{ $candidate->source_identifier }}</span>
                                </div>
                                <div class="text-[9px] font-mono text-gray-600 truncate max-w-xs" title="{{ $candidate->content_hash }}">
                                    hash: {{ substr($candidate->content_hash, 0, 16) }}...
                                </div>
                            </td>

                            <!-- Target Channel -->
                            <td class="py-3 px-4">
                                @if($candidate->channel)
                                    <span class="text-[11px] text-white font-medium block">{{ $candidate->channel->name }}</span>
                                    <span class="text-[9px] text-gray-500">{{ $candidate->category?->name ?: 'Direct Playout' }}</span>
                                @else
                                    <span class="text-[10px] text-gray-500 italic">Unassigned (Pool)</span>
                                @endif
                            </td>

                            <!-- Status & Rights -->
                            <td class="py-3 px-4 space-y-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $candidate->intake_status === 'approved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($candidate->intake_status === 'rejected' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30') }}">
                                    {{ $candidate->intake_status }}
                                </span>
                                @if($candidate->rejection_reason)
                                    <div class="text-[9px] text-rose-400 truncate max-w-xs" title="{{ $candidate->rejection_reason }}">
                                        Reason: {{ $candidate->rejection_reason }}
                                    </div>
                                @endif
                                <div class="text-[9px] text-gray-500">
                                    {{ $candidate->rights_basis }}
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.discovery.show', $candidate->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white font-semibold text-[11px] transition inline-block">
                                    Review &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                No discovery candidates found matching the selected filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($candidates->hasPages())
            <div class="p-4 border-t border-gray-800 bg-[#0d1017]">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
