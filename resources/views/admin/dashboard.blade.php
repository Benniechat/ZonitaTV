@extends('admin.layout')

@section('title', 'Zoniva TV - Administration Overview')

@section('admin_content')

<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Operations & Compliance Dashboard</h1>
            <p class="text-xs text-gray-400 mt-1">Real-time status of channel catalogue, rights review status, and platform health.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.channels.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition">
                + New Channel
            </a>
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Published Channels</span>
            <div class="text-2xl font-extrabold text-white mt-1">{{ $metrics['published_channels'] }} / {{ $metrics['total_channels'] }}</div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-2 inline-block">Directory Active</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Awaiting Rights Review</span>
            <div class="text-2xl font-extrabold text-amber-400 mt-1">{{ $metrics['pending_rights_review'] }}</div>
            <a href="{{ route('admin.rights.index') }}" class="text-[10px] text-gray-400 hover:text-white underline mt-2 inline-block">Review Permissions &rarr;</a>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Expired Rights / Restricted</span>
            <div class="text-2xl font-extrabold text-rose-500 mt-1">{{ $metrics['expired_rights'] }}</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">De-listed automatically</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Newsletter Subscribers</span>
            <div class="text-2xl font-extrabold text-white mt-1">{{ $metrics['subscribers_count'] }}</div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-2 inline-block">Viewing Guide Opt-in</span>
        </div>

    </div>

    <!-- Secondary Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Recent Channels -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Recently Added Channels</h3>
                <a href="{{ route('admin.channels.index') }}" class="text-xs text-red-400 hover:text-red-300">View All</a>
            </div>

            <div class="divide-y divide-gray-800">
                @foreach($recentChannels as $ch)
                    <div class="py-3 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <img src="{{ $ch->logo_url }}" alt="{{ $ch->name }}" class="w-8 h-8 object-contain rounded bg-gray-900 p-1" onerror="this.onerror=null; this.src='{{ asset(config('channels.default_logo', 'images/logo/zonitatvlogo2.png')) }}';">
                            <div>
                                <h4 class="text-xs font-bold text-white">{{ $ch->name }}</h4>
                                <span class="text-[10px] text-gray-400">{{ $ch->playback_mode }} &bull; {{ $ch->country_code }}</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $ch->is_published ? 'bg-emerald-500/10 text-emerald-400' : 'bg-gray-800 text-gray-400' }}">
                                {{ $ch->is_published ? 'Published' : 'Draft' }}
                            </span>
                            <a href="{{ route('admin.channels.edit', $ch->id) }}" class="text-xs text-gray-400 hover:text-white">Edit</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Problem Reports -->
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Stream & User Problem Reports</h3>
                <span class="text-xs px-2 py-0.5 rounded bg-red-600/20 text-red-400 font-semibold">{{ $metrics['open_reports'] }} New</span>
            </div>

            @if($recentReports->isNotEmpty())
                <div class="divide-y divide-gray-800">
                    @foreach($recentReports as $rep)
                        <div class="py-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-white">{{ $rep->channel->name ?? 'Unknown Channel' }}</span>
                                <span class="text-[10px] text-amber-400 uppercase font-semibold">{{ $rep->issue_type }}</span>
                            </div>
                            <p class="text-xs text-gray-400">{{ $rep->description }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-500 py-6 text-center">No open problem reports logged.</p>
            @endif
        </div>

    </div>

</div>

@endsection
