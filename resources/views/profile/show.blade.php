@extends('layouts.app')

@section('title', 'Account Settings - Zonita TV')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="mb-8 border-b border-gray-800 pb-5">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Account Profile & Security</h1>
        <p class="text-xs sm:text-sm text-gray-400 mt-1">Manage your profile information, password, and viewing preferences.</p>
    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs sm:text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs sm:text-sm">
            <p class="font-bold text-white mb-1">Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- User Badge & Role Card -->
        <div class="md:col-span-1">
            <div class="bg-[#121622] border border-gray-800 rounded-2xl p-6 text-center shadow-lg">
                <div class="w-20 h-20 mx-auto rounded-full ring-4 ring-amber-500/30 overflow-hidden bg-gray-800 flex items-center justify-center mb-4">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                </div>
                <h3 class="text-base font-bold text-white truncate">{{ $user->name }}</h3>
                @if($user->username)
                    <p class="text-xs text-amber-400 font-mono">{{ $user->display_name }}</p>
                @endif
                <p class="text-xs text-gray-400 truncate mt-0.5">{{ $user->email }}</p>
                
                <div class="mt-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                        {{ $user->isSuperAdmin() ? 'bg-purple-500/15 text-purple-400 border border-purple-500/30' : ($user->isContentManager() ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' : ($user->isRightsReviewer() ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : ($user->isAdvertisingManager() ? 'bg-indigo-500/15 text-indigo-400 border border-indigo-500/30' : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'))) }}">
                        Role: {{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}
                    </span>
                </div>

                @if($user->canAccessAdmin())
                    <div class="mt-5">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs tracking-wide transition shadow">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Open Admin Hub</span>
                        </a>
                    </div>
                @endif
                
                <div class="mt-6 pt-6 border-t border-gray-800 text-[11px] text-gray-500">
                    Member since {{ $user->created_at->format('M Y') }}
                </div>
            </div>
        </div>

        <!-- Form Panels -->
        <div class="md:col-span-2 space-y-8">
            <!-- Profile Info Form -->
            <div class="bg-[#121622] border border-gray-800 rounded-2xl p-6 shadow-lg">
                <h2 class="text-lg font-bold text-white mb-4">Profile Information</h2>
                <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-300 mb-1.5">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition">
                    </div>

                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-300 mb-1.5">Username / Channel Handle</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-gray-500 text-xs">@</span>
                            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}"
                                   class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl pl-8 pr-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                                   placeholder="username">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-300 mb-1.5">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                               class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition">
                    </div>

                    <div>
                        <label for="avatar" class="block text-xs font-semibold text-gray-300 mb-1.5">Avatar Image URL (Optional)</label>
                        <input type="url" name="avatar" id="avatar" value="{{ old('avatar', $user->avatar) }}"
                               class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                               placeholder="https://example.com/avatar.jpg">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-bold text-xs tracking-wider uppercase transition shadow-md">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Update Form -->
            <div class="bg-[#121622] border border-gray-800 rounded-2xl p-6 shadow-lg">
                <h2 class="text-lg font-bold text-white mb-4">Change Password</h2>
                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-gray-300 mb-1.5">Current Password</label>
                        <input type="password" name="current_password" id="current_password" required
                               class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-300 mb-1.5">New Password (min 8 characters)</label>
                        <input type="password" name="password" id="password" required
                               class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-gray-300 mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-white font-bold text-xs tracking-wider uppercase transition border border-gray-700">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Viewing Preferences Form -->
            <div class="bg-[#121622] border border-gray-800 rounded-2xl p-6 shadow-lg">
                <h2 class="text-lg font-bold text-white mb-4">Viewing Preferences</h2>
                <form action="{{ route('profile.preferences') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="country_code" class="block text-xs font-semibold text-gray-300 mb-1.5">Primary Country (ISO)</label>
                        <input type="text" name="country_code" id="country_code" value="{{ old('country_code', $preferences->country_code ?? 'NGA') }}" maxlength="3"
                               class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition uppercase">
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <input type="checkbox" name="enable_recommendations" id="enable_recommendations" value="1" {{ ($preferences->enable_recommendations ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-[#0b0d13] border-gray-800 text-amber-500 focus:ring-0">
                        <label for="enable_recommendations" class="text-xs text-gray-300">Enable personalised recommendations and discovery</label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-white font-bold text-xs tracking-wider uppercase transition border border-gray-700">
                            Save Preferences
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone: Delete Account -->
            <div class="bg-red-950/20 border border-red-900/40 rounded-2xl p-6 shadow-lg">
                <h2 class="text-lg font-bold text-red-400 mb-1">Delete Account</h2>
                <p class="text-xs text-gray-400 mb-4">Once your account is deleted, your favourites and custom viewing guides will be permanently removed.</p>
                
                <form action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete your Zonita TV account? This cannot be undone.');" class="space-y-4">
                    @csrf
                    @method('DELETE')

                    <div>
                        <label for="confirm_password" class="block text-xs font-semibold text-gray-300 mb-1.5">Enter password to confirm</label>
                        <input type="password" name="confirm_password" id="confirm_password" required placeholder="Current password"
                               class="w-full bg-[#0b0d13] border border-red-900/40 focus:border-red-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-red-500 transition">
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs tracking-wider uppercase transition shadow-md">
                        Permanently Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
