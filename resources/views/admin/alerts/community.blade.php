@extends('admin.layout')

@section('title', 'WhatsApp Community Broadcast Hub - Zoniva TV')

@section('admin_content')

<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Community Broadcaster
                </span>
                <span class="text-xs text-gray-500">WhatsApp & Viewing Guide Alerts</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white">WhatsApp Community Broadcast Hub</h1>
            <p class="text-xs text-gray-400 mt-0.5">Automated broadcast announcements, weekend viewing guides, and trending channel alerts.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.alerts.index') }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-xs font-semibold text-gray-200 transition">
                &larr; System Alerts
            </a>
            <a href="{{ route('wa.join') }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold text-white transition flex items-center gap-1.5">
                <span>Test Channel Join Link</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- Subscriber Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        
        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Opted-In Community Audience</span>
            <div class="text-3xl font-extrabold text-white mt-1">{{ number_format($subscriberCount) }}</div>
            <span class="text-[10px] text-emerald-400 font-semibold mt-2 inline-block">Active Subscribers</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Broadcast Channel Reach</span>
            <div class="text-3xl font-extrabold text-emerald-400 mt-1">100%</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">Direct WhatsApp gateway delivery</span>
        </div>

        <div class="p-5 rounded-2xl bg-[#12151c] border border-gray-800">
            <span class="text-xs text-gray-400 font-medium">Guide Integration</span>
            <div class="text-3xl font-extrabold text-indigo-400 mt-1">AI Guided</div>
            <span class="text-[10px] text-gray-500 mt-2 inline-block">Personalized /my-guide deep-linking</span>
        </div>

    </div>

    <!-- Broadcast Dispatch Composer -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        <h3 class="text-base font-bold text-white mb-2">Compose Community Guide Broadcast</h3>
        <p class="text-xs text-gray-400 mb-6">Send an announcement or viewing guide alert to WhatsApp community members.</p>

        <form action="{{ route('admin.community.broadcast') }}" method="POST" class="space-y-5">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Target Audience Segment</label>
                    <select name="target_group" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="all">All Community Subscribers ({{ $subscriberCount }})</option>
                        <option value="lagos">Lagos & Nigeria Primetime Viewers</option>
                        <option value="diaspora">Diaspora (UK, US, Canada, Europe)</option>
                        <option value="vip_viewers">Verified Registered Users</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5">Destination Link</label>
                    <input type="text" readonly value="{{ url('/my-guide') }}" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-gray-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1.5">Custom Alert Message (Optional)</label>
                <textarea name="custom_message" rows="3" placeholder="Leave blank to send standard automated weekend viewing highlights and top picks..." class="w-full bg-gray-900 border border-gray-800 rounded-xl p-4 text-xs text-white focus:outline-none focus:border-emerald-500 placeholder-gray-600"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Dispatch Community Broadcast
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Broadcast Dispatch History -->
    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6">
        <h3 class="text-base font-bold text-white mb-4">Recent Broadcast Dispatches</h3>

        @if($recentBroadcasts->isEmpty())
            <div class="py-8 text-center text-xs text-gray-500">
                No previous community broadcasts logged.
            </div>
        @else
            <div class="divide-y divide-gray-800">
                @foreach($recentBroadcasts as $b)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-white">{{ $b->message }}</span>
                            <span class="text-gray-500 text-[10px] block mt-0.5">Dispatched {{ $b->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Sent
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

@endsection
