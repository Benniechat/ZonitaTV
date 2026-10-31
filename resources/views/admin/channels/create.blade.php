@extends('admin.layout')

@section('title', 'Add New Channel - Zoniva TV')

@section('admin_content')

<div class="max-w-4xl mx-auto space-y-6" x-data="{ isVirtual: false, playbackMode: 'zoniva_original' }">

    <div class="flex items-center justify-between pb-6 border-b border-gray-800">
        <div>
            <h1 class="text-2xl font-bold text-white">Create Television Channel</h1>
            <p class="text-xs text-gray-400">Configure station branding, 24/7 virtual playout engine, licensing, and broadcast parameters.</p>
        </div>
        <a href="{{ route('admin.channels.index') }}" class="text-xs text-gray-400 hover:text-white">&larr; Back to Catalogue</a>
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

    <form action="{{ route('admin.channels.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 bg-[#12151c] border border-gray-800 rounded-2xl p-6 sm:p-8">
        @csrf

        <!-- 1. Station Identity & Branding -->
        <div>
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                1. Station Identity & Branding
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Channel Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Zonita Cinema Africa 24/7" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Channel Dial Number (LCN)</label>
                    <input type="text" name="channel_number" value="{{ old('channel_number') }}" placeholder="e.g. 101" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Station Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline') }}" placeholder="e.g. 24/7 African Masterpieces & Classic Cinema" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Station Accent Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="brand_color" value="{{ old('brand_color', '#E50914') }}" 
                               class="w-10 h-10 rounded-lg bg-gray-900 border border-gray-700 cursor-pointer p-0.5">
                        <input type="text" name="brand_color" value="{{ old('brand_color', '#E50914') }}" 
                               class="w-full bg-gray-900 text-xs text-white px-3 py-2.5 rounded-lg border border-gray-700 font-mono">
                    </div>
                </div>

                <!-- Logo & Visual Branding -->
                <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-800 space-y-2">
                    <label class="block text-xs font-bold uppercase text-white">Channel Logo</label>
                    <input type="text" name="logo_path" value="{{ old('logo_path') }}" placeholder="URL or Cloudinary" 
                           class="w-full bg-gray-900 text-xs text-white px-3 py-2 rounded-lg border border-gray-700 focus:outline-none focus:border-red-500 font-mono">
                    <input type="file" name="logo_file" accept="image/*" class="text-[11px] text-gray-400">
                </div>

                <!-- Banner Backdrop -->
                <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-800 space-y-2">
                    <label class="block text-xs font-bold uppercase text-white">Broadcast Banner (16:9)</label>
                    <input type="text" name="banner_path" value="{{ old('banner_path') }}" placeholder="URL or Cloudinary" 
                           class="w-full bg-gray-900 text-xs text-white px-3 py-2 rounded-lg border border-gray-700 focus:outline-none focus:border-red-500 font-mono">
                    <input type="file" name="banner_file" accept="image/*" class="text-[11px] text-gray-400">
                </div>

                <!-- Watermark Bug -->
                <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-800 space-y-2">
                    <label class="block text-xs font-bold uppercase text-white">Station Watermark Bug</label>
                    <input type="text" name="watermark_logo_path" value="{{ old('watermark_logo_path') }}" placeholder="Transparent PNG URL" 
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
                        <option value="zoniva_original">Mode D: Zonita 24/7 Virtual Linear Playout (Default)</option>
                        <option value="embed_player">Mode B: Official Broadcaster Embed Player (iframe / YouTube)</option>
                        <option value="direct_stream">Mode A: Authorised Direct Stream (HLS .m3u8)</option>
                        <option value="external_destination">Mode C: Official External Destination</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center space-x-3 p-3 rounded-xl bg-gray-900/80 border border-gray-700 cursor-pointer w-full">
                        <input type="checkbox" name="is_virtual" value="1" x-model="isVirtual" :checked="playbackMode === 'zoniva_original'" class="rounded bg-gray-800 border-gray-600 text-red-600 focus:ring-0">
                        <div>
                            <span class="text-xs font-bold text-white block">Automated 24/7 Virtual Channel</span>
                            <span class="text-[10px] text-gray-400 block">Enable algorithmic continuous scheduling with candidate reserve pool.</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Content Partner Attribution</label>
                    <select name="content_partner_id" class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                        <option value="">No Content Partner (Zonita Network Direct)</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('content_partner_id') == $partner->id ? 'selected' : '' }}>{{ $partner->name }} ({{ $partner->country_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Official Destination URL *</label>
                    <input type="url" name="official_destination_url" value="{{ old('official_destination_url', config('app.url') . '/channel') }}" required 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Emergency Fallback Slate Video ID</label>
                    <input type="text" name="fallback_video_id" value="{{ old('fallback_video_id') }}" placeholder="e.g. dQw4w9WgXcQ (Loop video if schedule has gap)" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Station Ident Bumper Video ID</label>
                    <input type="text" name="station_ident_video_id" value="{{ old('station_ident_video_id') }}" placeholder="e.g. 5-10 sec station ident clip" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Direct Stream URL (If Mode A HLS)</label>
                    <input type="url" name="stream_url" value="{{ old('stream_url') }}" placeholder="https://.../index.m3u8" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Embed Code (If Mode B iframe)</label>
                    <input type="text" name="embed_code" value="{{ old('embed_code') }}" placeholder="<iframe ...></iframe>" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- 3. Playout Priority & Scheduling Rules -->
        <div class="pt-6 border-t border-gray-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                3. Playout Priority & Scheduling Rules
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Curation & Search Keywords (Comma separated) *</label>
                    <input type="text" name="search_keywords" value="{{ old('search_keywords') }}" placeholder="e.g. Nollywood Movies, African Drama, Yoruba Classics" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Excluded Keywords (Negative filter)</label>
                    <input type="text" name="excluded_keywords" value="{{ old('excluded_keywords', 'trailer, promo, reaction, review, pack opening') }}" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Playout Rotation Priority Tier</label>
                    <select name="priority_level" class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                        <option value="balanced" {{ old('priority_level') === 'balanced' ? 'selected' : '' }}>Balanced (Standard Daypart Rotation)</option>
                        <option value="premier_heavy" {{ old('priority_level') === 'premier_heavy' ? 'selected' : '' }}>Premier Heavy (Prioritize Fresh & Trending Releases)</option>
                        <option value="deep_catalog" {{ old('priority_level') === 'deep_catalog' ? 'selected' : '' }}>Deep Catalog (Maximum Variety / Evergreen Fill)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Daypart Scheduling Mode</label>
                    <select name="scheduling_mode" class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                        <option value="hybrid">Hybrid (Fixed Premier Blocks + Continuous Fill)</option>
                        <option value="continuous">Continuous (Non-Stop Sequential Playout)</option>
                        <option value="blocks">Blocks (Strict Dayparts: Morning, Prime, Night)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Repeat Cooldown (Hours)</label>
                    <input type="number" name="repeat_cooldown_hours" value="{{ old('repeat_cooldown_hours', 8) }}" min="1" max="168" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Min Reserve Buffer Count</label>
                    <input type="number" name="min_reserve_count" value="{{ old('min_reserve_count', 5) }}" min="1" max="50" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Min / Max Duration (Minutes)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_duration_minutes" value="{{ old('min_duration_minutes', 5) }}" placeholder="Min" class="w-full bg-gray-900 text-xs text-white px-3 py-2.5 rounded-lg border border-gray-700">
                        <input type="number" name="max_duration_minutes" value="{{ old('max_duration_minutes', 180) }}" placeholder="Max" class="w-full bg-gray-900 text-xs text-white px-3 py-2.5 rounded-lg border border-gray-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Playout Timezone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', 'Africa/Lagos') }}" required 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 font-mono focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- 4. Geographic, Taxonomy & Rights -->
        <div class="pt-6 border-t border-gray-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                4. Taxonomy & Initial Rights
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Country Code (ISO-3)</label>
                    <input type="text" name="country_code" value="{{ old('country_code', 'NGA') }}" required maxlength="3" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 uppercase font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Primary Language Code</label>
                    <input type="text" name="language_code" value="{{ old('language_code', 'en') }}" required maxlength="10" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 lowercase font-mono">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Assign Broadcast Categories</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($categories as $cat)
                            <label class="flex items-center space-x-2 text-xs text-gray-300">
                                <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                                <span>{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Description</label>
                    <textarea name="description" rows="3" placeholder="Editorial synopsis of channel scope, target audience, and programming highlights..." 
                              class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-800 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_published" value="1" checked class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>Broadcast On-Air Immediately</span>
                </label>
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_featured" value="1" class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>Feature on Homepage Roster</span>
                </label>
            </div>

            <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow transition">
                Create & Initialize Station
            </button>
        </div>
    </form>
</div>

@include('components.cloudinary-upload-widget')

@endsection
