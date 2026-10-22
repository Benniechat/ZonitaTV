@extends('layouts.app')

@section('title', 'Channel Directory - Zoniva TV')
@section('meta_description', 'Browse the complete directory of verified Nigerian and international television channels.')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header & Filter Bar -->
    <div class="mb-8 pb-8 border-b border-gray-800">
        <h1 class="text-3xl font-extrabold text-white mb-2">Television Channel Directory</h1>
        <p class="text-sm text-gray-400 mb-6">Browse verified public and commercial channels with authorised viewing destinations.</p>

        <!-- Search & Filter Controls -->
        <form action="{{ route('channels.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-grow">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by channel name, topic, or broadcaster..." 
                       class="w-full bg-[#12151c] text-sm text-white placeholder-gray-500 pl-10 pr-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                <svg class="w-5 h-5 text-gray-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex gap-2">
                <select name="category" class="bg-[#12151c] text-sm text-gray-300 px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select name="country" class="bg-[#12151c] text-sm text-gray-300 px-4 py-3 rounded-xl border border-gray-700 focus:outline-none focus:border-red-500">
                    <option value="">All Countries</option>
                    <option value="NGA" {{ request('country') === 'NGA' ? 'selected' : '' }}>Nigeria (NGA)</option>
                    <option value="DEU" {{ request('country') === 'DEU' ? 'selected' : '' }}>International</option>
                </select>

                <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl transition">
                    Filter
                </button>
            </div>
        </form>

        <!-- Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pt-4 text-xs">
            <a href="{{ route('channels.index') }}" class="px-3 py-1.5 rounded-lg border {{ !request('category') ? 'bg-red-600 text-white border-red-500' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                All Channels
            </a>
            @foreach($categories as $c)
                <a href="{{ route('channels.index', ['category' => $c->slug]) }}" class="px-3 py-1.5 rounded-lg border whitespace-nowrap {{ request('category') === $c->slug ? 'bg-red-600 text-white border-red-500' : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white' }}">
                    {{ $c->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Channels Grid -->
    @if($channels->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($channels as $channel)
                <x-channel-card :channel="$channel" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $channels->links() }}
        </div>
    @else
        <div class="text-center py-20 bg-[#12151c] rounded-2xl border border-gray-800">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-lg font-bold text-white mb-1">No channels found</h3>
            <p class="text-xs text-gray-400 max-w-sm mx-auto mb-4">Try clearing some of your search filters or browse by category.</p>
            <a href="{{ route('channels.index') }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold rounded-lg transition">
                Reset Filters
            </a>
        </div>
    @endif

</div>

@endsection
