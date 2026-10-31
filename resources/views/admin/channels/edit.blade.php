@extends('admin.layout')

@section('title', 'Edit ' . $channel->name . ' - Station Configuration - Zoniva TV')

@section('admin_content')

<div class="max-w-4xl mx-auto space-y-6" x-data="{ isVirtual: {{ $channel->isVirtual() ? 'true' : 'false' }}, playbackMode: '{{ $channel->playback_mode }}' }">

    <div class="flex items-center justify-between pb-6 border-b border-gray-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold text-white shadow" style="background-color: {{ $channel->accent_color }};">
                    CH {{ $channel->display_number }}
                </span>
                <h1 class="text-2xl font-bold text-white">Edit Channel: {{ $channel->name }}</h1>
            </div>
            <p class="text-xs text-gray-400 mt-1">Configure station branding, playout priority rules, partner ownership, and on-air publication.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.channels.show', $channel->id) }}" class="text-xs text-indigo-400 hover:underline">
                Playout Monitor &rarr;
            </a>
            <a href="{{ route('admin.channels.index') }}" class="text-xs text-gray-400 hover:text-white">&larr; Catalogue</a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs space-y-1">
            <div class="font-bold">Please correct the following errors:</div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.channels.update', $channel->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 bg-[#12151c] border border-gray-800 rounded-2xl p-6 sm:p-8">
        @csrf
        @method('PUT')

        <!-- 1. Station Identity & Branding -->
        <div>
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                1. Station Identity & Branding
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Channel Name *</label>
                    <input type="text" name="name" value="{{ old('name', $channel->name) }}" required 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Channel Dial Number (LCN)</label>
                    <input type="text" name="channel_number" value="{{ old('channel_number', $channel->channel_number) }}" placeholder="e.g. 101" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Station Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $channel->tagline) }}" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Station Accent Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="brand_color" value="{{ old('brand_color', $channel->accent_color) }}" 
                               class="w-10 h-10 rounded-lg bg-gray-900 border border-gray-700 cursor-pointer p-0.5">
                        <input type="text" name="brand_color" value="{{ old('brand_color', $channel->accent_color) }}" 
                               class="w-full bg-gray-900 text-xs text-white px-3 py-2.5 rounded-lg border border-gray-700 font-mono">
                    </div>
                </div>

                <!-- Logo & Visual Branding -->
                <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase text-white">Logo</label>
                        <img src="{{ $channel->logo_url }}" class="w-8 h-8 object-contain rounded bg-gray-900 p-0.5 border" style="border-color: {{ $channel->accent_color }};">
                    </div>
                    <input type="text" name="logo_path" value="{{ old('logo_path', $channel->logo_path) }}" placeholder="URL or Cloudinary" 
                           class="w-full bg-gray-900 text-xs text-white px-3 py-2 rounded-lg border border-gray-700 focus:outline-none focus:border-red-500 font-mono">
                    <input type="file" name="logo_file" accept="image/*" class="text-[11px] text-gray-400">
                </div>

                <!-- Banner Backdrop -->
                <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase text-white">Banner (16:9)</label>
                        <img src="{{ $channel->banner_url }}" class="w-14 h-7 object-cover rounded bg-gray-900 border border-gray-700">
                    </div>
                    <input type="text" name="banner_path" value="{{ old('banner_path', $channel->banner_path) }}" placeholder="URL or Cloudinary" 
                           class="w-full bg-gray-900 text-xs text-white px-3 py-2 rounded-lg border border-gray-700 focus:outline-none focus:border-red-500 font-mono">
                    <input type="file" name="banner_file" accept="image/*" class="text-[11px] text-gray-400">
                </div>

                <!-- Watermark Bug -->
                <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-800 space-y-2">
                    <label class="block text-xs font-bold uppercase text-white">Watermark Bug</label>
                    <input type="text" name="watermark_logo_path" value="{{ old('watermark_logo_path', $channel->watermark_logo_path) }}" placeholder="Transparent PNG URL" 
                           class="w-full bg-gray-900 text-xs text-white px-3 py-2 rounded-lg border border-gray-700 focus:outline-none focus:border-red-500 font-mono">
                    <input type="file" name="watermark_file" accept="image/*" class="text-[11px] text-gray-400">
                </div>
            </div>
        </div>

        <!-- 2. Playout Architecture & Virtual Channel Configuration -->
        <div class="pt-6 border-t border-gray-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                2. Playout Mode & Virtual Engine
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Playback Mode Architecture *</label>
                    <select name="playback_mode" x-model="playbackMode" required class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                        <option value="zoniva_original" {{ $channel->playback_mode === 'zoniva_original' ? 'selected' : '' }}>Mode D: Zonita 24/7 Virtual Linear Playout</option>
                        <option value="embed_player" {{ $channel->playback_mode === 'embed_player' ? 'selected' : '' }}>Mode B: Official Broadcaster Embed Player (iframe / YouTube)</option>
                        <option value="direct_stream" {{ $channel->playback_mode === 'direct_stream' ? 'selected' : '' }}>Mode A: Authorised Direct Stream (HLS .m3u8)</option>
                        <option value="external_destination" {{ $channel->playback_mode === 'external_destination' ? 'selected' : '' }}>Mode C: Official External Destination</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center space-x-3 p-3 rounded-xl bg-gray-900/80 border border-gray-700 cursor-pointer w-full">
                        <input type="checkbox" name="is_virtual" value="1" x-model="isVirtual" {{ $channel->isVirtual() ? 'checked' : '' }} class="rounded bg-gray-800 border-gray-600 text-red-600 focus:ring-0">
                        <div>
                            <span class="text-xs font-bold text-white block">Automated 24/7 Virtual Channel</span>
                            <span class="text-[10px] text-gray-400 block">Maintain continuous rolling EPG playout schedule.</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Content Partner Attribution</label>
                    <select name="content_partner_id" class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                        <option value="">No Content Partner (Zonita Network Direct)</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('content_partner_id', $channel->content_partner_id) == $partner->id ? 'selected' : '' }}>{{ $partner->name }} ({{ $partner->country_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Official Destination URL *</label>
                    <input type="url" name="official_destination_url" value="{{ old('official_destination_url', $channel->official_destination_url) }}" required 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Emergency Fallback Slate Video ID</label>
                    <input type="text" name="fallback_video_id" value="{{ old('fallback_video_id', $channel->fallback_video_id) }}" placeholder="e.g. dQw4w9WgXcQ (Loop video if schedule has gap)" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Station Ident Bumper Video ID</label>
                    <input type="text" name="station_ident_video_id" value="{{ old('station_ident_video_id', $channel->station_ident_video_id) }}" placeholder="e.g. 5-10 sec station ident clip" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- 3. Playout Priority & Scheduling Rules -->
        @php $rule = $channel->sourceRule; @endphp
        <div class="pt-6 border-t border-gray-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                3. Playout Priority & Scheduling Rules
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Curation & Search Keywords (Comma separated)</label>
                    <input type="text" name="search_keywords" value="{{ old('search_keywords', is_array($rule?->search_keywords) ? implode(', ', $rule->search_keywords) : '') }}" placeholder="e.g. Nollywood Movies, African Drama, Yoruba Classics" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Excluded Keywords (Negative filter)</label>
                    <input type="text" name="excluded_keywords" value="{{ old('excluded_keywords', is_array($rule?->excluded_keywords) ? implode(', ', $rule->excluded_keywords) : '') }}" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Playout Rotation Priority Tier</label>
                    <select name="priority_level" class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                        <option value="balanced" {{ old('priority_level', $rule?->priority_level) === 'balanced' ? 'selected' : '' }}>Balanced (Standard Daypart Rotation)</option>
                        <option value="premier_heavy" {{ old('priority_level', $rule?->priority_level) === 'premier_heavy' ? 'selected' : '' }}>Premier Heavy (Prioritize Fresh Releases)</option>
                        <option value="deep_catalog" {{ old('priority_level', $rule?->priority_level) === 'deep_catalog' ? 'selected' : '' }}>Deep Catalog (Maximum Variety / Evergreen)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Daypart Scheduling Mode</label>
                    <select name="scheduling_mode" class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                        <option value="hybrid" {{ old('scheduling_mode', $rule?->scheduling_mode) === 'hybrid' ? 'selected' : '' }}>Hybrid (Fixed Blocks + Fill)</option>
                        <option value="continuous" {{ old('scheduling_mode', $rule?->scheduling_mode) === 'continuous' ? 'selected' : '' }}>Continuous (Sequential Playout)</option>
                        <option value="blocks" {{ old('scheduling_mode', $rule?->scheduling_mode) === 'blocks' ? 'selected' : '' }}>Blocks (Daypart Slots)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Repeat Cooldown (Hours)</label>
                    <input type="number" name="repeat_cooldown_hours" value="{{ old('repeat_cooldown_hours', $rule?->repeat_cooldown_hours ?? 8) }}" min="1" max="168" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Min Reserve Buffer Count</label>
                    <input type="number" name="min_reserve_count" value="{{ old('min_reserve_count', $rule?->min_reserve_count ?? 5) }}" min="1" max="50" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Min / Max Duration (Minutes)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_duration_minutes" value="{{ old('min_duration_minutes', $rule?->min_duration_minutes ?? 5) }}" placeholder="Min" class="w-full bg-gray-900 text-xs text-white px-3 py-2.5 rounded-lg border border-gray-700">
                        <input type="number" name="max_duration_minutes" value="{{ old('max_duration_minutes', $rule?->max_duration_minutes ?? 180) }}" placeholder="Max" class="w-full bg-gray-900 text-xs text-white px-3 py-2.5 rounded-lg border border-gray-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Playout Timezone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $rule?->timezone ?? 'Africa/Lagos') }}" required 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- 4. Categories & Meta -->
        <div class="pt-6 border-t border-gray-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                4. Taxonomy & Broadcast Settings
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Country Code</label>
                    <input type="text" name="country_code" value="{{ old('country_code', $channel->country_code) }}" maxlength="3" required 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 uppercase font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Language Code</label>
                    <input type="text" name="language_code" value="{{ old('language_code', $channel->language_code) }}" maxlength="10" required 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 lowercase font-mono">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Assign Categories</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @php $selectedCats = $channel->categories->pluck('id')->toArray(); @endphp
                        @foreach($categories as $cat)
                            <label class="flex items-center space-x-2 text-xs text-gray-300">
                                <input type="checkbox" name="categories[]" value="{{ $cat->id }}" {{ in_array($cat->id, $selectedCats) ? 'checked' : '' }} class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                                <span>{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">{{ old('description', $channel->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-800 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_published" value="1" {{ $channel->is_published ? 'checked' : '' }} class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>On-Air (Published)</span>
                </label>
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_featured" value="1" {{ $channel->is_featured ? 'checked' : '' }} class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>Featured</span>
                </label>
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_active" value="1" {{ $channel->is_active ? 'checked' : '' }} class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>Active</span>
                </label>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow transition">
                    Update Station
                </button>
            </div>
        </div>
    </form>

    <!-- Danger Zone: Archive Channel -->
    <div class="p-6 bg-red-950/20 border border-red-500/30 rounded-2xl flex items-center justify-between">
        <div>
            <h4 class="text-sm font-bold text-red-400">Archive / Soft-Delete Channel</h4>
            <p class="text-xs text-gray-400 mt-1">This will remove the channel from public directories and EPG guides while preserving broadcast history.</p>
        </div>
        <form action="{{ route('admin.channels.destroy', $channel->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this channel?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-900/60 hover:bg-red-800 text-red-200 border border-red-500/40 text-xs font-bold rounded-xl transition">
                Archive Channel
            </button>
        </form>
    </div>
</div>

@include('components.cloudinary-upload-widget')

@endsection
