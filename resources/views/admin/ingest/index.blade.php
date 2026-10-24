@extends('admin.layout')

@section('title', 'IPTV-ORG Ingest & EPG Synchronization - Zonita TV')

@section('admin_content')

<div class="space-y-8" x-data="{ playlistUrl: 'https://iptv-org.github.io/iptv/countries/ng.m3u', epgType: 'generate_schedules' }">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">IPTV Playlist Ingest & EPG Synchronization</h1>
            <p class="text-xs text-gray-400 mt-1">
                Automated channel discovery via <a href="https://github.com/iptv-org/iptv" target="_blank" class="text-red-400 underline">iptv-org</a> and Electronic Programme Guide (EPG) mapping via <a href="https://github.com/iptv-org/epg" target="_blank" class="text-red-400 underline">iptv-org/epg</a>.
            </p>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Total Ingested Channels</span>
            <div class="text-2xl font-extrabold text-white mt-1">{{ $metrics['total_channels'] }}</div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-2 inline-block">Active in Catalogue</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Channels with EPG Mapping</span>
            <div class="text-2xl font-extrabold text-blue-400 mt-1">{{ $metrics['channels_with_tvg_id'] }}</div>
            <span class="text-[10px] text-gray-400 mt-2 inline-block">Mapped via <code>tvg-id</code></span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">EPG Programme Entries</span>
            <div class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['total_programmes'] }}</div>
            <span class="text-[10px] text-gray-400 mt-2 inline-block">Indexed in Schedule Guide</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Live Now Programmes</span>
            <div class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $metrics['live_now_count'] }}</div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-2 inline-block">Active EPG Slots</span>
        </div>
    </div>

    <!-- Operations Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Module 1: M3U Playlist Ingest Engine -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6 sm:p-7 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">IPTV-ORG Playlist Ingestion</h3>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed mb-6">
                    Fetches and parses live M3U playlists, automatically extracting channel titles, stream feeds, logos, and EPG mapping IDs.
                </p>

                <!-- Presets -->
                <div class="mb-4">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase mb-2">Quick Presets</label>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <button type="button" @click="playlistUrl = 'https://iptv-org.github.io/iptv/countries/ng.m3u'" 
                                class="px-2.5 py-1 rounded-lg bg-gray-900 hover:bg-gray-800 border border-gray-700 text-gray-300 transition">
                            🇳🇬 Nigeria Feed
                        </button>
                        <button type="button" @click="playlistUrl = 'https://iptv-org.github.io/iptv/categories/sports.m3u'" 
                                class="px-2.5 py-1 rounded-lg bg-gray-900 hover:bg-gray-800 border border-gray-700 text-gray-300 transition">
                            ⚽ Sports Feeds
                        </button>
                        <button type="button" @click="playlistUrl = 'https://iptv-org.github.io/iptv/categories/news.m3u'" 
                                class="px-2.5 py-1 rounded-lg bg-gray-900 hover:bg-gray-800 border border-gray-700 text-gray-300 transition">
                            📰 News Feeds
                        </button>
                        <button type="button" @click="playlistUrl = 'https://iptv-org.github.io/iptv/categories/religious.m3u'" 
                                class="px-2.5 py-1 rounded-lg bg-gray-900 hover:bg-gray-800 border border-gray-700 text-gray-300 transition">
                            ✝ Religious / Christian
                        </button>
                        <button type="button" @click="playlistUrl = 'https://iptv-org.github.io/iptv/index.m3u'" 
                                class="px-2.5 py-1 rounded-lg bg-gray-900 hover:bg-gray-800 border border-gray-700 text-gray-300 transition">
                            🌐 Master Worldwide
                        </button>
                    </div>
                </div>

                <form action="{{ route('admin.ingest.playlist') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">M3U Playlist Source URL *</label>
                        <input type="url" name="url" x-model="playlistUrl" required 
                               class="w-full bg-gray-900 text-xs text-white px-3.5 py-2.5 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500 font-mono">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-300 mb-1">Country Filter (Optional)</label>
                            <input type="text" name="country" placeholder="e.g. NG, US, UK" maxlength="3" 
                                   class="w-full bg-gray-900 text-xs text-white px-3.5 py-2.5 rounded-xl border border-gray-700 uppercase">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-300 mb-1">Channel Import Limit</label>
                            <input type="number" name="limit" value="50" min="5" max="250" required 
                                   class="w-full bg-gray-900 text-xs text-white px-3.5 py-2.5 rounded-xl border border-gray-700">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition">
                            Ingest M3U Playlist &rarr;
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-800 text-[11px] text-gray-500 leading-relaxed">
                <strong class="text-gray-400">Compliance & Rights Rule:</strong> All imported channels are registered with <code>directory_approved</code> status and mapped to direct streams or official external destinations.
            </div>
        </div>

        <!-- Module 2: EPG Schedule Engine -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6 sm:p-7 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Electronic Program Guide (EPG)</h3>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed mb-6">
                    Synchronize XMLTV electronic program guides published via <a href="https://github.com/iptv-org/epg" target="_blank" class="text-emerald-400 underline">iptv-org/epg</a> or generate continuous 48-hour schedule timelines for every active channel.
                </p>

                <form action="{{ route('admin.ingest.epg') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase mb-2">Synchronization Strategy</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <label class="flex items-center p-3 rounded-xl bg-gray-900 border border-gray-700 cursor-pointer space-x-2">
                                <input type="radio" name="action_type" value="generate_schedules" x-model="epgType" checked class="text-emerald-600 focus:ring-0">
                                <div>
                                    <strong class="block text-white text-xs">Dynamic EPG</strong>
                                    <span class="text-[10px] text-gray-400">Generate 48h active timelines</span>
                                </div>
                            </label>

                            <label class="flex items-center p-3 rounded-xl bg-gray-900 border border-gray-700 cursor-pointer space-x-2">
                                <input type="radio" name="action_type" value="remote_xmltv" x-model="epgType" class="text-emerald-600 focus:ring-0">
                                <div>
                                    <strong class="block text-white text-xs">XMLTV Source Feed</strong>
                                    <span class="text-[10px] text-gray-400">Sync remote XML guide</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div x-show="epgType === 'remote_xmltv'" x-cloak>
                        <label class="block text-xs font-bold text-gray-300 mb-1">Remote XMLTV URL</label>
                        <input type="url" name="xmltv_url" placeholder="https://example.com/guide.xml" 
                               class="w-full bg-gray-900 text-xs text-white px-3.5 py-2.5 rounded-xl border border-gray-700 font-mono">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                            Run EPG Synchronization &rarr;
                        </button>
                    </div>
                </form>
            </div>

            <!-- iptv-org/epg Help Box -->
            <div class="mt-6 pt-4 border-t border-gray-800 text-[11px] text-gray-400 space-y-1.5">
                <strong class="text-white block">EPG Technical Reference:</strong>
                <p>
                    Guides can also be grabbed locally using <code>npm run grab --- --sites=&lt;site&gt;</code> in the <a href="https://github.com/iptv-org/epg" target="_blank" class="text-emerald-400 underline">iptv-org/epg</a> repository. Resulting <code>guide.xml</code> files sync automatically against channels matching <code>tvg-id</code>.
                </p>
            </div>
        </div>

    </div>

    <!-- Active Broadcasts Preview -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Recently Synchronized EPG Broadcasts</h3>
            <a href="{{ route('guide.index') }}" target="_blank" class="text-xs text-red-400 hover:text-red-300">Open Public TV Guide &rarr;</a>
        </div>

        <div class="divide-y divide-gray-800">
            @foreach($metrics['latest_programmes'] as $p)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-white">{{ $p->title }}</span>
                            @if($p->is_live)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-600 text-white uppercase">LIVE</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5">
                            {{ $p->channel->name ?? 'Channel' }} &bull; {{ $p->start_time->format('h:i A') }} - {{ $p->end_time->format('h:i A') }} ({{ $p->duration_minutes }} min)
                        </p>
                    </div>

                    @if($p->channel)
                        <a href="{{ route('channels.show', $p->channel->slug) }}" target="_blank" class="text-xs text-gray-400 hover:text-white px-3 py-1 rounded bg-gray-900 border border-gray-800">
                            Tune In
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</div>

@endsection
