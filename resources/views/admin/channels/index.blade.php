@extends('admin.layout')

@section('title', 'Channel Catalogue & Virtual Playout - Zoniva TV')

@section('admin_content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Channel Catalogue</h1>
            <p class="text-xs text-gray-400 mt-1">Manage broadcaster records, virtual 24/7 linear stations, station branding, and playout states.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.broadcasting.index') }}" class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 font-semibold text-xs rounded-xl transition">
                Virtual Broadcast Engine &rarr;
            </a>
            <a href="{{ route('admin.channels.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition">
                + Create Channel
            </a>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Channels</div>
            <div class="text-2xl font-black text-white mt-1">{{ $stats['total_channels'] }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Linear & Virtual Roster</div>
        </div>
        <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Virtual 24/7 Channels</div>
            <div class="text-2xl font-black text-red-500 mt-1">{{ $stats['virtual_channels'] }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Algorithmic Continuous Playout</div>
        </div>
        <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">On-Air Live</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">{{ $stats['on_air_channels'] }}</div>
            <div class="text-[10px] text-emerald-400/80 mt-1">Active & Published on Guide</div>
        </div>
        <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Partner Syndicated</div>
            <div class="text-2xl font-black text-indigo-400 mt-1">{{ $stats['partner_channels'] }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Content Partner Agreements</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
            <a href="{{ route('admin.channels.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('filter') ? 'bg-red-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                All ({{ $stats['total_channels'] }})
            </a>
            <a href="{{ route('admin.channels.index', ['filter' => 'virtual']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('filter') === 'virtual' ? 'bg-red-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                Virtual 24/7 ({{ $stats['virtual_channels'] }})
            </a>
            <a href="{{ route('admin.channels.index', ['filter' => 'published']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('filter') === 'published' ? 'bg-red-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                On-Air
            </a>
            <a href="{{ route('admin.channels.index', ['filter' => 'offline']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('filter') === 'offline' ? 'bg-red-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                Off-Air
            </a>
        </div>

        <form method="GET" action="{{ route('admin.channels.index') }}" class="w-full sm:w-72">
            @if(request('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search channels, dial #, broadcaster..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
        </form>
    </div>

    <!-- Channels Table -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl overflow-hidden shadow">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-900/60 text-gray-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                <tr>
                    <th class="py-3.5 px-4">Dial & Channel</th>
                    <th class="py-3.5 px-4">Categories</th>
                    <th class="py-3.5 px-4">Type & Mode</th>
                    <th class="py-3.5 px-4">Partner Attribution</th>
                    <th class="py-3.5 px-4">Broadcast State</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($channels as $channel)
                    <tr class="hover:bg-gray-900/40 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="relative flex-shrink-0">
                                    <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="w-9 h-9 object-contain rounded-lg bg-gray-900 p-1 border" style="border-color: {{ $channel->accent_color }};">
                                    <span class="absolute -bottom-1 -right-1 px-1 rounded text-[8px] font-mono font-bold text-white shadow" style="background-color: {{ $channel->accent_color }};">
                                        {{ $channel->display_number }}
                                    </span>
                                </div>
                                <div>
                                    <a href="{{ route('admin.channels.show', $channel->id) }}" class="font-bold text-white hover:text-red-400 transition">
                                        {{ $channel->name }}
                                    </a>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $channel->broadcaster_name ?? 'Zonita TV' }} &bull; <span class="font-mono uppercase">{{ $channel->country_code }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @foreach($channel->categories as $c)
                                <span class="inline-block px-2 py-0.5 rounded bg-gray-800 text-[10px] text-gray-300 mr-1 mb-1">{{ $c->name }}</span>
                            @endforeach
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1.5">
                                @if($channel->isVirtual())
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-950/60 text-red-400 border border-red-500/30 uppercase tracking-wider">Virtual 24/7</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-800 text-gray-300 uppercase tracking-wider">Syndicated</span>
                                @endif
                                <span class="font-mono text-[10px] text-gray-400 capitalize">{{ str_replace('_', ' ', $channel->playback_mode) }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($channel->contentPartner)
                                <a href="{{ route('admin.partners.show', $channel->contentPartner->id) }}" class="text-indigo-400 hover:underline font-semibold">
                                    {{ $channel->contentPartner->name }}
                                </a>
                            @else
                                <span class="text-gray-500 italic">Direct Zonita Roster</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <form action="{{ route('admin.channels.toggle-status', $channel->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="toggle_publish">
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold transition {{ $channel->is_published ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $channel->is_published ? 'bg-emerald-400' : 'bg-gray-500' }}"></span>
                                    {{ $channel->is_published ? 'On-Air' : 'Off-Air Slate' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="{{ route('admin.channels.show', $channel->id) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                Playout Monitor &rarr;
                            </a>
                            <a href="{{ route('admin.channels.edit', $channel->id) }}" class="text-xs font-semibold text-gray-400 hover:text-white">
                                Edit
                            </a>
                            <a href="{{ route('channel.show', $channel->slug) }}" target="_blank" class="text-xs text-gray-500 hover:text-gray-300">
                                ↗
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500 text-xs">
                            No channels found matching the selected filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $channels->links() }}
    </div>
</div>
@endsection
