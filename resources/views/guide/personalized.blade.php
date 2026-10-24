@extends('layouts.app')

@section('title', 'Personalised Viewing Guide - Zonita TV')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-10 pb-6 border-b border-gray-800">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-600/10 text-red-400 text-xs font-semibold uppercase tracking-wider mb-2">
            Audience Personalisation
        </span>
        <h1 class="text-3xl font-extrabold text-white mb-2">My Personalised Guide</h1>
        <p class="text-sm text-gray-400">Customised recommendations tuned using your viewing preferences and favourites.</p>
    </div>

    <!-- Recommendations Grid -->
    <div class="space-y-12">
        <section>
            <h2 class="text-xl font-bold text-white mb-4">Recommended Channels for You</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($recommendedChannels as $channel)
                    <x-channel-card :channel="$channel" />
                @endforeach
            </div>
        </section>

        @if($favourites->isNotEmpty())
        <section>
            <h2 class="text-xl font-bold text-white mb-4">Saved Favourites</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($favourites as $channel)
                    <x-channel-card :channel="$channel" />
                @endforeach
            </div>
        </section>
        @endif
    </div>

</div>

@endsection
