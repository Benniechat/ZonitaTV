@extends('admin.layout')

@section('title', 'Review Candidate: ' . $candidate->title)

@section('admin_content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-extrabold text-white">{{ $candidate->title }}</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $candidate->intake_status === 'approved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($candidate->intake_status === 'rejected' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30') }}">
                    {{ $candidate->intake_status }}
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-1">Staged Candidate #{{ $candidate->id }} • Source: <code class="text-amber-400">{{ $candidate->source_type }}:{{ $candidate->source_identifier }}</code></p>
        </div>
        <a href="{{ route('admin.discovery.index') }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
            &larr; Back to Workbench
        </a>
    </div>

    <!-- Review Inspection Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Media Preview -->
        <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <div class="aspect-video w-full rounded-lg overflow-hidden bg-black border border-gray-800 relative">
                @if($candidate->source_type === 'youtube_video')
                    <iframe src="https://www.youtube-nocookie.com/embed/{{ $candidate->source_identifier }}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                @else
                    <img src="{{ $candidate->thumbnail_url ?: asset('images/logo/zonitatvlogo2.png') }}" class="w-full h-full object-cover" alt="{{ $candidate->title }}">
                @endif
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-gray-800 text-gray-400">
                    <span>Source URL:</span>
                    <a href="{{ $candidate->source_url }}" target="_blank" class="text-red-400 underline truncate max-w-[150px]">Open Source &rarr;</a>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-800 text-gray-400">
                    <span>Duration:</span>
                    <span class="font-bold text-white">{{ gmdate('H:i:s', $candidate->duration_seconds) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-800 text-gray-400">
                    <span>Rights Basis:</span>
                    <span class="font-bold text-amber-400">{{ $candidate->rights_basis }}</span>
                </div>
                <div class="flex justify-between py-1 text-gray-400">
                    <span>Content Hash:</span>
                    <span class="font-mono text-[10px] text-gray-500" title="{{ $candidate->content_hash }}">{{ substr($candidate->content_hash, 0, 12) }}...</span>
                </div>
            </div>
        </div>

        <!-- Metadata & Decision Controls -->
        <div class="md:col-span-2 space-y-6">
            <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-3">
                <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">Candidate Description</h3>
                <p class="text-xs text-gray-300 leading-relaxed whitespace-pre-line">{{ $candidate->description ?: 'No synopsis or description provided by source.' }}</p>
            </div>

            @if($candidate->intake_status === 'staged')
                <!-- Review Action Form: Approve -->
                <div class="p-5 rounded-xl bg-emerald-950/20 border border-emerald-500/30 space-y-4">
                    <h3 class="text-sm font-bold text-emerald-400">Approve & Promote to Active Catalogue</h3>
                    <form method="POST" action="{{ route('admin.discovery.approve', $candidate->id) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-300 mb-1">Assign to Broadcast Channel</label>
                                <select name="channel_id" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                                    <option value="">Do Not Attach Channel</option>
                                    @foreach($channels as $channel)
                                        <option value="{{ $channel->id }}" {{ $candidate->channel_id == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-300 mb-1">Assign Category</label>
                                <select name="category_id" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                                    <option value="">Default Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $candidate->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                            ✓ Approve & Register into Broadcast Pool
                        </button>
                    </form>
                </div>

                <!-- Review Action Form: Reject -->
                <div class="p-5 rounded-xl bg-rose-950/20 border border-rose-500/30 space-y-3">
                    <h3 class="text-sm font-bold text-rose-400">Reject Candidate</h3>
                    <form method="POST" action="{{ route('admin.discovery.reject', $candidate->id) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-300 mb-1">Reason for Rejection *</label>
                            <input type="text" name="rejection_reason" required placeholder="e.g. Broken stream, low production quality, copyright ambiguity, irrelevant genre" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs rounded-xl transition">
                            ✕ Reject Candidate
                        </button>
                    </form>
                </div>
            @else
                <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800 text-xs text-gray-400 space-y-1">
                    <div class="font-semibold text-white">Review Audit History</div>
                    <div>Reviewed by: <span class="text-white">{{ $candidate->reviewer?->name ?: 'Automated Pipeline' }}</span></div>
                    <div>Reviewed at: <span class="text-white">{{ $candidate->reviewed_at?->format('M d, Y H:i:s') }}</span></div>
                    @if($candidate->approvedVideo)
                        <div class="mt-2">
                            Promoted to active Video: <a href="{{ route('admin.catalogue.show', $candidate->approvedVideo->id) }}" class="text-red-400 underline">#{{ $candidate->approvedVideo->id }} ({{ $candidate->approvedVideo->title }})</a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
