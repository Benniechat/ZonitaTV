@extends('layouts.app')

@section('title', 'Search Results for "' . $query . '" - Zoniva TV')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white mb-2">Search Results</h1>
        <p class="text-sm text-gray-400">
            Showing results matching <span class="text-white font-semibold">"{{ $query }}"</span>
        </p>
    </div>

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
            <h3 class="text-lg font-bold text-white mb-2">No matching channels found</h3>
            <p class="text-xs text-gray-400 mb-6">Try searching for generic terms like "Sports", "News", or "Channels".</p>
            <a href="{{ route('channels.index') }}" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition">
                Browse Full Catalogue
            </a>
        </div>
    @endif

</div>

@endsection
