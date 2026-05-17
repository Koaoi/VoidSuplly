@extends('layouts.app')
@section('title','Wishlist Saya')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">— Saved Items</p>
            <h1 class="text-3xl font-black text-void-accent">Wishlist Saya</h1>
            <p class="text-void-gray text-sm mt-1">
                <span class="text-void-light font-semibold">{{ $wishlists->total() }}</span> produk tersimpan
            </p>
        </div>

        @if($wishlists->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-5">
                @foreach($wishlists as $item)
                    @if($item->product)
                        @include('components.product-card', ['product' => $item->product])
                    @endif
                @endforeach
            </div>

            <div class="mt-10">
                {{ $wishlists->links('components.pagination') }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-24 h-24 rounded-2xl bg-void-card border border-void-border
                            flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-void-white mb-2">Wishlist masih kosong</h2>
                <p class="text-sm text-void-gray mb-8 max-w-xs leading-relaxed">
                    Simpan produk favoritmu dengan klik ikon hati di product card.
                </p>
                <a href="{{ route('products.index') }}" class="btn-primary">Jelajahi Produk</a>
            </div>
        @endif
    </div>
</div>
@endsection
