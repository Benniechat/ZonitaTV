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

    <form action="{{ route('admin.channels.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 bg-[#12151c] border border-gray-800 rounded-2xl p-6 sm:p-8">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Channel Name *</label>
                <input type="text" name="name" required placeholder="e.g. Channels Television" 
                       class="w-full bg-gray-900 text-sm text-white px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
            </div>

            <!-- Logo & Visual Branding (Cloudinary Powered) -->
            <div class="p-5 bg-gray-900/50 rounded-xl border border-gray-800 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Channel Logo
                    </label>
                    <button type="button" 
                            onclick="openCloudinaryWidget({ folder: 'zonita/logos', cropping: true, croppingAspectRatio: 1, targetInputId: 'logo_path_input', previewImageId: 'logo_preview' })"
                            class="px-2.5 py-1 bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-[10px] uppercase rounded-lg transition shadow">
                        Upload with Cloudinary
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <img id="logo_preview" src="{{ asset('images/logo/zonitatvlogo2.png') }}" class="w-14 h-14 object-contain rounded-xl bg-gray-900 border border-gray-700 p-1.5 shrink-0">
                    <div class="flex-1 space-y-1.5">
                        <input type="text" id="logo_path_input" name="logo_path" placeholder="Cloudinary URL or static path" 
                               class="w-full bg-gray-900 text-xs text-white px-3 py-2 rounded-lg border border-gray-700 focus:outline-none focus:border-amber-500 font-mono">
                        <div class="flex items-center justify-between text-[11px] text-gray-500">
                            <span>Or select a file from computer:</span>
                            <input type="file" name="logo_file" accept="image/*" class="text-[11px] text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:bg-gray-800 file:text-gray-300 hover:file:bg-gray-700">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Backdrop (Cloudinary Powered) -->
            <div class="p-5 bg-gray-900/50 rounded-xl border border-gray-800 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                        Channel Banner / Backdrop
                    </label>
                    <button type="button" 
                            onclick="openCloudinaryWidget({ folder: 'zonita/banners', cropping: true, croppingAspectRatio: 1.7777777, targetInputId: 'banner_path_input', previewImageId: 'banner_preview' })"
                            class="px-2.5 py-1 bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-[10px] uppercase rounded-lg transition shadow">
                        Upload with Cloudinary
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <img id="banner_preview" src="" class="w-20 h-11 object-cover rounded-lg bg-gray-900 border border-gray-700 shrink-0 hidden">
                    <div class="flex-1 space-y-1.5">
                        <input type="text" id="banner_path_input" name="banner_path" placeholder="Cloudinary URL (16:9 banner)" 
                               class="w-full bg-gray-900 text-xs text-white px-3 py-2 rounded-lg border border-gray-700 focus:outline-none focus:border-amber-500 font-mono">
                        <div class="flex items-center justify-between text-[11px] text-gray-500">
                            <span>Or select a file from computer:</span>
                            <input type="file" name="banner_file" accept="image/*" class="text-[11px] text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:bg-gray-800 file:text-gray-300 hover:file:bg-gray-700">
                        </div>
                    </div>
                </div>
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

@include('components.cloudinary-upload-widget')

@endsection
