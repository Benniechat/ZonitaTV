@extends('layouts.app')

@section('title', $category->name . ' - Zoniva TV')
@section('meta_description', $category->meta_title ?? $category->description)

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Category Header -->
    <div class="mb-10 pb-6 border-b border-gray-800">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-600/10 text-red-400 text-xs font-semibold uppercase tracking-wider mb-3">
            Category Collection
        </div>
        <h1 class="text-3xl font-extrabold text-white mb-2">{{ $category->name }}</h1>
        <p class="text-sm text-gray-400 max-w-2xl">{{ $category->description }}</p>
    </div>

    <!-- Category Channels Grid -->
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
            <h3 class="text-lg font-bold text-white mb-2">No active channels in this category</h3>
            <p class="text-xs text-gray-400 mb-4">Our content management team is reviewing channels for this category.</p>
            <a href="{{ route('channels.index') }}" class="px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg">Browse Directory</a>
        </div>
    @endif

</div>

@endsection
