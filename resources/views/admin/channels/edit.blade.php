@extends('admin.layout')

@section('title', 'Edit ' . $channel->name . ' - Zoniva TV')

@section('admin_content')

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between pb-6 border-b border-gray-800">
        <div>
            <h1 class="text-2xl font-bold text-white">Edit Channel: {{ $channel->name }}</h1>
            <p class="text-xs text-gray-400">Update channel metadata, active playback modes, and publication state.</p>
        </div>
        <a href="{{ route('admin.channels.index') }}" class="text-xs text-gray-400 hover:text-white">&larr; Back to Catalogue</a>
    </div>

    <form action="{{ route('admin.channels.update', $channel->id) }}" method="POST" class="space-y-6 bg-[#12151c] border border-gray-800 rounded-2xl p-6 sm:p-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Channel Name *</label>
                <input type="text" name="name" value="{{ old('name', $channel->name) }}" required 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Tagline</label>
                <input type="text" name="tagline" value="{{ old('tagline', $channel->tagline) }}" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Broadcaster Name</label>
                <input type="text" name="broadcaster_name" value="{{ old('broadcaster_name', $channel->broadcaster_name) }}" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Official Broadcaster Website</label>
                <input type="url" name="broadcaster_website" value="{{ old('broadcaster_website', $channel->broadcaster_website) }}" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Official Destination URL (Mode C Fallback) *</label>
                <input type="url" name="official_destination_url" value="{{ old('official_destination_url', $channel->official_destination_url) }}" required 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Country Code</label>
                <input type="text" name="country_code" value="{{ old('country_code', $channel->country_code) }}" maxlength="3" required 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 uppercase">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Language Code</label>
                <input type="text" name="language_code" value="{{ old('language_code', $channel->language_code) }}" maxlength="10" required 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 lowercase">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Playback Mode Architecture</label>
                <select name="playback_mode" required class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                    <option value="external_destination" {{ $channel->playback_mode === 'external_destination' ? 'selected' : '' }}>Mode C: Official External Broadcaster Destination</option>
                    <option value="embed_player" {{ $channel->playback_mode === 'embed_player' ? 'selected' : '' }}>Mode B: Official Broadcaster Embed Player (iframe)</option>
                    <option value="direct_stream" {{ $channel->playback_mode === 'direct_stream' ? 'selected' : '' }}>Mode A: Authorised Direct Stream (HLS.js)</option>
                    <option value="zoniva_original" {{ $channel->playback_mode === 'zoniva_original' ? 'selected' : '' }}>Mode D: Zoniva Originals</option>
                </select>
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

        <div class="pt-6 border-t border-gray-800 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_published" value="1" {{ $channel->is_published ? 'checked' : '' }} class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>Published</span>
                </label>
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_featured" value="1" {{ $channel->is_featured ? 'checked' : '' }} class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>Featured</span>
                </label>
            </div>

            <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow transition">
                Update Channel
            </button>
        </div>

    </form>

</div>

@endsection
