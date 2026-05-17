@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-24">
    <div class="w-full max-w-md animate-slide-up">

        {{-- Header --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-baseline gap-2 mb-6 group">
                <span class="text-3xl font-black tracking-widest text-void-accent">VOID</span>
                <span class="text-sm font-medium tracking-[0.3em] text-void-gray uppercase">Supply</span>
            </a>
            <h1 class="text-2xl font-bold text-void-white">Buat akun baru</h1>
            <p class="text-sm text-void-gray mt-2">Sudah punya akun?
                <a href="{{ route('login') }}" class="text-void-accent hover:underline font-medium">Login di sini</a>
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-8">

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google') }}"
               class="flex items-center justify-center gap-3 w-full px-4 py-3 bg-void-dark border border-void-border rounded-xl text-sm font-medium text-void-light hover:border-void-muted hover:bg-void-muted/30 transition-all duration-200">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>Daftar dengan Google</span>
            </a>

            {{-- Divider --}}
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-void-border"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-void-card px-4 text-xs text-void-gray">atau daftar dengan email</span>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-xs font-semibold text-void-light tracking-wider uppercase mb-2">
                        Nama Lengkap
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        placeholder="Nama kamu"
                        class="w-full bg-void-dark border {{ $errors->has('name') ? 'border-red-500/50' : 'border-void-border' }}
                               text-void-white placeholder-void-gray rounded-xl px-4 py-3 text-sm
                               focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted transition-colors"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-semibold text-void-light tracking-wider uppercase mb-2">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="kamu@email.com"
                        class="w-full bg-void-dark border {{ $errors->has('email') ? 'border-red-500/50' : 'border-void-border' }}
                               text-void-white placeholder-void-gray rounded-xl px-4 py-3 text-sm
                               focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted transition-colors"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-xs font-semibold text-void-light tracking-wider uppercase mb-2">
                        Password
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            class="w-full bg-void-dark border {{ $errors->has('password') ? 'border-red-500/50' : 'border-void-border' }}
                                   text-void-white placeholder-void-gray rounded-xl px-4 py-3 pr-12 text-sm
                                   focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted transition-colors"
                        >
                        <button type="button" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-void-gray hover:text-void-accent transition-colors p-1">
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-void-light tracking-wider uppercase mb-2">
                        Konfirmasi Password
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="Ulangi password"
                            class="w-full bg-void-dark border border-void-border
                                   text-void-white placeholder-void-gray rounded-xl px-4 py-3 pr-12 text-sm
                                   focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted transition-colors"
                        >
                        <button type="button" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-void-gray hover:text-void-accent transition-colors p-1">
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Terms --}}
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="terms" required
                           class="w-4 h-4 mt-0.5 rounded border-void-border bg-void-dark text-void-accent
                                  focus:ring-0 focus:ring-offset-0 cursor-pointer shrink-0">
                    <label for="terms" class="text-sm text-void-gray cursor-pointer leading-relaxed">
                        Saya setuju dengan
                        <a href="#" class="text-void-accent hover:underline">Syarat & Ketentuan</a>
                        dan
                        <a href="#" class="text-void-accent hover:underline">Kebijakan Privasi</a>
                        VOID Supply.
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-white text-black font-bold py-3 rounded-xl hover:bg-void-light
                               transition-all duration-200 tracking-wide text-sm mt-2
                               active:scale-[0.98]">
                    Buat Akun
                </button>
            </form>
        </div>

        {{-- Back to home --}}
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-xs text-void-gray hover:text-void-accent transition-colors">
                ← Kembali ke toko
            </a>
        </div>
    </div>
</div>
@endsection