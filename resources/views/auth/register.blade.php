@extends('layouts.app')

@section('title', 'Create Account - Zonita TV')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <!-- Card Container -->
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-8 shadow-2xl backdrop-blur-xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo/zonitatvlogo2.png') }}" alt="Zonita TV" class="h-10 mx-auto mb-3 object-contain">
                <h1 class="text-2xl font-black text-white tracking-tight">Create Free Account</h1>
                <p class="text-xs text-gray-400 mt-1">Start watching 24/7 channels, curate favourites, and explore the TV guide</p>
            </div>

            <!-- Google Sign-Up Button -->
            <a href="{{ route('auth.google') }}" class="w-full py-3 px-4 rounded-xl bg-white hover:bg-gray-100 text-gray-900 font-semibold text-xs flex items-center justify-center gap-3 transition shadow-md group">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Sign up with Google</span>
            </a>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-800"></div>
                </div>
                <div class="relative flex justify-center text-[11px] uppercase tracking-wider">
                    <span class="bg-[#121620] px-3 text-gray-500 font-medium">Or register with email</span>
                </div>
            </div>

            <!-- Email Registration Form -->
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-gray-300 mb-1.5">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                           class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                           placeholder="Alex Johnson">
                    @error('name')
                        <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-300 mb-1.5">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                           placeholder="you@example.com">
                    @error('email')
                        <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-300 mb-1.5">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                           placeholder="At least 8 characters">
                    @error('password')
                        <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-300 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                           placeholder="Repeat password">
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-black font-bold text-xs tracking-wider uppercase transition shadow-lg shadow-orange-600/20 active:scale-[0.99] mt-2">
                    Create Account
                </button>
            </form>

            <!-- Login Link Footer -->
            <div class="text-center mt-6 pt-6 border-t border-gray-800/60 text-xs text-gray-400">
                Already have an account?
                <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300 font-semibold ml-1">
                    Sign in
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
