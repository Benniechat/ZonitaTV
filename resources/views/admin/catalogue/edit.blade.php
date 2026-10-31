@extends('admin.layout')

@section('title', 'Edit ' . $video->title . ' - Zoniva TV Administration')

@section('admin_content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Edit Content Metadata</h1>
            <p class="text-xs text-gray-400 mt-1">Updating metadata and playout status for #{{ $video->id }} ({{ $video->title }})</p>
        </div>
        <a href="{{ route('admin.catalogue.show', $video->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
            &larr; Cancel
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs space-y-1">
            <div class="font-bold">Please correct the following errors:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.catalogue.update', $video->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Core Details -->
        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">1. Primary Asset Metadata</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $video->title) }}" required class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">YouTube Video ID (Read-only)</label>
                    <input type="text" value="{{ $video->youtube_id }}" disabled class="w-full bg-[#12151c] border border-gray-800 rounded-lg px-3 py-2 text-xs text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Channel / Creator Name</label>
                    <input type="text" name="channel_title" value="{{ old('channel_title', $video->channel_title) }}" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Description / Synopsis</label>
                    <textarea name="description" rows="3" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">{{ old('description', $video->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Thumbnail URL</label>
                    <input type="url" name="thumbnail_url" value="{{ old('thumbnail_url', $video->thumbnail_url) }}" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Direct Media URL (Optional)</label>
                    <input type="url" name="media_url" value="{{ old('media_url', $video->media_url) }}" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Technical & Broadcast Attributes -->
        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">2. Technical & Playout Specifications</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Duration (Seconds) *</label>
                    <input type="number" name="duration_seconds" value="{{ old('duration_seconds', $video->duration_seconds) }}" required min="0" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Aspect Ratio *</label>
                    <select name="aspect_ratio" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="16:9" {{ old('aspect_ratio', $video->aspect_ratio) === '16:9' ? 'selected' : '' }}>16:9 (Standard Widescreen)</option>
                        <option value="4:3" {{ old('aspect_ratio', $video->aspect_ratio) === '4:3' ? 'selected' : '' }}>4:3 (Classic Television)</option>
                        <option value="21:9" {{ old('aspect_ratio', $video->aspect_ratio) === '21:9' ? 'selected' : '' }}>21:9 (Cinematic Ultrawide)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Resolution *</label>
                    <select name="resolution" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="1080p" {{ old('resolution', $video->resolution) === '1080p' ? 'selected' : '' }}>1080p (Full HD)</option>
                        <option value="720p" {{ old('resolution', $video->resolution) === '720p' ? 'selected' : '' }}>720p (HD)</option>
                        <option value="4k" {{ old('resolution', $video->resolution) === '4k' ? 'selected' : '' }}>4K (Ultra HD)</option>
                        <option value="480p" {{ old('resolution', $video->resolution) === '480p' ? 'selected' : '' }}>480p (Standard Definition)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Content Rating *</label>
                    <select name="content_rating" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="PG" {{ old('content_rating', $video->content_rating) === 'PG' ? 'selected' : '' }}>PG (Parental Guidance)</option>
                        <option value="G" {{ old('content_rating', $video->content_rating) === 'G' ? 'selected' : '' }}>G (General Audience)</option>
                        <option value="13+" {{ old('content_rating', $video->content_rating) === '13+' ? 'selected' : '' }}>13+ (Teens)</option>
                        <option value="16+" {{ old('content_rating', $video->content_rating) === '16+' ? 'selected' : '' }}>16+ (Mature)</option>
                        <option value="18+" {{ old('content_rating', $video->content_rating) === '18+' ? 'selected' : '' }}>18+ (Adults Only)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Format Type *</label>
                    <select name="format_type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="feature" {{ old('format_type', $video->format_type) === 'feature' ? 'selected' : '' }}>Feature Film / Program</option>
                        <option value="short" {{ old('format_type', $video->format_type) === 'short' ? 'selected' : '' }}>Short Video</option>
                        <option value="clip" {{ old('format_type', $video->format_type) === 'clip' ? 'selected' : '' }}>Broadcast Clip</option>
                        <option value="stream" {{ old('format_type', $video->format_type) === 'stream' ? 'selected' : '' }}>Live Stream Capture</option>
                        <option value="episode" {{ old('format_type', $video->format_type) === 'episode' ? 'selected' : '' }}>Series Episode</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Category Slug</label>
                    <select name="category_slug" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="">None / General</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ old('category_slug', $video->category_slug) === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Advisory Tags (comma-separated)</label>
                    @php
                        $tagsStr = is_array($video->advisory_tags) ? implode(', ', $video->advisory_tags) : '';
                    @endphp
                    <input type="text" name="advisory_tags" value="{{ old('advisory_tags', $tagsStr) }}" placeholder="e.g. Mild Violence, Flashing Lights" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Rights, Language & Geography -->
        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">3. Rights Governance & Origin</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Language Code *</label>
                    <input type="text" name="language_code" value="{{ old('language_code', $video->language_code) }}" required maxlength="10" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Origin Country Code *</label>
                    <input type="text" name="country_code" value="{{ old('country_code', $video->country_code) }}" required maxlength="3" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Rights Basis *</label>
                    <select name="rights_basis" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="official_embed_permitted" {{ old('rights_basis', $video->rights_basis) === 'official_embed_permitted' ? 'selected' : '' }}>Official Embed Permitted (YouTube API)</option>
                        <option value="public_domain" {{ old('rights_basis', $video->rights_basis) === 'public_domain' ? 'selected' : '' }}>Public Domain</option>
                        <option value="creative_commons" {{ old('rights_basis', $video->rights_basis) === 'creative_commons' ? 'selected' : '' }}>Creative Commons</option>
                        <option value="direct_licence" {{ old('rights_basis', $video->rights_basis) === 'direct_licence' ? 'selected' : '' }}>Direct Bilateral Agreement</option>
                        <option value="under_review" {{ old('rights_basis', $video->rights_basis) === 'under_review' ? 'selected' : '' }}>Under Legal Review</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Availability Status *</label>
                    <select name="availability_status" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="approved" {{ old('availability_status', $video->availability_status) === 'approved' ? 'selected' : '' }}>Approved for Playout</option>
                        <option value="discovered" {{ old('availability_status', $video->availability_status) === 'discovered' ? 'selected' : '' }}>Discovered (Queue)</option>
                        <option value="scheduled" {{ old('availability_status', $video->availability_status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="restricted" {{ old('availability_status', $video->availability_status) === 'restricted' ? 'selected' : '' }}>Restricted</option>
                        <option value="disabled" {{ old('availability_status', $video->availability_status) === 'disabled' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 md:col-span-2 pt-5">
                    <label class="flex items-center gap-2 text-xs text-gray-300 cursor-pointer">
                        <input type="checkbox" name="is_embeddable" value="1" {{ old('is_embeddable', $video->is_embeddable) ? 'checked' : '' }} class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0">
                        <span>Embeddable in Web Player</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-gray-300 cursor-pointer">
                        <input type="checkbox" name="is_evergreen" value="1" {{ old('is_evergreen', $video->is_evergreen) ? 'checked' : '' }} class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0">
                        <span>Evergreen (Eligible for Continuous Auto-Rotation)</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.catalogue.show', $video->id) }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-lg transition">
                Update Metadata
            </button>
        </div>
    </form>
</div>
@endsection
