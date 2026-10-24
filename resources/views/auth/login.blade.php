@extends('layouts.app')

@section('title', 'Sign In - Zonita TV')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <!-- Card Container -->
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-8 shadow-2xl backdrop-blur-xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo/zonitatvlogo2.png') }}" alt="Zonita TV" class="h-10 mx-auto mb-3 object-contain">
                <h1 class="text-2xl font-black text-white tracking-tight">Welcome Back</h1>
                <p class="text-xs text-gray-400 mt-1">Sign in to access your personalised channels, favorites, and guide</p>
            </div>

            <!-- Google Sign-In Button -->
            <a href="{{ route('auth.google') }}" class="w-full py-3 px-4 rounded-xl bg-white hover:bg-gray-100 text-gray-900 font-semibold text-xs flex items-center justify-center gap-3 transition shadow-md group">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Continue with Google</span>
            </a>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-800"></div>
                </div>
                <div class="relative flex justify-center text-[11px] uppercase tracking-wider">
                    <span class="bg-[#121620] px-3 text-gray-500 font-medium">Or continue with email</span>
                </div>
            </div>

            <!-- Email / Password Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-300 mb-1.5">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                           placeholder="you@example.com">
                    @error('email')
                        <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-gray-300">Password</label>
                    </div>
                    <div class="relative flex items-center">
                        <input :type="show ? 'text' : 'password'" name="password" id="password" required
                               class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl pl-4 pr-11 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                               placeholder="••••••••">
                        <button type="button" 
                                @click="show = !show" 
                                :aria-label="show ? 'Hide password' : 'Show password'"
                                :title="show ? 'Hide password' : 'Show password'"
                                class="absolute right-3 p-1 text-gray-400 hover:text-white focus:outline-none transition">
                            <!-- Eye icon (Show) -->
                            <svg x-show="!show" class="w-4 h-4 text-gray-400 hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye-slash icon (Hide) -->
                            <svg x-show="show" x-cloak class="w-4 h-4 text-amber-400 hover:text-amber-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-gray-400 hover:text-gray-300">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#0b0d13] border-gray-800 text-amber-500 focus:ring-0">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-black font-bold text-xs tracking-wider uppercase transition shadow-lg shadow-orange-600/20 active:scale-[0.99] mt-2">
                    Sign In
                </button>
            </form>

            <!-- Register Link Footer -->
            <div class="text-center mt-6 pt-6 border-t border-gray-800/60 text-xs text-gray-400">
                Don't have an account yet?
                <a href="{{ route('register') }}" class="text-amber-400 hover:text-amber-300 font-semibold ml-1">
                    Create an account
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
