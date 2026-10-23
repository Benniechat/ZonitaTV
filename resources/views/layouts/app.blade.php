<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zonita TV - Discover More. Watch Smarter.')</title>
    <meta name="description" content="@yield('meta_description', 'Discover verified television channels, live sports, breaking news, Christian worship, and original productions through one organised digital platform.')">

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
                <div class="flex items-center space-x-6 xl:space-x-10">
                    <a href="{{ route('home') }}" class="flex items-center group">
                        <img src="{{ asset('images/logo/zonitatvlogo2.png') }}" alt="Zonita TV" class="h-10 sm:h-11 w-auto object-contain transition-transform group-hover:scale-105">
                    </a>

                    <!-- Navigation Links with Amber active pill indicator -->
                    <nav class="hidden lg:flex items-center space-x-1 text-sm font-medium">
                        <a href="{{ route('home') }}" class="relative px-3.5 py-2 text-white font-semibold transition group">
                            Home
                            <span class="absolute bottom-0 left-3 right-3 h-0.5 bg-amber-400 rounded-full"></span>
                        </a>
                        <a href="{{ route('guide.index') }}" class="px-3.5 py-2 text-gray-400 hover:text-white transition">Live TV</a>
                        <a href="{{ route('channels.index') }}" class="px-3.5 py-2 text-gray-400 hover:text-white transition">Channels</a>
                        <a href="{{ route('categories.show', 'sports') }}" class="px-3.5 py-2 text-gray-400 hover:text-white transition">Sports</a>
                        <a href="{{ route('categories.show', 'news') }}" class="px-3.5 py-2 text-gray-400 hover:text-white transition">News</a>
                        <a href="{{ route('categories.show', 'christian-tv') }}" class="px-3.5 py-2 text-gray-400 hover:text-white transition">Christian</a>
                        <a href="{{ route('categories.show', 'education') }}" class="px-3.5 py-2 text-gray-400 hover:text-white transition">Education</a>
                        <a href="{{ route('categories.show', 'entertainment') }}" class="px-3.5 py-2 text-gray-400 hover:text-white transition">Entertainment</a>
                    </nav>
                </div>

                <!-- Search & Profile Controls -->
                <div class="flex items-center space-x-4">
                    <form action="{{ route('search') }}" method="GET" class="relative hidden sm:block w-64 xl:w-80">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search channels, shows, topics..." 
                               class="w-full bg-[#151922] text-xs text-gray-200 placeholder-gray-500 pl-10 pr-4 py-2.5 rounded-full border border-gray-700/80 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </form>

                    <!-- Notifications Bell with Red Badge -->
                    <button type="button" class="relative p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-red-600 text-[10px] font-bold text-white rounded-full flex items-center justify-center">1</span>
                    </button>

                    <!-- User Profile Avatar -->
                    <div class="flex items-center space-x-2">
                        <div class="w-9 h-9 rounded-full ring-2 ring-amber-500/40 overflow-hidden bg-gray-800 flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                    </div>

                    <!-- WhatsApp Channel Pill -->
                    <a href="{{ route('wa.join') }}" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-lg transition">
                        <svg class="w-3.5 h-3.5 fill-current text-emerald-400" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.087-.179.181-.077.355.101.173.45 1.745 1.547 2.721 1.407 1.252 2.593 1.639 2.96 1.783.367.144.583.12.799-.129.217-.249.925-1.077 1.17-1.446.246-.369.491-.308.824-.184.332.124 2.107.994 2.468 1.173.361.179.602.268.69.418.086.15.086.87-.058 1.275z"/></svg>
                        WhatsApp
                    </a>

                    <!-- Admin Portal Pill -->
                    <a href="{{ route('admin.dashboard') }}" class="hidden md:inline-flex items-center px-3 py-1.5 text-xs text-gray-400 hover:text-white border border-gray-800 rounded-lg hover:border-gray-700 transition">
                        Admin
                    </a>
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

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#080a0e] border-t border-gray-800/60 mt-20 pt-12 pb-8 text-gray-400 text-xs">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-gray-800/60">
                <div class="flex items-center space-x-4">
                    <img src="{{ asset('images/logo/zonitatvlogo2.png') }}" alt="Zonita TV" class="h-9 w-auto">
                    <span class="text-gray-500">Discover More. Watch Smarter.</span>
                </div>
                <div class="flex items-center space-x-6 text-gray-400">
                    <a href="{{ route('pages.about') }}" class="hover:text-white transition">About</a>
                    <a href="{{ route('pages.privacy') }}" class="hover:text-white transition">Privacy Policy</a>
                    <a href="{{ route('pages.terms') }}" class="hover:text-white transition">Terms</a>
                    <a href="{{ route('guide.index') }}" class="hover:text-white transition">TV Guide</a>
                    <a href="{{ route('channels.index') }}" class="hover:text-white transition">Channels</a>
                    <a href="{{ route('wa.join') }}" target="_blank" rel="noopener noreferrer" class="text-emerald-400 hover:text-emerald-300 font-medium transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.087-.179.181-.077.355.101.173.45 1.745 1.547 2.721 1.407 1.252 2.593 1.639 2.96 1.783.367.144.583.12.799-.129.217-.249.925-1.077 1.17-1.446.246-.369.491-.308.824-.184.332.124 2.107.994 2.468 1.173.361.179.602.268.69.418.086.15.086.87-.058 1.275z"/></svg>
                        WhatsApp Channel
                    </a>
                </div>
            </div>
            <div class="pt-6 text-center md:text-left text-gray-500 text-[11px] flex flex-col md:flex-row items-center justify-between gap-2">
                <p>&copy; {{ date('Y') }} Zonita TV. All rights reserved. Content discovery platform indexing authorised broadcasts.</p>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Verified Feeds</span>
                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">EPG Synced</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
