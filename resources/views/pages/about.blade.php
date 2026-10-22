@extends('layouts.app')

@section('title', 'About Zoniva TV - Product, Mission and Ecosystem')

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="mb-10 pb-6 border-b border-gray-800">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">About Zoniva TV</h1>
        <p class="text-base text-gray-400">Discover More. Watch Smarter.</p>
    </div>

    <div class="space-y-8 text-sm text-gray-300 leading-relaxed">
        <section>
            <h2 class="text-xl font-bold text-white mb-2">1. Who We Are</h2>
            <p>
                Zoniva TV is an online television discovery, streaming and media platform designed to help audiences explore television channels, programmes, live broadcasts, sports analysis, news, Christian content, educational resources, and original productions through one unified, legally compliant digital experience.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-white mb-2">2. Our Core Value Proposition</h2>
            <p>
                Zoniva TV organises television discovery around what people want to watch, where they can watch it through authorised sources, and what they may enjoy discovering next. We bridge the gap between audiences seeking reliable viewing information and broadcasters seeking legitimate audience engagement.
            </p>
        </section>

        <section id="business" class="p-6 rounded-2xl bg-[#12151c] border border-gray-800">
            <h2 class="text-xl font-bold text-white mb-2">3. Zoniva Business: Branded Television Portals</h2>
            <p class="mb-4">
                Beyond consumer television discovery, Zoniva TV provides organisations, churches, schools, training bodies, and media brands with white-label, branded television directories and curated video portals.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-gray-400">
                <div class="p-4 rounded-xl bg-gray-900 border border-gray-800">
                    <strong class="text-white block mb-1">Churches & Ministries</strong>
                    Branded sermon portals, livestreaming directories, and archive libraries.
                </div>
                <div class="p-4 rounded-xl bg-gray-900 border border-gray-800">
                    <strong class="text-white block mb-1">Schools & Training</strong>
                    Curated video classrooms, internal educational streams, and recorded webinars.
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold text-white mb-2">4. Content Rights & Compliance Commitment</h2>
            <p>
                A publicly accessible link does not grant unvetted permission to redistribute or monetise third-party content. Zoniva TV verifies permissions for every listed broadcaster, distinguishing between directory indexing, embedded player support, and external destination redirects.
            </p>
        </section>
    </div>

</div>

@endsection
