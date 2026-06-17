@extends('layouts.app')

@section('title', 'Contoh Karya')
@section('meta_description', 'Lihat contoh-contoh karya commission yang telah kami kerjakan.')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-black text-void-white mb-3">
                Contoh Karya
            </h1>
            <p class="text-void-gray max-w-2xl mx-auto">
                Berikut adalah beberapa contoh karya commission yang telah kami selesaikan.
                Setiap karya dibuat dengan detail dan dedikasi tinggi.
            </p>
        </div>

        {{-- Filter Kategori --}}
        <div class="flex flex-wrap justify-center gap-2 mb-10">
            <a href="{{ route('portfolio') }}" 
               class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ !request('category') ? 'bg-void-accent text-white' : 'bg-void-card border border-void-border text-void-gray hover:text-void-light hover:border-void-muted' }}">
                Semua
            </a>
            @foreach($categories as $category)
                <a href="{{ route('portfolio', ['category' => $category->slug]) }}" 
                   class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                          {{ request('category') == $category->slug ? 'bg-void-accent text-white' : 'bg-void-card border border-void-border text-void-gray hover:text-void-light hover:border-void-muted' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        {{-- Grid Karya --}}
        @if($works->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($works as $work)
                    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden hover:border-void-muted transition-all duration-300 group">
                        {{-- Gambar --}}
                        <div class="aspect-[4/3] overflow-hidden bg-void-dark relative">
                            @if($work->image_url)
                                <img src="{{ $work->image_url }}" 
                                     alt="{{ $work->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-void-muted">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            
                            {{-- Badge Kategori --}}
                            @if($work->category)
                                <span class="absolute top-3 left-3 text-[10px] font-bold bg-black/70 backdrop-blur-sm text-white px-3 py-1 rounded-full">
                                    {{ $work->category->name }}
                                </span>
                            @endif
                        </div>
                        
                        {{-- Info --}}
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-void-white mb-1 line-clamp-1">{{ $work->title }}</h3>
                            <p class="text-sm text-void-gray line-clamp-2 mb-3">{{ $work->description }}</p>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($work->user)
                                        <img src="{{ $work->user->avatar_url ?? asset('images/default-avatar.png') }}" 
                                             class="w-6 h-6 rounded-full object-cover border border-void-border">
                                        <span class="text-xs text-void-gray">{{ $work->user->name }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('portfolio.show', $work->slug) }}" 
                                   class="text-xs font-semibold text-void-accent hover:text-void-accent/80 transition-colors">
                                    Lihat Detail →
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Pagination --}}
            @if($works->hasPages())
                <div class="mt-8">
                    {{ $works->links('components.pagination') }}
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <svg class="w-20 h-20 text-void-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-void-gray">Belum ada contoh karya yang tersedia.</p>
                <p class="text-xs text-void-muted mt-1">Pantau terus untuk karya terbaru.</p>
            </div>
        @endif
    </div>
</div>
@endsection