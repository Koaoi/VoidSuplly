@extends('layouts.app')

@section('title', 'Profile Saya')
@section('page-title', 'Profile Saya')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">— Account</p>
            <h1 class="text-3xl font-black text-void-accent">Profile Saya</h1>
            <p class="text-void-gray text-sm mt-2">Kelola informasi akun Anda</p>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                
                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Avatar & Nama --}}
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-24 h-24 rounded-full overflow-hidden bg-void-dark mb-3 border-2 border-void-border">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    </div>
                    <h2 class="text-lg font-bold text-void-white">{{ $user->name }}</h2>
                    <p class="text-xs text-void-gray mt-0.5">{{ $user->email }}</p>
                </div>

                {{-- Informasi Akun --}}
                <div class="space-y-4 mb-6">
                    <div class="flex items-center justify-between py-2 border-b border-void-border">
                        <span class="text-xs text-void-gray">Nama Lengkap</span>
                        <span class="text-sm text-void-white font-medium">{{ $user->name }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-void-border">
                        <span class="text-xs text-void-gray">Email</span>
                        <span class="text-sm text-void-white font-medium">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-void-border">
                        <span class="text-xs text-void-gray">Nomor Telepon</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-void-white font-medium">
                                {{ $user->phone ? ($user->formatted_phone ?? $user->phone) : 'Belum diisi' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-void-border">
                        <span class="text-xs text-void-gray">Bergabung Sejak</span>
                        <span class="text-sm text-void-white font-medium">{{ $user->created_at->format('d F Y') }}</span>
                    </div>
                </div>

                {{-- Tombol Edit Profile --}}
                <div class="flex justify-center gap-3 pt-4 border-t border-void-border">
                    <a href="{{ route('profile.edit') }}" 
                       class="btn-primary inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection