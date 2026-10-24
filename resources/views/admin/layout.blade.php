<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - Zoniva TV')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#0a0c10] text-gray-200 antialiased min-h-screen flex flex-col">

    <!-- Admin Top Nav -->
    <header class="bg-[#10131a] border-b border-gray-800 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                <img src="{{ asset('images/logo/zonivatvLogo.png') }}" alt="Zoniva TV" class="h-8 w-auto">
                <span class="text-xs px-2 py-0.5 rounded bg-red-600/20 text-red-400 font-bold uppercase tracking-wider">Control Hub</span>
            </a>
        </div>

        <div class="flex items-center space-x-4 text-xs">
            <a href="{{ route('home') }}" target="_blank" class="text-gray-400 hover:text-white flex items-center gap-1 transition">
                <span>View Public Site</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            <span class="text-gray-600">|</span>
            <span class="text-gray-300 font-medium">Administrator</span>
        </div>
    </header>

    <div class="flex-grow flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#0d1017] border-r border-gray-800 p-6 hidden md:block space-y-6">
            <div>
                <h4 class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-3">Management</h4>
                <nav class="space-y-1 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/60 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white font-semibold' : '' }}">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.channels.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/60 {{ request()->routeIs('admin.channels.*') ? 'bg-gray-800 text-white font-semibold' : '' }}">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Channel Catalogue
                    </a>
                    <a href="{{ route('admin.broadcasting.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/60 {{ request()->routeIs('admin.broadcasting.*') ? 'bg-red-950/40 border border-red-500/30 text-white font-semibold' : '' }}">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Virtual Broadcasting
                    </a>
                    <a href="{{ route('admin.rights.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/60 {{ request()->routeIs('admin.rights.*') ? 'bg-gray-800 text-white font-semibold' : '' }}">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Rights Governance
                    </a>
                    <a href="{{ route('admin.complaints.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/60 {{ request()->routeIs('admin.complaints.*') ? 'bg-red-950/40 border border-red-500/30 text-white font-semibold' : '' }}">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Content Complaints
                    </a>
                    <a href="{{ route('admin.diagnostics.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/60 {{ request()->routeIs('admin.diagnostics.*') ? 'bg-gray-800 text-white font-semibold' : '' }}">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Stream Health
                    </a>
                    <a href="{{ route('admin.ingest.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/60 {{ request()->routeIs('admin.ingest.*') ? 'bg-gray-800 text-white font-semibold' : '' }}">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                        IPTV & EPG Ingest
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Panel -->
        <main class="flex-grow p-8 bg-[#0a0c10]">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            @yield('admin_content')
        </main>
    </div>

</body>
</html>
