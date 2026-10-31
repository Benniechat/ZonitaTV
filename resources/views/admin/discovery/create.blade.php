@extends('admin.layout')

@section('title', 'Stage Candidate - Content Discovery Workbench')

@section('admin_content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Stage Content for Discovery Review</h1>
            <p class="text-xs text-gray-400 mt-1">Submit a video, playlist, or archive link for ingestion and deduplication checking.</p>
        </div>
        <a href="{{ route('admin.discovery.index') }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
            &larr; Back to Workbench
        </a>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.discovery.store') }}" class="space-y-6">
        @csrf

        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">Source URL & Origin</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Source URL *</label>
                    <input type="url" name="source_url" value="{{ old('source_url') }}" required placeholder="https://www.youtube.com/watch?v=... or https://archive.org/details/..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
                    <span class="text-[10px] text-gray-500 mt-1 block">YouTube watch/embed links or Internet Archive details URLs will be parsed automatically.</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Intake Mechanism *</label>
                        <select name="source_type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="youtube_video" {{ old('source_type') === 'youtube_video' ? 'selected' : '' }}>YouTube Video</option>
                            <option value="youtube_playlist" {{ old('source_type') === 'youtube_playlist' ? 'selected' : '' }}>YouTube Playlist</option>
                            <option value="internet_archive" {{ old('source_type') === 'internet_archive' ? 'selected' : '' }}>Internet Archive</option>
                            <option value="creator_submission" {{ old('source_type') === 'creator_submission' ? 'selected' : '' }}>Direct Creator Submission</option>
                            <option value="direct_url" {{ old('source_type') === 'direct_url' ? 'selected' : '' }}>Direct Stream URL</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Associated Content Provider</label>
                        <select name="content_provider_id" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="">None / Independent</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" {{ old('content_provider_id') == $provider->id ? 'selected' : '' }}>{{ $provider->name }} ({{ $provider->provider_type }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">Target Assignment & Rights</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Target Channel</label>
                    <select name="channel_id" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="">Unassigned (General Pool)</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel->id }}" {{ old('channel_id') == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Broadcast Category</label>
                    <select name="category_id" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="">Auto-infer from Channel</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Rights Classification *</label>
                    <select name="rights_basis" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="official_embed_permitted">Official Embed Permitted (YouTube API)</option>
                        <option value="public_domain">Public Domain Mark 1.0</option>
                        <option value="creative_commons">Creative Commons</option>
                        <option value="direct_licence">Direct Bilateral Licence</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Custom Title Override (Optional)</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Leave blank to use source title" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.discovery.index') }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-lg transition">
                Stage Candidate for Ingestion
            </button>
        </div>
    </form>
</div>
@endsection
