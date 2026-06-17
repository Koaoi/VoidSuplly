@extends('layouts.app')

@section('title', $work->title)
@section('meta_description', Str::limit($work->description, 150))

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <nav class="flex text-xs text-void-gray">
                <a href="{{ route('home') }}" class="hover:text-void-accent">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('portfolio') }}" class="hover:text-void-accent">Contoh Karya</a>
                <span class="mx-2">/</span>
                <span class="text-void-light">{{ $work->title }}</span>
            </nav>
        </div>
        
        {{-- Image --}}
        <div class="rounded-2xl overflow-hidden bg-void-card border border-void-border mb-8">
            @if($work->image_url)
                <img src="{{ $work->image_url }}" 
                     alt="{{ $work->title }}"
                     class="w-full max-h-[500px] object-cover">
            @else
                <div class="w-full h-80 flex items-center justify-center text-void-muted">
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </div>
        
        {{-- Info --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-6 md:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-void-white">{{ $work->title }}</h1>
                    @if($work->category)
                        <span class="inline-block mt-2 text-xs font-bold bg-void-accent/20 text-void-accent px-3 py-1 rounded-full">
                            {{ $work->category->name }}
                        </span>
                    @endif
                </div>
                @if($work->user)
                    <div class="flex items-center gap-3 bg-void-dark/50 px-4 py-2 rounded-xl">
                        <img src="{{ $work->user->avatar_url ?? asset('images/default-avatar.png') }}" 
                             class="w-8 h-8 rounded-full object-cover border border-void-border">
                        <div>
                            <p class="text-xs text-void-gray">Dibuat oleh</p>
                            <p class="text-sm font-semibold text-void-white">{{ $work->user->name }}</p>
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- Description --}}
            <div class="prose prose-invert max-w-none">
                <p class="text-void-gray leading-relaxed">{{ $work->description }}</p>
            </div>
            
            {{-- Additional Info --}}
            @if($work->details)
                <div class="mt-6 pt-6 border-t border-void-border">
                    <h3 class="text-sm font-bold text-void-white uppercase tracking-wider mb-3">Detail</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach(json_decode($work->details, true) ?? [] as $key => $value)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-void-gray">{{ $key }}:</span>
                                <span class="text-void-light">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Tombol Kembali --}}
            <div class="mt-6 pt-6 border-t border-void-border">
                <a href="{{ route('portfolio') }}" 
                   class="inline-flex items-center gap-2 text-sm text-void-gray hover:text-void-accent transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Contoh Karya
                </a>
            </div>
        </div>
    </div>
</div>
@endsection