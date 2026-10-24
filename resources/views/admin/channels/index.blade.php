@extends('admin.layout')

@section('title', 'Channel Catalogue - Zoniva TV Administration')

@section('admin_content')

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Channel Catalogue</h1>
            <p class="text-xs text-gray-400">Manage broadcaster records, viewing modes, and publication state.</p>
        </div>
        <a href="{{ route('admin.channels.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition">
            + Add New Channel
        </a>
    </div>

    <div class="bg-[#12151c] border border-gray-800 rounded-2xl overflow-hidden shadow">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-900/60 text-gray-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                <tr>
                    <th class="py-3.5 px-4">Channel</th>
                    <th class="py-3.5 px-4">Categories</th>
                    <th class="py-3.5 px-4">Playback Mode</th>
                    <th class="py-3.5 px-4">Rights Status</th>
                    <th class="py-3.5 px-4">State</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($channels as $channel)
                    <tr class="hover:bg-gray-900/40 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="w-8 h-8 object-contain rounded bg-gray-900 p-1">
                                <div>
                                    <div class="font-bold text-white">{{ $channel->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $channel->broadcaster_name }} &bull; {{ $channel->country_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @foreach($channel->categories as $c)
                                <span class="inline-block px-2 py-0.5 rounded bg-gray-800 text-[10px] text-gray-300 mr-1 mb-1">{{ $c->name }}</span>
                            @endforeach
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] text-gray-300">{{ $channel->playback_mode }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $status = $channel->latestRights?->status ?? 'pending_review';
                                $badgeColor = match($status) {
                                    'embedding_approved', 'redistribution_approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                    'directory_approved' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                    'expired', 'rejected' => 'bg-red-500/10 text-red-400 border-red-500/30',
                                    default => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                };
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border {{ $badgeColor }}">
                                {{ str_replace('_', ' ', $status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($channel->is_published)
                                <span class="text-emerald-400 font-semibold">● Published</span>
                            @else
                                <span class="text-gray-500">○ Hidden</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="{{ route('channels.show', $channel->slug) }}" target="_blank" class="text-gray-400 hover:text-white">View</a>
                            <a href="{{ route('admin.channels.edit', $channel->id) }}" class="text-red-400 hover:text-red-300 font-semibold">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>
        {{ $channels->links() }}
    </div>

</div>

@endsection
