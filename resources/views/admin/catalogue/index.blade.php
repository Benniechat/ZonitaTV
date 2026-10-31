@extends('admin.layout')

@section('title', 'Content Catalogue - Zoniva TV Administration')

@section('admin_content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Content Database & Catalogue</h1>
            <p class="text-xs text-gray-400 mt-1">Unified metadata repository for videos, feature films, episodic series, and licensing status.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.catalogue.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Content Asset</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Videos</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($metrics['total_videos']) }}</div>
            <div class="text-[10px] text-emerald-400 mt-1">Verified Playout Assets</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Feature Movies</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($metrics['total_movies']) }}</div>
            <div class="text-[10px] text-amber-400 mt-1">Licensed & Public Domain</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Series / Episodes</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($metrics['total_series']) }} / {{ number_format($metrics['total_episodes']) }}</div>
            <div class="text-[10px] text-indigo-400 mt-1">Episodic Franchises</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Archived / Trashed</div>
            <div class="text-2xl font-black text-rose-400 mt-1">{{ number_format($metrics['trashed_videos']) }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Soft Deleted</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 rounded-xl bg-[#10131a] border border-gray-800 space-y-3">
        <!-- Sub-navigation tabs: Videos, Movies, Series -->
        <div class="flex items-center gap-2 border-b border-gray-800/80 pb-3">
            <a href="{{ route('admin.catalogue.index', ['type' => 'videos']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $type === 'videos' ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Videos ({{ $metrics['total_videos'] }})
            </a>
            <a href="{{ route('admin.catalogue.index', ['type' => 'movies']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $type === 'movies' ? 'bg-amber-600/20 text-amber-400 border border-amber-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Movies ({{ $metrics['total_movies'] }})
            </a>
            <a href="{{ route('admin.catalogue.index', ['type' => 'series']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $type === 'series' ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Series & Episodes ({{ $metrics['total_series'] }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.catalogue.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="sm:col-span-2">
                <input type="text" name="q" value="{{ $query }}" placeholder="Search title, creator, YouTube ID, keywords..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
            </div>
            <div>
                <select name="status" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    <option value="">All Statuses</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="discovered" {{ $status === 'discovered' ? 'selected' : '' }}>Discovered</option>
                    <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="restricted" {{ $status === 'restricted' ? 'selected' : '' }}>Restricted</option>
                    <option value="disabled" {{ $status === 'disabled' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-1.5 text-xs text-gray-400 cursor-pointer">
                    <input type="checkbox" name="trashed" value="1" {{ $showTrashed ? 'checked' : '' }} class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0">
                    <span>Trashed</span>
                </label>
                <button type="submit" class="ml-auto px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="rounded-xl border border-gray-800 bg-[#0d1017] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300">
                <thead class="bg-[#12151c] text-gray-400 font-semibold uppercase text-[10px] tracking-wider border-b border-gray-800">
                    <tr>
                        <th class="py-3 px-4">Asset</th>
                        <th class="py-3 px-4">Metadata</th>
                        <th class="py-3 px-4">Rating / Advisory</th>
                        <th class="py-3 px-4">Status & Rights</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-800/30 transition">
                            <!-- Asset column -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-16 h-10 rounded bg-gray-900 overflow-hidden flex-shrink-0 border border-gray-800">
                                        <img src="{{ $item->thumbnail_url ?? $item->poster_url ?? asset('images/logo/zonitatvlogo2.png') }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ $type === 'videos' ? route('admin.catalogue.show', $item->id) : '#' }}" class="font-bold text-white hover:text-red-400 transition truncate block max-w-xs">
                                            {{ $item->title }}
                                        </a>
                                        <span class="text-[10px] text-gray-500 block truncate">
                                            {{ $item->channel_title ?? $item->source_provider ?? $item->genre ?? 'General' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Metadata column -->
                            <td class="py-3 px-4 space-y-0.5">
                                <div class="text-[11px] text-gray-300">
                                    {{ gmdate('H:i:s', $item->duration_seconds ?? 0) }} • {{ $item->resolution ?? '1080p' }} ({{ $item->aspect_ratio ?? '16:9' }})
                                </div>
                                <div class="text-[10px] text-gray-500">
                                    Lang: {{ strtoupper($item->language_code ?? $item->language ?? 'EN') }} | Geo: {{ strtoupper($item->country_code ?? 'ALL') }}
                                </div>
                            </td>

                            <!-- Rating / Advisory column -->
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-800 text-gray-300 border border-gray-700">
                                    {{ $item->content_rating ?? 'PG' }}
                                </span>
                                @if(!empty($item->advisory_tags) && is_array($item->advisory_tags))
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach(array_slice($item->advisory_tags, 0, 2) as $tag)
                                            <span class="text-[9px] px-1 rounded bg-amber-500/10 text-amber-400">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            <!-- Status & Rights column -->
                            <td class="py-3 px-4">
                                @php
                                    $statusVal = $item->availability_status ?? $item->verification_status ?? $item->status ?? 'active';
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ in_array($statusVal, ['approved', 'verified', 'running', 'completed']) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : (in_array($statusVal, ['disabled', 'rejected', 'failed_verification']) ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30') }}">
                                    {{ str_replace('_', ' ', $statusVal) }}
                                </span>
                                <div class="text-[9px] text-gray-500 mt-0.5">
                                    {{ $item->rights_basis ?? $item->rights_classification ?? 'official_embed' }}
                                </div>
                            </td>

                            <!-- Actions column -->
                            <td class="py-3 px-4 text-right">
                                @if($type === 'videos')
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.catalogue.show', $item->id) }}" class="p-1 rounded hover:bg-gray-800 text-gray-400 hover:text-white" title="View details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('admin.catalogue.edit', $item->id) }}" class="p-1 rounded hover:bg-gray-800 text-gray-400 hover:text-white" title="Edit metadata">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if($item->trashed())
                                            <form method="POST" action="{{ route('admin.catalogue.restore', $item->id) }}" class="inline" onsubmit="return confirm('Restore this asset to active catalogue?');">
                                                @csrf
                                                <button type="submit" class="p-1 rounded hover:bg-emerald-800/50 text-emerald-400" title="Restore asset">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.catalogue.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Soft delete this asset from catalogue?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded hover:bg-red-800/50 text-red-400" title="Soft delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                No catalogue items matched the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-4 border-t border-gray-800 bg-[#0d1017]">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
