@extends('admin.layout')

@section('title', $video->title . ' - Content Metadata Details')

@section('admin_content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-extrabold text-white">{{ $video->title }}</h1>
                @if($video->trashed())
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase">Trashed</span>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-1">Asset ID: #{{ $video->id }} • YouTube ID: <code class="text-red-400">{{ $video->youtube_id }}</code></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.catalogue.edit', $video->id) }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-white transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Metadata</span>
            </a>
            <a href="{{ route('admin.catalogue.index') }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
                &larr; Back
            </a>
        </div>
    </div>

    <!-- Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Thumbnail Preview & Embed -->
        <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <div class="aspect-video w-full rounded-lg overflow-hidden bg-black border border-gray-800 relative">
                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full h-full object-cover">
                <div class="absolute bottom-2 right-2 px-2 py-0.5 rounded bg-black/80 text-[10px] font-bold text-white">
                    {{ gmdate('H:i:s', $video->duration_seconds) }}
                </div>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-gray-800 text-gray-400">
                    <span>Aspect Ratio:</span>
                    <span class="font-bold text-white">{{ $video->aspect_ratio ?? '16:9' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-800 text-gray-400">
                    <span>Resolution:</span>
                    <span class="font-bold text-white">{{ $video->resolution ?? '1080p' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-800 text-gray-400">
                    <span>Content Rating:</span>
                    <span class="font-bold text-amber-400">{{ $video->content_rating ?? 'PG' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-800 text-gray-400">
                    <span>Language / Country:</span>
                    <span class="font-bold text-white">{{ strtoupper($video->language_code ?? 'EN') }} / {{ strtoupper($video->country_code ?? 'NGA') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-800 text-gray-400">
                    <span>Embeddable:</span>
                    <span class="font-bold {{ $video->is_embeddable ? 'text-emerald-400' : 'text-rose-400' }}">{{ $video->is_embeddable ? 'YES' : 'NO' }}</span>
                </div>
                <div class="flex justify-between py-1 text-gray-400">
                    <span>Confidence Score:</span>
                    <span class="font-bold text-emerald-400">{{ $video->confidence_score }}/100</span>
                </div>
            </div>

            @if(!empty($video->advisory_tags))
                <div class="pt-2 border-t border-gray-800">
                    <span class="text-[10px] text-gray-400 font-semibold block mb-1.5 uppercase tracking-wider">Advisory Tags</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($video->advisory_tags as $tag)
                            <span class="px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-amber-300 text-[10px] font-medium">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Detailed Specifications -->
        <div class="md:col-span-2 space-y-6">
            <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
                <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">Description & Playout Basis</h3>
                <p class="text-xs text-gray-300 leading-relaxed whitespace-pre-line">{{ $video->description ?: 'No detailed description recorded.' }}</p>

                <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-800 text-xs">
                    <div>
                        <span class="text-gray-500 text-[11px] block">Broadcaster / Channel Title</span>
                        <span class="font-semibold text-white">{{ $video->channel_title ?: 'Independent / Public' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 text-[11px] block">Rights & Licensing Basis</span>
                        <span class="font-semibold text-red-400">{{ $video->rights_basis }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 text-[11px] block">Format Classification</span>
                        <span class="font-semibold text-white capitalize">{{ $video->format_type }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 text-[11px] block">Availability Status</span>
                        <span class="font-semibold text-emerald-400 uppercase">{{ $video->availability_status }}</span>
                    </div>
                </div>
            </div>

            <!-- Content Relationships Section -->
            <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-3">
                <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">Linked Catalog Entities</h3>
                
                <div class="space-y-2 text-xs">
                    @if($video->movies->isNotEmpty())
                        <div class="text-gray-400">
                            <span class="font-semibold text-white">Movie Association:</span>
                            @foreach($video->movies as $movie)
                                <a href="{{ route('movies.show', $movie->slug) }}" target="_blank" class="text-amber-400 underline hover:text-white ml-1">
                                    {{ $movie->title }} ({{ $movie->release_year }})
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($video->episodes->isNotEmpty())
                        <div class="text-gray-400">
                            <span class="font-semibold text-white">Series Episodes:</span>
                            @foreach($video->episodes as $episode)
                                <span class="ml-1 text-indigo-400">
                                    {{ $episode->season?->series?->title }} — S{{ $episode->season?->season_number }}E{{ $episode->episode_number }}: {{ $episode->title }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if($video->movies->isEmpty() && $video->episodes->isEmpty())
                        <div class="text-gray-500 text-xs italic">
                            Standalone video asset; not currently mapped to an episodic series or feature catalogue entry.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
