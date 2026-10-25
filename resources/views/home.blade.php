@extends('layouts.app')

@section('title', 'Zonita TV - Discover More. Watch Smarter.')

@section('content')

<div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- 1. Hero Billboard Carousel with Left Fade Effect -->
    <div x-data="{
            activeSlide: 0,
            slides: [
                {
                    title: 'Great Stories.<br><span class=\'text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-orange-400 to-amber-500\'>Endless Fun.</span>',
                    subtitle: 'From blockbuster movies to trending series, music, culture and more — find the entertainment you love, all in one place.',
                    badge: 'ENTERTAINMENT',
                    badgeColor: 'bg-purple-500/20 text-purple-300 border-purple-500/40',
                    buttonText: 'Explore Entertainment',
                    buttonUrl: '{{ route('categories.show', 'entertainment') }}',
                    secondaryText: 'Watch Live TV',
                    secondaryUrl: '{{ route('guide.index') }}',
                    bgImage: '{{ asset('images/hero/hero_slide_1.jpg') }}',
                    tag: 'Movies &bull; Series &bull; Shows'
                },
                {
                    title: 'Your World Of<br><span class=\'text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-500 to-red-500\'>Entertainment Awaits.</span>',
                    subtitle: 'From blockbuster movies to binge-worthy series, music, culture and more — all in one place.',
                    badge: 'PREMIUM PICKS',
                    badgeColor: 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                    buttonText: 'Start Watching Now',
                    buttonUrl: '{{ route('channels.index') }}',
                    secondaryText: 'Browse Catalogue',
                    secondaryUrl: '{{ route('channels.index') }}',
                    bgImage: '{{ asset('images/hero/hero_slide_2.jpg') }}',
                    tag: 'Live Channels &bull; On Demand'
                },
                {
                    title: 'Bigger Stories.<br><span class=\'text-transparent bg-clip-text bg-gradient-to-r from-pink-400 via-purple-400 to-indigo-400\'>Brighter Moments.</span>',
                    subtitle: 'From blockbuster movies to binge-worthy series, music, culture and more — all in one place.',
                    badge: 'FEATURED DISCOVERY',
                    badgeColor: 'bg-pink-500/20 text-pink-300 border-pink-500/40',
                    buttonText: 'Explore Entertainment',
                    buttonUrl: '{{ route('categories.show', 'entertainment') }}',
                    secondaryText: 'Channel Guide',
                    secondaryUrl: '{{ route('guide.index') }}',
                    bgImage: '{{ asset('images/hero/hero_slide_3.jpg') }}',
                    tag: 'All-In-One Streaming Experience'
                }
            ],
            autoplayInterval: null,
            init() {
                this.startAutoplay();
            },
            startAutoplay() {
                this.autoplayInterval = setInterval(() => {
                    this.next();
                }, 6500);
            },
            stopAutoplay() {
                if (this.autoplayInterval) {
                    clearInterval(this.autoplayInterval);
                }
            },
            next() {
                this.activeSlide = (this.activeSlide + 1) % this.slides.length;
            },
            prev() {
                this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length;
            },
            goTo(index) {
                this.activeSlide = index;
            }
         }"
         @mouseenter="stopAutoplay()"
         @mouseleave="startAutoplay()"
         class="relative w-full rounded-3xl overflow-hidden bg-[#07090e] border border-gray-800 shadow-2xl min-h-[460px] lg:min-h-[520px] flex items-center group/carousel">

        <!-- Slides Background with Smooth Crossfade -->
        <template x-for="(slide, idx) in slides" :key="idx">
            <div x-show="activeSlide === idx"
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0 scale-105"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-700"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute inset-0 bg-cover bg-center bg-no-repeat"
                 :style="`background-image: url('${slide.bgImage}');`">
            </div>
        </template>

        <!-- Left Side Fading Gradients & Overlays (Exact Deep Atmospheric Fade) -->
        <!-- Primary Left-to-Right Heavy Obsidian Vignette -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#06080d] via-[#080b12]/95 via-45% to-transparent pointer-events-none z-10"></div>
        <!-- Soft Radial Glow on Bottom & Left for High Contrast Readability -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#06080d] via-transparent to-black/30 pointer-events-none z-10"></div>
        <div class="absolute -left-20 top-0 bottom-0 w-96 bg-purple-950/20 blur-3xl pointer-events-none z-10"></div>

        <!-- Carousel Slide Content (Left Side) -->
        <div class="relative z-20 px-8 sm:px-12 lg:px-16 py-12 max-w-2xl">
            <!-- Dynamic Category / Pill Tag -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full border text-[11px] font-bold tracking-widest uppercase mb-6 shadow-sm backdrop-blur-md transition-all duration-500"
                 :class="slides[activeSlide].badgeColor">
                <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                <span x-text="slides[activeSlide].badge"></span>
            </div>

            <!-- Main Heading with Highlight Gradient -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.08] mb-4 drop-shadow-md min-h-[120px] flex items-center"
                x-html="slides[activeSlide].title">
            </h1>

            <!-- Descriptive Tagline -->
            <p class="text-sm sm:text-base text-gray-300 font-normal leading-relaxed mb-8 max-w-lg min-h-[48px]"
               x-text="slides[activeSlide].subtitle">
            </p>

            <!-- Call to Actions -->
            <div class="flex flex-wrap items-center gap-4">
                <a :href="slides[activeSlide].buttonUrl" 
                   class="inline-flex items-center gap-2 px-7 py-3.5 rounded-full bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-black font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-orange-500/30 transition-all duration-300 transform hover:-translate-y-0.5 hover:scale-105">
                    <span x-text="slides[activeSlide].buttonText"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a :href="slides[activeSlide].secondaryUrl" 
                   class="inline-flex items-center px-6 py-3.5 rounded-full bg-gray-900/80 hover:bg-gray-800 text-gray-200 hover:text-white font-semibold text-xs tracking-wider border border-gray-700/80 backdrop-blur-md transition-all shadow-md">
                    <span x-text="slides[activeSlide].secondaryText"></span>
                </a>
            </div>

            <!-- Indicators / Dot Pagination -->
            <div class="flex items-center space-x-2.5 mt-10">
                <template x-for="(slide, index) in slides" :key="index">
                    <button @click="goTo(index)" 
                            type="button"
                            class="transition-all duration-300 rounded-full h-1.5 focus:outline-none"
                            :class="activeSlide === index ? 'w-8 bg-amber-400' : 'w-2 bg-gray-600 hover:bg-gray-400'"
                            :aria-label="'Go to slide ' + (index + 1)">
                    </button>
                </template>
            </div>
        </div>

        <!-- Left / Right Nav Arrows (Subtle, Visible on Group Hover) -->
        <button @click="prev()" 
                type="button"
                aria-label="Previous Slide"
                class="absolute left-4 z-30 w-11 h-11 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center border border-gray-700/60 backdrop-blur-md opacity-0 group-hover/carousel:opacity-100 transition-all duration-300 hover:scale-110 shadow-xl focus:opacity-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <button @click="next()" 
                type="button"
                aria-label="Next Slide"
                class="absolute right-4 z-30 w-11 h-11 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center border border-gray-700/60 backdrop-blur-md opacity-0 group-hover/carousel:opacity-100 transition-all duration-300 hover:scale-110 shadow-xl focus:opacity-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>

    <!-- 2. Main 2-Column Content Grid: Left Stream (75%) + Right Sidebar (25%) -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        
        <!-- Left Column: Content Showcase (3 Columns) -->
        <div class="xl:col-span-3 space-y-10">
            
            <!-- SECTION 1: Live & Trending Now -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <a href="{{ route('guide.index') }}" class="flex items-center gap-2 group">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                        <h2 class="text-lg font-bold text-white group-hover:text-red-400 transition">Live &amp; Trending Now</h2>
                        <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('guide.index') }}" class="text-xs text-gray-400 hover:text-white transition">View All</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($liveTrending as $item)
                        @php
                            $badgeColor = match($item->category?->slug) {
                                'sports' => 'bg-purple-900/60 text-purple-300 border-purple-500/30',
                                'news' => 'bg-blue-900/60 text-blue-300 border-blue-500/30',
                                'christian-tv' => 'bg-amber-900/60 text-amber-300 border-amber-500/30',
                                'education' => 'bg-emerald-900/60 text-emerald-300 border-emerald-500/30',
                                default => 'bg-pink-900/60 text-pink-300 border-pink-500/30',
                            };
                        @endphp
                        <a href="{{ route('channels.show', $item->channel->slug ?? 'sports-central') }}" 
                           class="group relative rounded-2xl overflow-hidden bg-[#121620] border border-gray-800 hover:border-gray-700 transition flex flex-col shadow-lg">
                            <!-- Thumbnail Area with Center Play Icon -->
                            <div class="relative aspect-video w-full overflow-hidden bg-gray-900">
                                <img src="{{ $item->poster_image ?: 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=400&q=80' }}" 
                                     alt="{{ $item->title }}" 
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

                                <!-- LIVE badge -->
                                <div class="absolute top-2.5 left-2.5">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-red-600 text-white uppercase tracking-wider">LIVE</span>
                                </div>

                                <!-- Center Play Icon on Hover -->
                                <div class="absolute inset-0 flex items-center justify-center opacity-70 group-hover:opacity-100 transition">
                                    <div class="w-8 h-8 rounded-full bg-black/60 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 fill-white ml-0.5" viewBox="0 0 20 20"><path d="M4 4l12 6-12 6z"/></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Meta -->
                            <div class="p-3">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold border {{ $badgeColor }} uppercase tracking-wider mb-1">
                                    {{ $item->category?->name ?? 'General' }}
                                </span>
                                <h3 class="text-xs font-bold text-white group-hover:text-amber-400 transition truncate mb-1">
                                    {{ $item->title }}
                                </h3>
                                <div class="flex items-center text-[10px] text-gray-400 gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span>{{ $item->time_left_str ?? 'Live' }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- SECTION 2: Popular Channels -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <a href="{{ route('channels.index') }}" class="flex items-center gap-2 group">
                        <h2 class="text-lg font-bold text-white group-hover:text-amber-400 transition">Popular Channels</h2>
                        <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('channels.index') }}" class="text-xs text-gray-400 hover:text-white transition">View All</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($popularChannels as $channel)
                        <a href="{{ route('channels.show', $channel->slug) }}" 
                           class="group relative rounded-2xl overflow-hidden bg-[#121620] border border-gray-800 hover:border-gray-700 transition flex flex-col shadow-lg">
                            <div class="relative aspect-video w-full overflow-hidden bg-gray-900 flex items-center justify-center">
                                <img src="{{ $channel->banner_url }}" 
                                     alt="{{ $channel->name }}" 
                                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 filter brightness-[0.75] group-hover:brightness-[0.85]"
                                     loading="lazy"
                                     onerror="this.onerror=null; this.src='{{ $channel->getCategoryBannerUrl() }}';">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-black/20"></div>

                                <!-- Floating frosted glass channel badge -->
                                <div class="relative z-10 p-1.5 rounded-xl bg-black/45 backdrop-blur-md border border-white/10 shadow-lg flex items-center justify-center">
                                    <img src="{{ $channel->logo_url }}" 
                                         alt="{{ $channel->name }}" 
                                         class="h-7 w-auto max-w-[70px] object-contain drop-shadow"
                                         loading="lazy"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-[10px] font-black text-white px-1.5 py-0.5 rounded bg-red-600/80\'>{{ $channel->initials }}</span>';">
                                </div>
                            </div>

                            <div class="p-3 flex items-end justify-between bg-[#121620]">
                                <div class="min-w-0 pr-1">
                                    <h3 class="text-xs font-bold text-white group-hover:text-amber-400 transition truncate">
                                        {{ $channel->name }}
                                    </h3>
                                    <span class="text-[10px] text-gray-400 block truncate">{{ $channel->categories->first()?->name ?? 'General' }}</span>
                                </div>

                                <div class="flex items-center gap-1 text-[10px] text-emerald-400 font-semibold shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Online</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- SECTION 3: 2-Column Split (Football & Sports | News Around the World) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Football & Sports Column -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('categories.show', 'sports') }}" class="flex items-center gap-2 group">
                            <span class="text-amber-500 font-bold text-xs">&#9658;</span>
                            <h2 class="text-base font-bold text-white group-hover:text-amber-400 transition">Football &amp; Sports</h2>
                            <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('categories.show', 'sports') }}" class="text-xs text-gray-400 hover:text-white transition">View All</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($footballSports as $item)
                            <a href="{{ route('channels.show', $item['slug']) }}" class="group rounded-xl overflow-hidden bg-[#121620] border border-gray-800 hover:border-gray-700 transition flex flex-col">
                                <div class="relative aspect-video w-full overflow-hidden bg-gray-900">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=400&q=80';">
                                    <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-purple-900/70 text-[8px] font-bold text-purple-300 uppercase">Sports</span>
                                </div>
                                <div class="p-2.5">
                                    <h4 class="text-[11px] font-bold text-white group-hover:text-amber-400 transition truncate">{{ $item['title'] }}</h4>
                                    <span class="text-[9px] text-gray-500 mt-0.5 block">{{ $item['time_str'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>

                <!-- News Around the World Column -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('categories.show', 'news') }}" class="flex items-center gap-2 group">
                            <span class="text-amber-500 font-bold text-xs">&#9658;</span>
                            <h2 class="text-base font-bold text-white group-hover:text-amber-400 transition">News Around the World</h2>
                            <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('categories.show', 'news') }}" class="text-xs text-gray-400 hover:text-white transition">View All</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($newsWorld as $item)
                            <a href="{{ route('channels.show', $item['slug']) }}" class="group rounded-xl overflow-hidden bg-[#121620] border border-gray-800 hover:border-gray-700 transition flex flex-col">
                                <div class="relative aspect-video w-full overflow-hidden bg-gray-900">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=400&q=80';">
                                    <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-blue-900/70 text-[8px] font-bold text-blue-300 uppercase">News</span>
                                </div>
                                <div class="p-2.5">
                                    <h4 class="text-[11px] font-bold text-white group-hover:text-amber-400 transition truncate">{{ $item['title'] }}</h4>
                                    <span class="text-[9px] text-gray-500 mt-0.5 block">{{ $item['time_str'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>

            </div>

            <!-- SECTION 4: 2-Column Split (Faith & Inspiration | Learning & Discovery) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Faith & Inspiration Column -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('categories.show', 'christian-tv') }}" class="flex items-center gap-2 group">
                            <span class="text-amber-500 font-bold text-xs">&#9658;</span>
                            <h2 class="text-base font-bold text-white group-hover:text-amber-400 transition">Faith &amp; Inspiration</h2>
                            <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('categories.show', 'christian-tv') }}" class="text-xs text-gray-400 hover:text-white transition">View All</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($faithInspiration as $item)
                            <a href="{{ route('channels.show', $item['slug']) }}" class="group rounded-xl overflow-hidden bg-[#121620] border border-gray-800 hover:border-gray-700 transition flex flex-col">
                                <div class="relative aspect-video w-full overflow-hidden bg-gray-900">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&w=400&q=80';">
                                    <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-amber-900/70 text-[8px] font-bold text-amber-300 uppercase">Christian</span>
                                </div>
                                <div class="p-2.5">
                                    <h4 class="text-[11px] font-bold text-white group-hover:text-amber-400 transition truncate">{{ $item['title'] }}</h4>
                                    <span class="text-[9px] text-gray-500 mt-0.5 block">{{ $item['time_str'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>

                <!-- Learning & Discovery Column -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('categories.show', 'education') }}" class="flex items-center gap-2 group">
                            <span class="text-amber-500 font-bold text-xs">&#9658;</span>
                            <h2 class="text-base font-bold text-white group-hover:text-amber-400 transition">Learning &amp; Discovery</h2>
                            <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('categories.show', 'education') }}" class="text-xs text-gray-400 hover:text-white transition">View All</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($learningDiscovery as $item)
                            <a href="{{ route('channels.show', $item['slug']) }}" class="group rounded-xl overflow-hidden bg-[#121620] border border-gray-800 hover:border-gray-700 transition flex flex-col">
                                <div class="relative aspect-video w-full overflow-hidden bg-gray-900">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=400&q=80';">
                                    <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-emerald-900/70 text-[8px] font-bold text-emerald-300 uppercase">Education</span>
                                </div>
                                <div class="p-2.5">
                                    <h4 class="text-[11px] font-bold text-white group-hover:text-amber-400 transition truncate">{{ $item['title'] }}</h4>
                                    <span class="text-[9px] text-gray-500 mt-0.5 block">{{ $item['time_str'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>

            </div>

            <!-- SECTION 5: 2-Column Split (Gospel Drama 24/7 | Kids, Cartoons & Folktales 24/7) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Gospel Drama 24/7 Column (Damilola Mike-Bamiloye, Prem TV) -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('categories.show', 'christian-tv') }}" class="flex items-center gap-2 group">
                            <span class="text-amber-500 font-bold text-xs">&#9658;</span>
                            <h2 class="text-base font-bold text-white group-hover:text-amber-400 transition">Gospel Drama TV &bull; 24/7 Realtime</h2>
                            <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('categories.show', 'christian-tv') }}" class="text-xs text-gray-400 hover:text-white transition">View All</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($gospelDrama as $item)
                            <a href="{{ route('channels.show', $item['slug']) }}" class="group rounded-xl overflow-hidden bg-[#121620] border border-gray-800 hover:border-gray-700 transition flex flex-col shadow">
                                <div class="relative aspect-video w-full overflow-hidden bg-gray-900">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&w=400&q=80';">
                                    <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-amber-900/80 text-[8px] font-bold text-amber-300 uppercase tracking-wider">Faith Drama</span>
                                    <span class="absolute bottom-1.5 right-1.5 px-1.5 py-0.5 rounded bg-red-600/90 text-[8px] font-black text-white uppercase flex items-center gap-1">
                                        <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span>
                                        LIVE
                                    </span>
                                </div>
                                <div class="p-2.5">
                                    <h4 class="text-[11px] font-bold text-white group-hover:text-amber-400 transition truncate">{{ $item['title'] }}</h4>
                                    <span class="text-[9px] text-gray-400 mt-0.5 block truncate">{{ $item['time_str'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>

                <!-- Kids & Animation Column (CoComelon, Folktales, Superbook) -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('categories.show', 'kids') }}" class="flex items-center gap-2 group">
                            <span class="text-amber-500 font-bold text-xs">&#9658;</span>
                            <h2 class="text-base font-bold text-white group-hover:text-amber-400 transition">Kids, Cartoons &amp; Bible Stories &bull; 24/7</h2>
                            <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('categories.show', 'kids') }}" class="text-xs text-gray-400 hover:text-white transition">View All</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($kidsAnimation as $item)
                            <a href="{{ route('channels.show', $item['slug']) }}" class="group rounded-xl overflow-hidden bg-[#121620] border border-gray-800 hover:border-gray-700 transition flex flex-col shadow">
                                <div class="relative aspect-video w-full overflow-hidden bg-gray-900">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1566140967404-b8b3932483f5?auto=format&fit=crop&w=400&q=80';">
                                    <span class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-pink-900/80 text-[8px] font-bold text-pink-300 uppercase tracking-wider">Kids &amp; Family</span>
                                    <span class="absolute bottom-1.5 right-1.5 px-1.5 py-0.5 rounded bg-red-600/90 text-[8px] font-black text-white uppercase flex items-center gap-1">
                                        <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span>
                                        LIVE
                                    </span>
                                </div>
                                <div class="p-2.5">
                                    <h4 class="text-[11px] font-bold text-white group-hover:text-amber-400 transition truncate">{{ $item['title'] }}</h4>
                                    <span class="text-[9px] text-gray-400 mt-0.5 block truncate">{{ $item['time_str'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>

            </div>

        </div>

        <!-- Right Column: Sidebar Widgets (1 Column - Exact Reference Match) -->
        <div class="space-y-6">
            
            <!-- WIDGET 1: Tonight on Zonita (Schedule Widget) -->
            <div class="bg-[#121620] border border-gray-800 rounded-2xl p-5 shadow-lg">
                <div class="flex items-center justify-between pb-4 border-b border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="text-sm">📅</span>
                        <h3 class="text-sm font-bold text-white">Tonight on Zonita</h3>
                    </div>
                    <a href="{{ route('guide.index') }}" class="text-[10px] text-purple-400 hover:text-purple-300 font-semibold transition">
                        View Full Schedule
                    </a>
                </div>

                <!-- Schedule Rows -->
                <div class="divide-y divide-gray-800/80">
                    @foreach($tonightSchedule as $sched)
                        <a href="{{ route('channels.show', $sched['slug']) }}" class="py-3 flex items-center space-x-3 group hover:bg-gray-800/30 rounded-xl px-2 transition">
                            <span class="text-[11px] font-semibold text-gray-400 w-14 shrink-0 font-mono">{{ $sched['time'] }}</span>
                            
                            <div class="relative w-12 h-8 rounded-lg overflow-hidden shrink-0 bg-gray-900">
                                <img src="{{ $sched['image'] }}" alt="{{ $sched['title'] }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    <svg class="w-3 h-3 fill-white" viewBox="0 0 20 20"><path d="M4 4l12 6-12 6z"/></svg>
                                </div>
                            </div>

                            <div class="min-w-0 flex-grow">
                                <h4 class="text-xs font-bold text-white group-hover:text-amber-400 transition truncate">{{ $sched['title'] }}</h4>
                                <span class="text-[10px] text-gray-400">{{ $sched['category'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- WIDGET 2: Your Viewing Guide (Personalised Interests) -->
            <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-5 shadow-lg">
                <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                    <div class="flex items-center gap-1.5">
                        <span class="text-amber-400 text-sm">&#9733;</span>
                        <h3 class="text-sm font-bold text-white">Your Viewing Guide</h3>
                    </div>
                    <a href="{{ route('guide.personalized') }}" class="text-[10px] text-purple-400 hover:text-purple-300 font-semibold transition">
                        Manage
                    </a>
                </div>

                <div class="pt-3">
                    <span class="text-[11px] text-gray-400 block mb-2.5">Based on your interests</span>
                    
                    <!-- Interest Badges matching screenshot -->
                    <div class="flex flex-wrap items-center gap-2 mb-5">
                        <span class="px-3 py-1 rounded-full bg-purple-900/40 border border-purple-500/30 text-purple-300 text-[11px] font-semibold flex items-center gap-1">
                            <span>⚡</span> Sports
                        </span>
                        <span class="px-3 py-1 rounded-full bg-blue-900/40 border border-blue-500/30 text-blue-300 text-[11px] font-semibold flex items-center gap-1">
                            <span>📰</span> News
                        </span>
                        <span class="px-3 py-1 rounded-full bg-amber-900/40 border border-amber-500/30 text-amber-300 text-[11px] font-semibold flex items-center gap-1">
                            <span>✝</span> Christian
                        </span>
                    </div>

                    <!-- Recommended For You Mini-List -->
                    <h4 class="text-xs font-bold text-gray-300 mb-3">Recommended for you</h4>
                    <div class="space-y-2.5">
                        @foreach($recommendedForYou as $rec)
                            <a href="{{ route('channels.show', $rec['slug']) }}" class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-800/40 transition group">
                                <div class="flex items-center space-x-3">
                                    <img src="{{ $rec['image'] }}" alt="{{ $rec['title'] }}" class="w-10 h-7 object-cover rounded-lg shrink-0">
                                    <div>
                                        <h5 class="text-xs font-bold text-white group-hover:text-amber-400 transition truncate">{{ $rec['title'] }}</h5>
                                        <span class="text-[10px] text-gray-400">{{ $rec['category'] }} &bull; {{ $rec['time'] }}</span>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-500 group-hover:text-white group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- 3. Bottom Sticky / Promo Growth Bar (Exact Reference Match) -->
    <div class="relative rounded-2xl overflow-hidden bg-gradient-to-r from-purple-950/70 via-[#101420] to-[#0d1017] border border-purple-500/30 p-5 sm:p-6 shadow-2xl">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            
            <!-- Left: WhatsApp Section -->
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 fill-emerald-400" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.087-.179.181-.077.355.101.173.45 1.745 1.547 2.721 1.407 1.252 2.593 1.639 2.96 1.783.367.144.583.12.799-.129.217-.249.925-1.077 1.17-1.446.246-.369.491-.308.824-.184.332.124 2.107.994 2.468 1.173.361.179.602.268.69.418.086.15.086.87-.058 1.275z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white">Join Our WhatsApp Channel</h4>
                    <p class="text-xs text-gray-400">Get daily recommendations, new releases and exclusive updates.</p>
                </div>
            </div>

            <!-- Middle: Personalised Guide Prompt -->
            <div class="hidden md:flex items-center space-x-3 border-l border-gray-800/80 pl-6">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-white">Get your personalised viewing guide</h4>
                    <p class="text-[11px] text-gray-400">Choose your interests and never miss what you love.</p>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                <a href="{{ route('wa.join') }}" target="_blank" rel="noopener noreferrer" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-500 hover:bg-emerald-600 text-black font-bold text-xs shadow-lg transition">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.087-.179.181-.077.355.101.173.45 1.745 1.547 2.721 1.407 1.252 2.593 1.639 2.96 1.783.367.144.583.12.799-.129.217-.249.925-1.077 1.17-1.446.246-.369.491-.308.824-.184.332.124 2.107.994 2.468 1.173.361.179.602.268.69.418.086.15.086.87-.058 1.275z"/></svg>
                    Join WhatsApp
                </a>

                <a href="#newsletter-modal" onclick="document.getElementById('email-input')?.focus();" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gray-900/90 hover:bg-gray-800 text-gray-200 hover:text-white border border-gray-700 text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Subscribe by Email
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

        </div>
    </div>

</div>

@endsection
