<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zonita TV - Discover More. Watch Smarter.')</title>
    <meta name="description" content="@yield('meta_description', 'Discover verified television channels, live sports, breaking news, Christian worship, and original productions through one organised digital platform.')">
    <!-- Google Tag Manager -->
    @if(config('services.google.gtm_id'))
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ config('services.google.gtm_id') }}');</script>
    @endif
    <!-- End Google Tag Manager -->

    <meta name="google-site-verification" content="4m1NYPs5Z60SZPllspbNVhbJmHKV-K0qmBWuPZ1KuIk" />
    <meta name="google-site-verification" content="google98a49cab5be19ce2" />

    <!-- Google Analytics (gtag.js) -->
    @if(config('services.google.analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{ config('services.google.analytics_id') }}');
        </script>
    @endif

    <!-- Favicon (Using Official Zonita TV Logo) -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo/zonitatvlogo2.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo/zonitatvlogo2.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/zonitatvlogo2.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Styles and Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #0b0d13;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0c0e14; }
        ::-webkit-scrollbar-thumb { background: #1e2433; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }
    </style>
</head>
<body class="bg-[#0b0d13] text-gray-200 antialiased min-h-screen flex flex-col selection:bg-orange-600 selection:text-white">
    <!-- Google Tag Manager (noscript) -->
    @if(config('services.google.gtm_id'))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google.gtm_id') }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    <!-- End Google Tag Manager (noscript) -->

    <!-- Redesigned Two-Tier Navigation Architecture -->
    <header class="sticky top-0 z-50 bg-[#0b0d13]/95 backdrop-blur-md border-b border-gray-800/80"
            role="banner"
            x-data="{ 
                mobileNavOpen: false, 
                megaMenuOpen: false, 
                searchFilter: 'all',
                searchFilterOpen: false,
                userDropdownOpen: false,
                notificationsOpen: false,
                mobileCatExpanded: {
                    format: false,
                    genre: true,
                    faith: false
                }
            }"
            @keydown.escape.window="megaMenuOpen = false; userDropdownOpen = false; notificationsOpen = false; searchFilterOpen = false; mobileNavOpen = false"
            x-effect="document.body.style.overflow = mobileNavOpen ? 'hidden' : ''">

        @php
            $dropdownCategories = $navCategories ?? \App\Models\Category::where('is_nav_visible', true)->orderBy('sort_order')->get();
        @endphp

        <!-- ========================================================================= -->
        <!-- TIER 1: GLOBAL UTILITY HEADER (Brand, Global Search & System Actions)   -->
        <!-- ========================================================================= -->
        <div class="border-b border-gray-800/60 bg-[#0c0e14]">
            <div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-18 gap-3 sm:gap-6">
                    
                    <!-- 1. Brand Logo Anchor -->
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group focus:outline-none focus:ring-2 focus:ring-amber-500 rounded-lg p-1" aria-label="Zonita TV Homepage">
                            <img src="{{ asset('images/logo/zonitatvlogo2.png') }}" alt="Zonita TV" class="h-9 sm:h-10 xl:h-11 w-auto object-contain transition-transform duration-200 group-hover:scale-105">
                            <span class="hidden xl:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                Discovery Platform
                            </span>
                        </a>
                    </div>

                    <!-- 2. Global Predictive Search Bar with Filter Shortcut -->
                    <div class="hidden md:flex flex-1 max-w-xl mx-auto px-2">
                        <form action="{{ route('search') }}" method="GET" class="w-full relative flex items-center" role="search">
                            <div class="relative w-full group">
                                <div class="flex items-center w-full bg-[#131722] hover:bg-[#161b28] focus-within:bg-[#161b28] border border-gray-700/70 focus-within:border-amber-500/80 rounded-full transition-all duration-200 shadow-inner">
                                    
                                    <!-- Search Icon -->
                                    <div class="pl-3.5 pr-2 text-gray-400 group-focus-within:text-amber-400 transition-colors pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>

                                    <!-- Search Input Field -->
                                    <input type="text" 
                                           name="q" 
                                           id="global-search-input"
                                           value="{{ request('q') }}" 
                                           placeholder="Search channels, live football, movies, gospel..." 
                                           autocomplete="off"
                                           class="w-full bg-transparent py-2 text-xs text-gray-100 placeholder-gray-500 focus:outline-none pr-2">

                                    <!-- Filter Shortcut Dropdown -->
                                    <div class="relative shrink-0 border-l border-gray-700/60 my-1" @click.away="searchFilterOpen = false">
                                        <button type="button" 
                                                @click="searchFilterOpen = !searchFilterOpen" 
                                                class="flex items-center gap-1.5 px-3 py-1 text-[11px] font-medium text-gray-400 hover:text-white transition focus:outline-none"
                                                aria-label="Filter search results by type">
                                            <span x-text="searchFilter === 'all' ? 'All' : (searchFilter === 'channels' ? 'Channels' : (searchFilter === 'sports' ? 'Sports' : 'Movies'))">All</span>
                                            <svg class="w-3 h-3 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': searchFilterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Filter Options Popup -->
                                        <div x-show="searchFilterOpen" 
                                             x-cloak
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             class="absolute right-0 mt-2 w-32 bg-[#121622] border border-gray-700 rounded-xl shadow-xl py-1 z-50 text-xs divide-y divide-gray-800">
                                            <button type="button" @click="searchFilter = 'all'; searchFilterOpen = false" class="w-full text-left px-3 py-1.5 hover:bg-gray-800 text-gray-300 hover:text-white flex items-center justify-between">
                                                <span>All Formats</span>
                                                <span x-show="searchFilter === 'all'" class="text-amber-400 font-bold">✓</span>
                                            </button>
                                            <button type="button" @click="searchFilter = 'channels'; searchFilterOpen = false" class="w-full text-left px-3 py-1.5 hover:bg-gray-800 text-gray-300 hover:text-white flex items-center justify-between">
                                                <span>Channels</span>
                                                <span x-show="searchFilter === 'channels'" class="text-amber-400 font-bold">✓</span>
                                            </button>
                                            <button type="button" @click="searchFilter = 'sports'; searchFilterOpen = false" class="w-full text-left px-3 py-1.5 hover:bg-gray-800 text-gray-300 hover:text-white flex items-center justify-between">
                                                <span>Live Sports</span>
                                                <span x-show="searchFilter === 'sports'" class="text-amber-400 font-bold">✓</span>
                                            </button>
                                            <button type="button" @click="searchFilter = 'movies'; searchFilterOpen = false" class="w-full text-left px-3 py-1.5 hover:bg-gray-800 text-gray-300 hover:text-white flex items-center justify-between">
                                                <span>Movies</span>
                                                <span x-show="searchFilter === 'movies'" class="text-amber-400 font-bold">✓</span>
                                            </button>
                                        </div>
                                        <input type="hidden" name="type" :value="searchFilter !== 'all' ? searchFilter : ''">
                                    </div>

                                    <!-- Keyboard Shortcut Badge -->
                                    <div class="hidden lg:flex items-center pr-3 pointer-events-none">
                                        <kbd class="px-1.5 py-0.5 text-[10px] font-mono font-medium text-gray-400 bg-gray-800/80 border border-gray-700 rounded shadow-sm">⌘K</kbd>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- 3. System Utility Actions -->
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        
                        <!-- Mobile Search Toggle -->
                        <a href="{{ route('search') }}" 
                           class="md:hidden p-2 rounded-xl text-gray-400 hover:text-white hover:bg-gray-800 transition" 
                           aria-label="Search channels and movies">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </a>

                        <!-- Notifications Bell with Red Indicator Badge -->
                        <div class="relative" @click.away="notificationsOpen = false">
                            <button type="button" 
                                    @click="notificationsOpen = !notificationsOpen" 
                                    class="relative p-2 rounded-xl text-gray-400 hover:text-white hover:bg-gray-800 transition focus:outline-none focus:ring-2 focus:ring-amber-500" 
                                    aria-label="View system notifications"
                                    :aria-expanded="notificationsOpen.toString()">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span class="absolute top-1.5 right-1.5 flex h-4 w-4">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-red-600 text-[9px] font-extrabold text-white items-center justify-center">1</span>
                                </span>
                            </button>

                            <!-- Notifications Popover -->
                            <div x-show="notificationsOpen" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute right-0 mt-2 w-80 rounded-2xl bg-[#121622] border border-gray-800 shadow-2xl py-3 z-50 divide-y divide-gray-800/80">
                                <div class="px-4 pb-2 flex items-center justify-between">
                                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Notifications</h3>
                                    <span class="text-[10px] font-semibold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full">New Update</span>
                                </div>
                                <div class="py-2 px-3">
                                    <a href="{{ route('guide.index') }}" class="flex items-start gap-3 p-2 rounded-xl hover:bg-gray-800/60 transition group">
                                        <div class="w-8 h-8 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        </div>
                                        <div class="text-xs">
                                            <p class="font-semibold text-gray-200 group-hover:text-white">Live Broadcast Scheduled</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">Premier League & African Cup matchday coverage is now live on SportyTV.</p>
                                            <span class="text-[10px] text-gray-500 mt-1 block">10 mins ago</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="pt-2 px-4 text-center">
                                    <a href="{{ route('guide.index') }}" class="text-[11px] font-semibold text-amber-400 hover:text-amber-300 transition">View Live TV Guide &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp Community CTA Pill -->
                        <a href="{{ route('wa.join') }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-full transition shadow-sm"
                           title="Join Zonita TV WhatsApp Channel">
                            <svg class="w-3.5 h-3.5 fill-current text-emerald-400 shrink-0" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.087-.179.181-.077.355.101.173.45 1.745 1.547 2.721 1.407 1.252 2.593 1.639 2.96 1.783.367.144.583.12.799-.129.217-.249.925-1.077 1.17-1.446.246-.369.491-.308.824-.184.332.124 2.107.994 2.468 1.173.361.179.602.268.69.418.086.15.086.87-.058 1.275z"/>
                            </svg>
                            <span>WhatsApp</span>
                        </a>

                        <!-- User Profile Dropdown (BE ⌵) / Guest Authentication -->
                        @guest
                            <div class="flex items-center gap-2">
                                <a href="{{ route('login') }}" class="text-xs font-semibold text-gray-300 hover:text-white px-2.5 py-1.5 rounded-lg hover:bg-gray-800/60 transition">
                                    Sign In
                                </a>
                                <a href="{{ route('register') }}" class="inline-flex items-center text-xs font-bold text-black bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 px-3.5 py-1.5 rounded-full shadow-md shadow-orange-500/20 transition">
                                    Register
                                </a>
                            </div>
                        @else
                            <div class="relative" @click.away="userDropdownOpen = false">
                                <button @click="userDropdownOpen = !userDropdownOpen" 
                                        type="button" 
                                        class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-800/80 transition focus:outline-none focus:ring-2 focus:ring-amber-500 group"
                                        aria-label="User Account Menu"
                                        :aria-expanded="userDropdownOpen.toString()">
                                    <div class="w-8 h-8 rounded-full ring-2 ring-amber-500/40 group-hover:ring-amber-400 overflow-hidden bg-gray-800 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                        @if(auth()->user()->avatar_url)
                                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span>{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <span class="hidden md:inline-flex items-center gap-1 text-xs font-semibold text-gray-300 group-hover:text-white">
                                        <span class="max-w-[90px] truncate">{{ auth()->user()->name }}</span>
                                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-white transition-transform duration-200" :class="{ 'rotate-180': userDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </span>
                                </button>

                                <!-- Authenticated Dropdown Menu -->
                                <div x-show="userDropdownOpen" 
                                     x-cloak 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="transform opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="transform opacity-0 scale-95 -translate-y-1"
                                     class="absolute right-0 mt-2 w-60 rounded-2xl bg-[#121622] border border-gray-800 shadow-2xl py-2 z-50 text-xs divide-y divide-gray-800/80">
                                    <div class="px-4 py-2.5">
                                        <p class="font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</p>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-800 text-amber-400 border border-gray-700">
                                            {{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Member')) }}
                                        </span>
                                    </div>

                                    <div class="py-1">
                                        <a href="{{ route('profile.show') }}" class="flex items-center gap-2.5 px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/60 transition">
                                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Account Settings</span>
                                        </a>

                                        <a href="{{ route('guide.personalized') }}" class="flex items-center gap-2.5 px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/60 transition">
                                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                            <span>My Personalised Guide</span>
                                        </a>

                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isContentManager())
                                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/60 transition">
                                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>Control Hub (Admin)</span>
                                            </a>
                                        @endif
                                    </div>

                                    <div class="py-1">
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 transition text-left">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                <span>Sign Out</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endguest

                        <!-- Mobile Hamburger Drawer Trigger (lg:hidden) -->
                        <button type="button" 
                                @click="mobileNavOpen = true" 
                                class="lg:hidden p-2 rounded-xl text-gray-300 hover:text-white hover:bg-gray-800 transition focus:outline-none focus:ring-2 focus:ring-amber-500"
                                aria-label="Open mobile navigation menu"
                                :aria-expanded="mobileNavOpen.toString()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TIER 2: PRIMARY NAVIGATION BAR (Lean, Scalable & High-Level Architecture) -->
        <!-- ========================================================================= -->
        <div class="hidden lg:block bg-[#0e111a]/95 border-b border-gray-800/40 shadow-sm relative">
            <div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-11">
                    
                    <!-- Strictly 4–5 Primary Nav Sections -->
                    <nav class="flex items-center space-x-1" aria-label="Platform Main Menu">
                        <ul class="flex items-center space-x-1" role="menubar">
                            
                            <!-- 1. Home -->
                            <li role="none">
                                <a href="{{ route('home') }}" 
                                   role="menuitem"
                                   class="relative inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold transition group {{ request()->routeIs('home') ? 'text-amber-400 bg-amber-500/10' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}">
                                    <span>Home</span>
                                    @if(request()->routeIs('home'))
                                        <span class="absolute bottom-0 left-3 right-3 h-0.5 bg-amber-400 rounded-full"></span>
                                    @endif
                                </a>
                            </li>

                            <!-- 2. Live TV (Channel Guide / EPG) -->
                            <li role="none">
                                <a href="{{ route('guide.index') }}" 
                                   role="menuitem"
                                   class="relative inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition group {{ request()->routeIs('guide.*') ? 'text-amber-400 bg-amber-500/10' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                    <span>Live TV</span>
                                    @if(request()->routeIs('guide.*'))
                                        <span class="absolute bottom-0 left-3 right-3 h-0.5 bg-amber-400 rounded-full"></span>
                                    @endif
                                </a>
                            </li>

                            <!-- 3. Channels (Directory of 120+ Verified Stations) -->
                            <li role="none">
                                <a href="{{ route('channels.index') }}" 
                                   role="menuitem"
                                   class="relative inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold transition group {{ request()->routeIs('channels.*') ? 'text-amber-400 bg-amber-500/10' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}">
                                    <span>Channels</span>
                                    @if(request()->routeIs('channels.*'))
                                        <span class="absolute bottom-0 left-3 right-3 h-0.5 bg-amber-400 rounded-full"></span>
                                    @endif
                                </a>
                            </li>

                            <!-- 4. Movies (Cinema & On-Demand Library) -->
                            <li role="none">
                                <a href="{{ route('movies.index') }}" 
                                   role="menuitem"
                                   class="relative inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold transition group {{ request()->routeIs('movies.*') ? 'text-amber-400 bg-amber-500/10' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}">
                                    <span>Movies</span>
                                    @if(request()->routeIs('movies.*'))
                                        <span class="absolute bottom-0 left-3 right-3 h-0.5 bg-amber-400 rounded-full"></span>
                                    @endif
                                </a>
                            </li>

                            <!-- 5. Categories ⌵ (Consolidated Mega-Menu Trigger) -->
                            <li role="none" class="relative" @click.away="megaMenuOpen = false">
                                <button @click="megaMenuOpen = !megaMenuOpen"
                                        type="button"
                                        role="menuitem"
                                        aria-haspopup="menu"
                                        :aria-expanded="megaMenuOpen.toString()"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition focus:outline-none"
                                        :class="megaMenuOpen || {{ (request()->is('category*') || request()->routeIs('nollywood.*') || request()->routeIs('comedy.*') || request()->routeIs('sports.*') || request()->routeIs('gospel.*') || request()->routeIs('islamic.*')) ? 'true' : 'false' }} ? 'text-amber-400 bg-amber-500/10' : 'text-gray-300 hover:text-white hover:bg-gray-800/50'">
                                    <span>Categories</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
                                         :class="{ 'rotate-180 text-amber-400': megaMenuOpen }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- ========================================================================= -->
                                <!-- MEGA MENU DROPDOWN PANEL (Consolidated Content Taxonomies)               -->
                                <!-- ========================================================================= -->
                                <div x-show="megaMenuOpen"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                                     role="menu"
                                     aria-label="Content Categories and Discovery Hubs"
                                     class="absolute left-0 mt-2 w-[880px] xl:w-[1040px] rounded-2xl bg-[#10141e]/98 backdrop-blur-xl border border-gray-800/90 shadow-2xl p-6 z-50">
                                    
                                    <div class="grid grid-cols-12 gap-6">
                                        
                                        <!-- Column 1: By Format & Playout (3 cols) -->
                                        <div class="col-span-3 space-y-3">
                                            <div class="flex items-center gap-2 pb-1 border-b border-gray-800/60">
                                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">By Format</span>
                                            </div>
                                            <div class="space-y-1">
                                                <a href="{{ route('channels.index') }}" role="menuitem" class="group flex items-start gap-2.5 p-2 rounded-xl hover:bg-gray-800/60 transition">
                                                    <div class="w-6 h-6 rounded-lg bg-gray-800 flex items-center justify-center text-gray-300 group-hover:text-amber-400 shrink-0 mt-0.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-semibold text-gray-200 group-hover:text-amber-400 transition">All Channels</p>
                                                        <p class="text-[10px] text-gray-500">120+ verified live stations</p>
                                                    </div>
                                                </a>

                                                <a href="{{ route('guide.index') }}" role="menuitem" class="group flex items-start gap-2.5 p-2 rounded-xl hover:bg-gray-800/60 transition">
                                                    <div class="w-6 h-6 rounded-lg bg-gray-800 flex items-center justify-center text-gray-300 group-hover:text-red-400 shrink-0 mt-0.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-semibold text-gray-200 group-hover:text-red-400 transition">Live EPG Guide</p>
                                                        <p class="text-[10px] text-gray-500">Real-time programme schedules</p>
                                                    </div>
                                                </a>

                                                <a href="{{ route('movies.index') }}" role="menuitem" class="group flex items-start gap-2.5 p-2 rounded-xl hover:bg-gray-800/60 transition">
                                                    <div class="w-6 h-6 rounded-lg bg-gray-800 flex items-center justify-center text-gray-300 group-hover:text-amber-400 shrink-0 mt-0.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-semibold text-gray-200 group-hover:text-amber-400 transition">On-Demand Movies</p>
                                                        <p class="text-[10px] text-gray-500">Full-length African cinema</p>
                                                    </div>
                                                </a>

                                                <a href="{{ route('categories.show', 'zoniva-originals') }}" role="menuitem" class="group flex items-start gap-2.5 p-2 rounded-xl hover:bg-gray-800/60 transition">
                                                    <div class="w-6 h-6 rounded-lg bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-semibold text-gray-200 group-hover:text-orange-400 transition">Zoniva Originals</p>
                                                        <p class="text-[10px] text-gray-500">Exclusive sports & documentaries</p>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Column 2: By Genre & Entertainment (3 cols) -->
                                        <div class="col-span-3 space-y-3">
                                            <div class="flex items-center gap-2 pb-1 border-b border-gray-800/60">
                                                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">By Genre</span>
                                            </div>
                                            <div class="space-y-1">
                                                <a href="{{ route('nollywood.index') }}" role="menuitem" class="group flex items-center justify-between p-2 rounded-xl hover:bg-amber-500/10 transition border border-transparent hover:border-amber-500/20">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                                        <span class="text-xs font-semibold text-gray-200 group-hover:text-amber-400">Nollywood Cinema</span>
                                                    </div>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30">60 Ch</span>
                                                </a>

                                                <a href="{{ route('comedy.index') }}" role="menuitem" class="group flex items-center justify-between p-2 rounded-xl hover:bg-orange-500/10 transition border border-transparent hover:border-orange-500/20">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                                                        <span class="text-xs font-semibold text-gray-200 group-hover:text-orange-400">Comedy Central</span>
                                                    </div>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-orange-500/10 text-orange-400 border border-orange-500/30">10 Ch</span>
                                                </a>

                                                <a href="{{ route('sports.index') }}" role="menuitem" class="group flex items-center justify-between p-2 rounded-xl hover:bg-red-500/10 transition border border-transparent hover:border-red-500/20">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                                        <span class="text-xs font-semibold text-gray-200 group-hover:text-red-400">Sports Broadcasting</span>
                                                    </div>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-600/20 text-red-400 border border-red-500/30 uppercase">Live</span>
                                                </a>

                                                <a href="{{ route('categories.show', 'entertainment') }}" role="menuitem" class="group flex items-center justify-between p-2 rounded-xl hover:bg-purple-500/10 transition border border-transparent hover:border-purple-500/20">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                                                        <span class="text-xs font-semibold text-gray-200 group-hover:text-purple-300">Entertainment & Lifestyle</span>
                                                    </div>
                                                    <span class="text-[10px] text-gray-500 group-hover:text-gray-300">&rarr;</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Column 3: By Faith, Family & Culture (3 cols) -->
                                        <div class="col-span-3 space-y-3">
                                            <div class="flex items-center gap-2 pb-1 border-b border-gray-800/60">
                                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Faith & Culture</span>
                                            </div>
                                            <div class="space-y-1">
                                                <a href="{{ route('gospel.index') }}" role="menuitem" class="group flex items-center justify-between p-2 rounded-xl hover:bg-amber-500/10 transition border border-transparent hover:border-amber-500/20">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                                        <span class="text-xs font-semibold text-gray-200 group-hover:text-amber-400">Gospel TV & Ministry</span>
                                                    </div>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30">Worship</span>
                                                </a>

                                                <a href="{{ route('islamic.index') }}" role="menuitem" class="group flex items-center justify-between p-2 rounded-xl hover:bg-emerald-500/10 transition border border-transparent hover:border-emerald-500/20">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                                        <span class="text-xs font-semibold text-gray-200 group-hover:text-emerald-400">Islamic TV & Cinema</span>
                                                    </div>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">8 Ch</span>
                                                </a>

                                                <a href="{{ route('categories.show', 'news') }}" role="menuitem" class="group flex items-center justify-between p-2 rounded-xl hover:bg-blue-500/10 transition border border-transparent hover:border-blue-500/20">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                                                        <span class="text-xs font-semibold text-gray-200 group-hover:text-blue-300">News & Current Affairs</span>
                                                    </div>
                                                    <span class="text-[10px] text-gray-500 group-hover:text-gray-300">&rarr;</span>
                                                </a>

                                                <a href="{{ route('categories.show', 'education') }}" role="menuitem" class="group flex items-center justify-between p-2 rounded-xl hover:bg-cyan-500/10 transition border border-transparent hover:border-cyan-500/20">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                                                        <span class="text-xs font-semibold text-gray-200 group-hover:text-cyan-300">Kids & Education</span>
                                                    </div>
                                                    <span class="text-[10px] text-gray-500 group-hover:text-gray-300">&rarr;</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Column 4: Curated Live Feature Hub / Spotlight Card (3 cols) -->
                                        <div class="col-span-3">
                                            <div class="h-full rounded-2xl bg-gradient-to-br from-gray-900/90 to-[#151a26] border border-gray-800 p-4 flex flex-col justify-between shadow-inner">
                                                <div>
                                                    <div class="flex items-center justify-between mb-3">
                                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-red-600/20 border border-red-500/30 text-red-400 text-[10px] font-bold uppercase">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                                            Spotlight
                                                        </span>
                                                        <span class="text-[10px] font-semibold text-gray-500">Free Stream</span>
                                                    </div>
                                                    <h4 class="text-xs font-bold text-white leading-snug">The Matchroom Africa</h4>
                                                    <p class="text-[11px] text-gray-400 mt-1 line-clamp-2">Exclusive studio sports panel dissecting African football stars and international fixtures.</p>
                                                </div>

                                                <div class="mt-4 pt-3 border-t border-gray-800/80">
                                                    <a href="{{ route('sports.index') }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black text-xs font-bold shadow-md transition">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                        <span>Watch Sports Hub</span>
                                                    </a>
                                                    <div class="flex items-center justify-between text-[10px] text-gray-500 mt-2 px-1">
                                                        <span>120+ Verified Streams</span>
                                                        <span>100% Free Access</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </li>

                        </ul>
                    </nav>

                    <!-- Auxiliary Secondary Discovery Action (Desktop Right) -->
                    <div class="hidden xl:flex items-center gap-3 text-xs">
                        <a href="{{ route('sports.live') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 transition font-medium text-[11px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            <span>Live Matchday Stream Active</span>
                        </a>
                        <span class="text-gray-700">|</span>
                        <a href="{{ route('channels.index') }}" class="text-[11px] text-gray-400 hover:text-amber-400 transition font-medium">
                            Explore 120+ Channels &rarr;
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MOBILE / TABLET OFF-CANVAS RESPONSIVE DRAWER                              -->
        <!-- ========================================================================= -->
        <div x-show="mobileNavOpen" 
             x-cloak
             class="fixed inset-0 z-50 lg:hidden"
             role="dialog" 
             aria-modal="true"
             aria-label="Mobile Navigation Menu">
            
            <!-- Backdrop Overlay with blur -->
            <div x-show="mobileNavOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                 @click="mobileNavOpen = false"></div>

            <!-- Slide-in Drawer Container -->
            <div x-show="mobileNavOpen"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed inset-y-0 right-0 max-w-xs sm:max-w-sm w-full bg-[#0c0f17] border-l border-gray-800 shadow-2xl p-5 overflow-y-auto flex flex-col justify-between">
                
                <div class="space-y-6">
                    <!-- Drawer Header: Brand + Close Button -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-800">
                        <a href="{{ route('home') }}" @click="mobileNavOpen = false" class="flex items-center gap-2">
                            <img src="{{ asset('images/logo/zonitatvlogo2.png') }}" alt="Zonita TV" class="h-8 w-auto">
                        </a>
                        <button type="button" 
                                @click="mobileNavOpen = false" 
                                class="p-2 rounded-xl text-gray-400 hover:text-white hover:bg-gray-800 transition"
                                aria-label="Close navigation menu">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Search Input in Drawer -->
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <input type="text" name="q" placeholder="Search channels, movies..." 
                               class="w-full bg-[#131722] text-xs text-gray-200 placeholder-gray-500 pl-9 pr-3 py-2.5 rounded-xl border border-gray-700/80 focus:outline-none focus:border-amber-500 transition">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </form>

                    <!-- Primary High-Level Navigation -->
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 px-3">Main Navigation</p>
                        <a href="{{ route('home') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('home') ? 'bg-amber-500 text-black font-bold' : 'text-gray-200 hover:bg-gray-800/60' }} transition">
                            <span>Home</span>
                            <span class="text-[10px] text-gray-400">&rarr;</span>
                        </a>
                        <a href="{{ route('guide.index') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('guide.*') ? 'bg-amber-500 text-black font-bold' : 'text-gray-200 hover:bg-gray-800/60' }} transition">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                <span>Live TV Guide</span>
                            </span>
                            <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-red-600/20 text-red-400">Live</span>
                        </a>
                        <a href="{{ route('channels.index') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('channels.*') ? 'bg-amber-500 text-black font-bold' : 'text-gray-200 hover:bg-gray-800/60' }} transition">
                            <span>Channels (120+)</span>
                            <span class="text-[10px] text-gray-400">&rarr;</span>
                        </a>
                        <a href="{{ route('movies.index') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('movies.*') ? 'bg-amber-500 text-black font-bold' : 'text-gray-200 hover:bg-gray-800/60' }} transition">
                            <span>Movies Library</span>
                            <span class="text-[10px] text-gray-400">&rarr;</span>
                        </a>
                    </div>

                    <!-- Categorized Content Verticals (Accordion Grouping) -->
                    <div class="space-y-2 pt-2 border-t border-gray-800">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 px-3">Browse Verticals</p>
                        
                        <!-- Accordion: Entertainment & Genres -->
                        <div class="rounded-xl border border-gray-800/80 bg-gray-900/30 overflow-hidden">
                            <button type="button" 
                                    @click="mobileCatExpanded.genre = !mobileCatExpanded.genre"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-bold text-gray-200 hover:text-white transition">
                                <span class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                                    <span>Genre Verticals</span>
                                </span>
                                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': mobileCatExpanded.genre }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="mobileCatExpanded.genre" class="px-3 pb-2.5 space-y-1">
                                <a href="{{ route('nollywood.index') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-300 hover:text-amber-400 hover:bg-gray-800/50 transition">
                                    <span>Nollywood Cinema</span>
                                    <span class="text-[10px] text-amber-400 font-bold">60 Ch</span>
                                </a>
                                <a href="{{ route('comedy.index') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-300 hover:text-orange-400 hover:bg-gray-800/50 transition">
                                    <span>Comedy Central</span>
                                    <span class="text-[10px] text-orange-400 font-bold">10 Ch</span>
                                </a>
                                <a href="{{ route('sports.index') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-300 hover:text-red-400 hover:bg-gray-800/50 transition">
                                    <span>Sports Broadcasting</span>
                                    <span class="text-[10px] text-red-400 font-bold uppercase">Live</span>
                                </a>
                            </div>
                        </div>

                        <!-- Accordion: Faith & Family -->
                        <div class="rounded-xl border border-gray-800/80 bg-gray-900/30 overflow-hidden">
                            <button type="button" 
                                    @click="mobileCatExpanded.faith = !mobileCatExpanded.faith"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-bold text-gray-200 hover:text-white transition">
                                <span class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Faith & Family</span>
                                </span>
                                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': mobileCatExpanded.faith }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="mobileCatExpanded.faith" class="px-3 pb-2.5 space-y-1">
                                <a href="{{ route('gospel.index') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-300 hover:text-amber-400 hover:bg-gray-800/50 transition">
                                    <span>Gospel TV & Ministry</span>
                                    <span class="text-[10px] text-amber-400">Worship</span>
                                </a>
                                <a href="{{ route('islamic.index') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-300 hover:text-emerald-400 hover:bg-gray-800/50 transition">
                                    <span>Islamic TV & Cinema</span>
                                    <span class="text-[10px] text-emerald-400 font-bold">8 Ch</span>
                                </a>
                                <a href="{{ route('categories.show', 'news') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-300 hover:text-blue-300 hover:bg-gray-800/50 transition">
                                    <span>News & Current Affairs</span>
                                    <span class="text-[10px] text-gray-500">&rarr;</span>
                                </a>
                                <a href="{{ route('categories.show', 'education') }}" @click="mobileNavOpen = false" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-300 hover:text-cyan-300 hover:bg-gray-800/50 transition">
                                    <span>Kids & Education</span>
                                    <span class="text-[10px] text-gray-500">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Drawer Footer: WhatsApp Community & User Profile / Login -->
                <div class="pt-6 border-t border-gray-800 space-y-3">
                    <a href="{{ route('wa.join') }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="w-full flex items-center justify-center gap-2 py-2 px-3 text-xs font-bold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-xl transition">
                        <svg class="w-4 h-4 fill-current text-emerald-400" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.087-.179.181-.077.355.101.173.45 1.745 1.547 2.721 1.407 1.252 2.593 1.639 2.96 1.783.367.144.583.12.799-.129.217-.249.925-1.077 1.17-1.446.246-.369.491-.308.824-.184.332.124 2.107.994 2.468 1.173.361.179.602.268.69.418.086.15.086.87-.058 1.275z"/></svg>
                        <span>Join WhatsApp Community</span>
                    </a>

                    @guest
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('login') }}" class="w-full text-center py-2 px-3 rounded-xl border border-gray-700 text-xs font-semibold text-gray-300 hover:text-white hover:bg-gray-800 transition">
                                Sign In
                            </a>
                            <a href="{{ route('register') }}" class="w-full text-center py-2 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-black text-xs font-bold shadow-md transition">
                                Register
                            </a>
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-gray-900/60 border border-gray-800 flex items-center justify-between">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full ring-2 ring-amber-500/40 overflow-hidden bg-gray-800 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-gray-400 truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-red-400 hover:bg-red-500/10 transition" title="Sign Out">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </button>
                            </form>
                        </div>
                    @endguest
                </div>

            </div>
        </div>

    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="bg-emerald-500/10 border-b border-emerald-500/30 text-emerald-300 text-xs px-4 py-2.5 text-center">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-500/10 border-b border-red-500/30 text-red-300 text-xs px-4 py-2.5 text-center">
            {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-amber-500/10 border-b border-amber-500/30 text-amber-300 text-xs px-4 py-2.5 text-center">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('status'))
        <div class="bg-blue-500/10 border-b border-blue-500/30 text-blue-300 text-xs px-4 py-2.5 text-center">
            {{ session('status') }}
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#080a0e] border-t border-gray-800/60 mt-20 pt-12 pb-8 text-gray-400 text-xs">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-8 border-b border-gray-800/60">
                <!-- Brand & Mission -->
                <div class="md:col-span-1 space-y-3">
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('images/logo/zonitatvlogo2.png') }}" alt="Zonita TV" class="h-8 w-auto">
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Continuous virtual television & smart electronic programming guide. Discover verified news, sports commentary, Christian worship, and entertainment.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('wa.join') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-lg transition">
                            <svg class="w-3.5 h-3.5 fill-current text-emerald-400" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.087-.179.181-.077.355.101.173.45 1.745 1.547 2.721 1.407 1.252 2.593 1.639 2.96 1.783.367.144.583.12.799-.129.217-.249.925-1.077 1.17-1.446.246-.369.491-.308.824-.184.332.124 2.107.994 2.468 1.173.361.179.602.268.69.418.086.15.086.87-.058 1.275z"/></svg>
                            Join Official WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Navigation & Guide -->
                <div class="space-y-2 text-xs">
                    <p class="font-bold text-white uppercase tracking-wider text-[11px] mb-3">Explore & Watch</p>
                    <ul class="space-y-1.5">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                        <li><a href="{{ route('guide.index') }}" class="hover:text-white transition">24/7 Live TV Playout</a></li>
                        <li><a href="{{ route('channels.index') }}" class="hover:text-white transition">Channels Directory</a></li>
                        <li><a href="{{ route('movies.index') }}" class="hover:text-amber-400 transition font-medium">Movies & Cinema Directory</a></li>
                        <li><a href="{{ route('gospel.index') }}" class="hover:text-amber-400 transition font-medium">Gospel Film Directory (60)</a></li>
                        <li><a href="{{ route('islamic.index') }}" class="hover:text-emerald-400 transition font-medium">Islamic TV &amp; Cinema (8 Channels)</a></li>
                        <li><a href="{{ route('categories.show', 'sports') }}" class="hover:text-white transition">Sports Hub</a></li>
                        <li><a href="{{ route('categories.show', 'news') }}" class="hover:text-white transition">Live News Bulletins</a></li>
                        <li><a href="{{ route('categories.show', 'christian-tv') }}" class="hover:text-white transition">Christian Television</a></li>
                        <li><a href="{{ route('categories.show', 'kids') }}" class="hover:text-white transition">Kids & Cartoons</a></li>
                    </ul>
                </div>

                <!-- Legal & Compliance Framework -->
                <div class="space-y-2 text-xs">
                    <p class="font-bold text-white uppercase tracking-wider text-[11px] mb-3">Legal & Governance</p>
                    <ul class="space-y-1.5">
                        <li><a href="{{ route('legal.disclaimer') }}" class="hover:text-amber-400 transition">Legal Disclaimer</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="hover:text-amber-400 transition">Terms of Use</a></li>
                        <li><a href="{{ route('legal.copyright') }}" class="hover:text-amber-400 transition">Copyright Policy</a></li>
                        <li><a href="{{ route('legal.content-rights') }}" class="hover:text-amber-400 transition">Content Rights & Verification</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="hover:text-amber-400 transition">Privacy Policy (NDPA)</a></li>
                        <li><a href="{{ route('legal.advertising') }}" class="hover:text-amber-400 transition">Advertising & Sponsorship</a></li>
                    </ul>
                </div>

                <!-- Rights & Complaints Reporting -->
                <div class="space-y-3 text-xs">
                    <p class="font-bold text-white uppercase tracking-wider text-[11px] mb-1">Content Protection</p>
                    <p class="text-gray-400 text-[11px]">
                        Are you a copyright holder or broadcaster? We respect intellectual property and provide a rapid notice-and-takedown procedure.
                    </p>
                    <a href="{{ route('legal.report') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-500 transition shadow-lg shadow-red-600/20">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Report Content / Takedown
                    </a>
                    <div class="text-[11px] text-gray-500">
                        Notice desk: <a href="mailto:copyright@zonitatv.com" class="text-amber-400 underline">copyright@zonitatv.com</a>
                    </div>
                </div>
            </div>

            <!-- Third-Party Embedding Disclosure Note -->
            <div class="py-4 text-[11px] text-gray-500 leading-relaxed border-b border-gray-800/60">
                <p>
                    <strong class="text-gray-400">Platform Transparency Notice:</strong> Zonita TV is an independent discovery and virtual scheduling guide. Linear video streams are displayed utilizing YouTube's official embedded player technology. Zonita TV does not host, download, or re-encode third-party media files. All trademarks, channel logos, and video broadcasts belong to their respective rights holders.
                </p>
            </div>

            <!-- Bottom Copyright & Status -->
            <div class="pt-6 text-center md:text-left text-gray-500 text-[11px] flex flex-col md:flex-row items-center justify-between gap-2">
                <p>&copy; {{ date('Y') }} Zonita TV. All rights reserved. Operating context: Federal Republic of Nigeria.</p>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Protected Embeds</span>
                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">Takedown Active</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
