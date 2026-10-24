@extends('admin.layout')

@section('title', 'Media Storage & Cloudinary - Zoniva TV')

@section('admin_content')

<div class="max-w-7xl mx-auto space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-gray-800">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Cloudinary Media Storage & Delivery
            </h1>
            <p class="text-xs text-gray-400 mt-1">
                Manage cloud assets, upload channel logos and banners, optimize video thumbnails, and monitor CDN delivery.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" 
                    onclick="openCloudinaryWidget({ folder: 'zonita/media', cropping: false, onSuccess: (r) => window.location.reload() })"
                    class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-black font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-orange-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Launch Upload Widget
            </button>
        </div>
    </div>

    <!-- Cloudinary Configuration Card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Cloud Name</span>
            <span class="text-lg font-mono font-bold text-white">{{ $config['cloud_name'] ?: 'Not Configured' }}</span>
        </div>
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-1">API Key</span>
            <span class="text-lg font-mono font-bold text-amber-400">{{ substr($config['api_key'], 0, 6) }}••••••••</span>
        </div>
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Upload Mode</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $config['has_secret'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $config['has_secret'] ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                {{ $config['has_secret'] ? 'Signed Uploads (Full Secret)' : 'Preset Mode (' . $config['upload_preset'] . ')' }}
            </span>
        </div>
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-1">CDN Optimizations</span>
            <span class="text-xs font-medium text-emerald-400 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                f_auto, q_auto Enabled
            </span>
        </div>
    </div>

    <!-- Quick Action Presets -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Quick Upload Presets</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <button type="button" 
                    onclick="openCloudinaryWidget({ folder: 'zonita/logos', cropping: true, croppingAspectRatio: 1, onSuccess: handleUploadComplete })"
                    class="p-4 rounded-xl bg-gray-900 hover:bg-gray-800/80 border border-gray-700/60 transition text-left flex items-start gap-4 group">
                <div class="w-12 h-12 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 shrink-0 group-hover:scale-105 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Channel Logo</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Square 1:1 aspect ratio with auto-crop & transparent PNG/SVG support.</p>
                </div>
            </button>

            <button type="button" 
                    onclick="openCloudinaryWidget({ folder: 'zonita/banners', cropping: true, croppingAspectRatio: 1.7777777, onSuccess: handleUploadComplete })"
                    class="p-4 rounded-xl bg-gray-900 hover:bg-gray-800/80 border border-gray-700/60 transition text-left flex items-start gap-4 group">
                <div class="w-12 h-12 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0 group-hover:scale-105 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Channel Banner</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">16:9 widescreen stage backdrop with adaptive resolution.</p>
                </div>
            </button>

            <button type="button" 
                    onclick="openCloudinaryWidget({ folder: 'zonita/thumbnails', cropping: true, croppingAspectRatio: 1.7777777, onSuccess: handleUploadComplete })"
                    class="p-4 rounded-xl bg-gray-900 hover:bg-gray-800/80 border border-gray-700/60 transition text-left flex items-start gap-4 group">
                <div class="w-12 h-12 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 shrink-0 group-hover:scale-105 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Video Thumbnail</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Custom video preview card for EPG guide and homepage carousels.</p>
                </div>
            </button>

        </div>

        <!-- Latest Upload Toast / Display -->
        <div id="latest-upload-box" class="hidden mt-6 p-4 rounded-xl bg-emerald-950/30 border border-emerald-500/30 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img id="latest-upload-preview" src="" class="w-16 h-12 object-cover rounded-lg border border-gray-700">
                <div>
                    <h4 class="text-xs font-bold text-white">Upload Succeeded</h4>
                    <p id="latest-upload-url" class="text-[11px] text-emerald-400 font-mono break-all"></p>
                </div>
            </div>
            <button type="button" 
                    onclick="copyLatestUrl()" 
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg transition shrink-0">
                Copy CDN URL
            </button>
        </div>
    </div>

    <!-- Channels Media Asset Registry -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider">Channel Logos & Banners</h2>
                <p class="text-xs text-gray-400">Current visual branding assets attached to television channels.</p>
            </div>
            <a href="{{ route('admin.channels.index') }}" class="text-xs text-gray-400 hover:text-white">&rarr; Channel Manager</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300">
                <thead class="text-[10px] text-gray-500 uppercase bg-gray-900/60 border-b border-gray-800">
                    <tr>
                        <th class="py-3 px-4">Channel</th>
                        <th class="py-3 px-4">Logo</th>
                        <th class="py-3 px-4">Banner Backdrop</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse($channels as $ch)
                        <tr class="hover:bg-gray-900/30">
                            <td class="py-3 px-4 font-semibold text-white">
                                {{ $ch->name }}
                                <span class="block text-[10px] text-gray-500 font-mono">{{ $ch->slug }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($ch->logo_path)
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $ch->logo_url }}" class="w-10 h-10 object-contain rounded-lg bg-gray-900 border border-gray-800 p-1">
                                        <span class="text-[11px] text-gray-400 font-mono truncate max-w-xs">{{ $ch->logo_path }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Default icon</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($ch->banner_path)
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $ch->banner_url }}" class="w-16 h-9 object-cover rounded-md border border-gray-800">
                                        <span class="text-[11px] text-gray-400 font-mono truncate max-w-xs">{{ $ch->banner_path }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">None</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.channels.edit', $ch->id) }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
                                    Edit Assets &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500 italic">No channels found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Video Pool Thumbnails -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider">Video Thumbnails & Dynamic CDN</h2>
                <p class="text-xs text-gray-400">Thumbnails delivered through Cloudinary's dynamic image fetch engine.</p>
            </div>
            <a href="{{ route('admin.broadcasting.index') }}" class="text-xs text-gray-400 hover:text-white">&rarr; Broadcasting Ledger</a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @forelse($videos as $v)
                <div class="bg-gray-900/60 rounded-xl overflow-hidden border border-gray-800 flex flex-col group">
                    <div class="relative aspect-video w-full bg-black overflow-hidden">
                        <img src="{{ $v->thumbnail_url }}" alt="{{ $v->title }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                        <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded text-[9px] font-mono bg-black/80 text-emerald-400">
                            CDN
                        </span>
                    </div>
                    <div class="p-2.5 flex-1 flex flex-col justify-between">
                        <h4 class="text-[11px] font-semibold text-gray-200 line-clamp-2">{{ $v->title }}</h4>
                        <div class="mt-2 flex items-center justify-between text-[10px] text-gray-500 font-mono">
                            <span>{{ $v->youtube_id }}</span>
                            <button type="button" 
                                    onclick="navigator.clipboard.writeText('{{ $v->thumbnail_url }}'); alert('Thumbnail URL copied to clipboard!')" 
                                    class="text-amber-400 hover:text-white">
                                Copy URL
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-6 text-center text-gray-500 italic">
                    No videos registered in pool yet.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Include Cloudinary Upload Widget component -->
@include('components.cloudinary-upload-widget')

<script>
let lastUploadedUrl = '';

function handleUploadComplete(info) {
    lastUploadedUrl = info.secure_url;
    const box = document.getElementById('latest-upload-box');
    const preview = document.getElementById('latest-upload-preview');
    const urlText = document.getElementById('latest-upload-url');

    if (box && preview && urlText) {
        preview.src = lastUploadedUrl;
        urlText.textContent = lastUploadedUrl;
        box.classList.remove('hidden');
    }
}

function copyLatestUrl() {
    if (lastUploadedUrl) {
        navigator.clipboard.writeText(lastUploadedUrl);
        alert('Cloudinary URL copied to clipboard!');
    }
}
</script>

@endsection
