@extends('admin.layout')

@section('title', 'Add New Channel - Zoniva TV')

@section('admin_content')

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between pb-6 border-b border-gray-800">
        <div>
            <h1 class="text-2xl font-bold text-white">Add New Television Channel</h1>
            <p class="text-xs text-gray-400">Configure channel metadata, compliant viewing mode, and initial permissions record.</p>
        </div>
        <a href="{{ route('admin.channels.index') }}" class="text-xs text-gray-400 hover:text-white">&larr; Back to Catalogue</a>
    </div>

    <form action="{{ route('admin.channels.store') }}" method="POST" class="space-y-6 bg-[#12151c] border border-gray-800 rounded-2xl p-6 sm:p-8">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Channel Name *</label>
                <input type="text" name="name" required placeholder="e.g. Channels Television" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Tagline</label>
                <input type="text" name="tagline" placeholder="e.g. Your Home for the News" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Broadcaster Name</label>
                <input type="text" name="broadcaster_name" placeholder="e.g. Channels Incorporated Ltd" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Official Broadcaster Website</label>
                <input type="url" name="broadcaster_website" placeholder="https://channelstv.com" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Official Destination URL (Mode C Fallback) *</label>
                <input type="url" name="official_destination_url" required placeholder="https://channelstv.com/live" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Country Code (ISO 3-Letter)</label>
                <input type="text" name="country_code" value="NGA" maxlength="3" required 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 uppercase">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Language Code</label>
                <input type="text" name="language_code" value="en" maxlength="10" required 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 lowercase">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Playback Mode Architecture *</label>
                <select name="playback_mode" required class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                    <option value="external_destination">Mode C: Official External Broadcaster Destination (Default)</option>
                    <option value="embed_player">Mode B: Official Broadcaster Embed Player (iframe)</option>
                    <option value="direct_stream">Mode A: Authorised Direct Stream (HLS.js Inside Zoniva)</option>
                    <option value="zoniva_original">Mode D: Zoniva Originals (Direct Media)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Direct Stream URL (HLS .m3u8)</label>
                <input type="url" name="stream_url" placeholder="https://example.com/live/index.m3u8" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Embed Code (If Mode B)</label>
                <textarea name="embed_code" rows="3" placeholder="<iframe ...></iframe>" 
                          class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 font-mono"></textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Assign Categories</label>
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
                <textarea name="description" rows="3" placeholder="Brief editorial summary of the channel..." 
                          class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500"></textarea>
            </div>

        </div>

        <!-- Rights Attachment -->
        <div class="pt-6 border-t border-gray-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Initial Rights & Legal Review</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Rights Holder Name *</label>
                    <input type="text" name="rights_holder_name" required placeholder="Broadcaster Legal Entity" 
                           class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Rights Review Decision *</label>
                    <select name="rights_status" required class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700">
                        <option value="directory_approved">Directory Approved (Listing permitted)</option>
                        <option value="embedding_approved">Embedding Approved (Official embed permitted)</option>
                        <option value="redistribution_approved">Redistribution Approved (Direct stream permitted)</option>
                        <option value="pending_review">Pending Review (Hold publication)</option>
                        <option value="rejected">Rejected (Unauthorised source)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-800 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_published" value="1" checked class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>Publish to Directory Immediately</span>
                </label>
                <label class="flex items-center space-x-2 text-xs text-gray-300">
                    <input type="checkbox" name="is_featured" value="1" class="rounded bg-gray-900 border-gray-700 text-red-600 focus:ring-0">
                    <span>Feature on Homepage</span>
                </label>
            </div>

            <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow transition">
                Create & Save Channel
            </button>
        </div>

    </form>

</div>

@endsection
