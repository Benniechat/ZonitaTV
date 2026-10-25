<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zonita TV - Discover More. Watch Smarter.')</title>
    <meta name="description" content="@yield('meta_description', 'Discover verified television channels, live sports, breaking news, Christian worship, and original productions through one organised digital platform.')">
    <meta name="google-site-verification" content="4m1NYPs5Z60SZPllspbNVhbJmHKV-K0qmBWuPZ1KuIk" />
    <meta name="google-site-verification" content="google98a49cab5be19ce2" />

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

    <!-- Top Navigation Bar matching Reference Layout -->
    <header class="sticky top-0 z-50 bg-[#0b0d13]/95 backdrop-blur-md border-b border-gray-800/60">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo & Nav Links -->
                <div class="flex items-center space-x-4 xl:space-x-8">
                    <a href="{{ route('home') }}" class="flex items-center group shrink-0">
                        <img src="{{ asset('images/logo/zonitatvlogo2.png') }}" alt="Zonita TV" class="h-9 sm:h-10 xl:h-11 w-auto object-contain transition-transform group-hover:scale-105">
                    </a>

                    <!-- Navigation Links with Amber active pill indicator -->
                    <nav class="hidden lg:flex items-center space-x-0.5 xl:space-x-1 text-xs xl:text-sm font-medium">
                        <a href="{{ route('home') }}" class="relative px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('home') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition group shrink-0">
                            Home
                            @if(request()->routeIs('home'))
                                <span class="absolute bottom-0 left-2.5 right-2.5 xl:left-3 xl:right-3 h-0.5 bg-amber-400 rounded-full"></span>
                            @endif
                        </a>
                        <a href="{{ route('guide.index') }}" class="px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('guide.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0">Live TV</a>
                        <a href="{{ route('channels.index') }}" class="px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('channels.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0">Channels</a>

                        <!-- Categories Dropdown Menu -->
                        @php
                            $dropdownCategories = $navCategories ?? \App\Models\Category::where('is_nav_visible', true)->orderBy('sort_order')->get();
                        @endphp
                        <div class="relative" x-data="{ catOpen: false }" @click.away="catOpen = false">
                            <button @click="catOpen = !catOpen" 
                                    type="button" 
                                    class="px-2.5 xl:px-3.5 py-2 {{ request()->is('category*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0 flex items-center gap-1.5 focus:outline-none group">
                                <span>Categories</span>
                                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-white transition-transform duration-200" 
                                     :class="{ 'rotate-180': catOpen }" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="catOpen" 
                                 x-cloak 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="transform opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="transform opacity-0 scale-95 -translate-y-1"
                                 class="absolute left-0 mt-2 w-64 rounded-2xl bg-[#121622] border border-gray-800 shadow-2xl py-2 z-50 divide-y divide-gray-800/80">
                                
                                <div class="px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                    Explore Categories
                                </div>

                                <div class="py-1">
                                    @foreach($dropdownCategories as $cat)
                                        @php
                                            $catColor = match($cat->slug) {
                                                'sports' => 'bg-red-500',
                                                'news' => 'bg-blue-400',
                                                'christian-tv' => 'bg-amber-400',
                                                'islamic' => 'bg-emerald-400',
                                                'kids' => 'bg-pink-400',
                                                'education' => 'bg-cyan-400',
                                                'comedy' => 'bg-yellow-400',
                                                'entertainment' => 'bg-purple-400',
                                                'zoniva-originals' => 'bg-orange-500',
                                                default => 'bg-gray-400'
                                            };
                                        @endphp
                                        <a href="{{ route('categories.show', $cat->slug) }}" 
                                           class="flex items-center justify-between px-3.5 py-2 text-xs font-semibold {{ request()->is('category/' . $cat->slug) ? 'text-amber-400 bg-amber-500/10' : 'text-gray-300 hover:text-white hover:bg-gray-800/60' }} rounded-xl mx-1.5 transition group">
                                            <span class="flex items-center gap-2.5">
                                                <span class="w-2 h-2 rounded-full {{ $catColor }}"></span>
                                                <span>{{ $cat->name }}</span>
                                            </span>
                                            <span class="text-[10px] text-gray-500 group-hover:text-gray-300">Browse &rarr;</span>
                                        </a>
                                    @endforeach
                                </div>

                                <div class="p-2 space-y-1">
                                    <a href="{{ route('comedy.index') }}" class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-orange-500/10 border border-orange-500/20 text-[11px] font-bold text-orange-400 hover:bg-orange-500/20 transition">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-400 animate-pulse"></span>
                                            <span>Comedy TV (10 Channels)</span>
                                        </span>
                                        <span class="text-[10px] text-orange-300">Laugh &rarr;</span>
                                    </a>
                                    <a href="{{ route('nollywood.index') }}" class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] font-bold text-amber-400 hover:bg-amber-500/20 transition">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            <span>Nollywood Cinema (60 Channels)</span>
                                        </span>
                                        <span class="text-[10px] text-amber-300">Explore &rarr;</span>
                                    </a>
                                    <a href="{{ route('channels.index') }}" class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-gray-900 hover:bg-gray-800 text-[11px] font-bold text-gray-300 hover:text-white transition">
                                        <span>View All Channels</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('movies.index') }}" class="px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('movies.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            Movies
                        </a>
                        <a href="{{ route('nollywood.index') }}" class="px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('nollywood.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Nollywood
                        </a>
                        <a href="{{ route('comedy.index') }}" class="px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('comedy.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                            Comedy
                        </a>
                        <a href="{{ route('sports.index') }}" class="px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('sports.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            Sports
                        </a>
                        <a href="{{ route('gospel.index') }}" class="px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('gospel.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            Gospel TV
                        </a>
                        <a href="{{ route('islamic.index') }}" class="px-2.5 xl:px-3.5 py-2 {{ request()->routeIs('islamic.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }} transition shrink-0 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Islamic TV
                        </a>
                    </nav>
                </div>

                <!-- Search & Profile Controls -->
                <div class="flex items-center space-x-2 sm:space-x-3 xl:space-x-4 shrink-0">
                    <form action="{{ route('search') }}" method="GET" class="relative hidden sm:block w-36 md:w-44 lg:w-48 xl:w-56 focus-within:w-52 lg:focus-within:w-60 xl:focus-within:w-68 transition-all duration-200">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search channels..." 
                               class="w-full bg-[#151922] text-xs text-gray-200 placeholder-gray-500 pl-9 pr-3 py-2 rounded-full border border-gray-700/80 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </form>

                    <!-- Notifications Bell with Red Badge -->
                    <button type="button" class="relative p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-red-600 text-[10px] font-bold text-white rounded-full flex items-center justify-center">1</span>
                    </button>

                    <!-- WhatsApp Channel Pill -->
                    <a href="{{ route('wa.join') }}" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-lg transition">
                        <svg class="w-3.5 h-3.5 fill-current text-emerald-400" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.087-.179.181-.077.355.101.173.45 1.745 1.547 2.721 1.407 1.252 2.593 1.639 2.96 1.783.367.144.583.12.799-.129.217-.249.925-1.077 1.17-1.446.246-.369.491-.308.824-.184.332.124 2.107.994 2.468 1.173.361.179.602.268.69.418.086.15.086.87-.058 1.275z"/></svg>
                        WhatsApp
                    </a>

                    @guest
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-gray-300 hover:text-white px-2 sm:px-3 py-1.5 transition">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center text-xs font-bold text-black bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 px-3 sm:px-4 py-1.5 rounded-full shadow-md shadow-orange-500/20 transition">
                            Register
                        </a>
                    @else
                        <!-- Authenticated User Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" type="button" class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-800 transition focus:outline-none">
                                <div class="w-8 h-8 rounded-full ring-2 ring-amber-500/40 overflow-hidden bg-gray-800 flex items-center justify-center">
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" x-cloak 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-xl bg-[#121620] border border-gray-800 shadow-2xl py-2 z-50 text-xs">
                                <div class="px-4 py-2 border-b border-gray-800/80">
                                    <p class="font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</p>
                                </div>

                                <a href="{{ route('guide.personalized') }}" class="flex items-center gap-2 px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/60 transition">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                    My Personalised Guide
                                </a>

                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isContentManager())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/60 transition">
                                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Control Hub (Admin)
                                    </a>
                                @endif

                                <div class="border-t border-gray-800/80 my-1"></div>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 transition text-left">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile / Tablet Quick Navigation Bar -->
    <div class="lg:hidden bg-[#0c0e14] border-b border-gray-800/80 px-4 py-2.5 overflow-x-auto flex items-center gap-2 text-xs font-semibold whitespace-nowrap z-40 sticky top-20 shadow-md">
        <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('home') ? 'bg-amber-500 text-black font-bold' : 'bg-gray-900 text-gray-300 hover:text-white' }}">Home</a>
        <a href="{{ route('guide.index') }}" class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guide.*') ? 'bg-amber-500 text-black font-bold' : 'bg-gray-900 text-gray-300 hover:text-white' }}">Live TV</a>
        <a href="{{ route('channels.index') }}" class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('channels.*') ? 'bg-amber-500 text-black font-bold' : 'bg-gray-900 text-gray-300 hover:text-white' }}">Channels</a>

        <!-- Mobile Categories Dropdown -->
        <div class="relative" x-data="{ mCatOpen: false }" @click.away="mCatOpen = false">
            <button @click="mCatOpen = !mCatOpen" type="button" class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ request()->is('category*') ? 'bg-amber-500 text-black font-bold' : 'bg-gray-900 text-gray-300 hover:text-white' }}">
                <span>Categories</span>
                <svg class="w-3 h-3 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="mCatOpen" x-cloak 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute left-0 mt-2 w-56 rounded-2xl bg-[#121622] border border-gray-800 shadow-2xl py-2 z-50 divide-y divide-gray-800/80">
                <div class="py-1">
                    @foreach($dropdownCategories as $cat)
                        <a href="{{ route('categories.show', $cat->slug) }}" class="block px-3.5 py-2 text-xs {{ request()->is('category/' . $cat->slug) ? 'text-amber-400 font-bold bg-amber-500/10' : 'text-gray-300 hover:text-white hover:bg-gray-800/60' }} transition">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <a href="{{ route('movies.index') }}" class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('movies.*') ? 'bg-amber-500 text-black font-bold' : 'bg-gray-900 text-gray-300 hover:text-white' }}">Movies</a>
        <a href="{{ route('nollywood.index') }}" class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ request()->routeIs('nollywood.*') ? 'bg-amber-500 text-black font-bold' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            Nollywood
        </a>
        <a href="{{ route('comedy.index') }}" class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ request()->routeIs('comedy.*') ? 'bg-orange-500 text-black font-bold' : 'bg-orange-500/10 text-orange-400 border border-orange-500/30' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
            Comedy
        </a>
        <a href="{{ route('sports.index') }}" class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('sports.*') ? 'bg-red-600 text-white font-bold' : 'bg-gray-900 text-gray-300 hover:text-white' }}">Sports</a>
        <a href="{{ route('gospel.index') }}" class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ request()->routeIs('gospel.*') ? 'bg-amber-500 text-black font-bold' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            Gospel TV
        </a>
        <a href="{{ route('islamic.index') }}" class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ request()->routeIs('islamic.*') ? 'bg-emerald-500 text-black font-bold' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            Islamic TV
        </a>
    </div>

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
