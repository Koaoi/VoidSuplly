@extends('layouts.app')

@section('title', 'VOID Supply — Limited Drop & Streetwear')
@section('meta_description', 'VOID Supply — Limited fashion drop, streetwear premium, dan custom commission. Eksklusif, selalu terbatas.')

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     HERO SECTION
════════════════════════════════════════════════════════════ --}}
<section class="relative min-h-screen flex items-center overflow-hidden">

    {{-- Background pattern grid --}}
    <div class="absolute inset-0 opacity-[0.03]"
         style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px);
                background-size: 60px 60px;">
    </div>

    {{-- Vignette corners --}}
    <div class="absolute inset-0 bg-gradient-to-br from-void-black via-transparent to-void-black opacity-80 pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 w-full">
        <div class="max-w-4xl">

            {{-- Eyebrow --}}
            <div class="flex items-center gap-3 mb-8 animate-fade-in">
                <div class="flex items-center gap-2 bg-void-card border border-void-border rounded-full px-4 py-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse-slow"></span>
                    <span class="text-[11px] font-semibold tracking-[0.25em] text-void-gray uppercase">
                        Limited Drop Active
                    </span>
                </div>
            </div>

            {{-- Main Headline --}}
            <h1 class="animate-slide-up" style="animation-delay: 0.1s">
                <span class="block text-[clamp(3.5rem,10vw,8rem)] font-black leading-none tracking-tighter text-void-accent">
                    VOID
                </span>
                <span class="block text-[clamp(3.5rem,10vw,8rem)] font-black leading-none tracking-tighter
                             text-transparent bg-clip-text"
                      style="background-image: linear-gradient(135deg, #888 0%, #444 100%);">
                    SUPPLY
                </span>
            </h1>

            {{-- Sub headline --}}
            <p class="mt-6 text-lg text-void-gray max-w-xl leading-relaxed animate-slide-up"
               style="animation-delay: 0.2s">
                Fashion streetwear premium. Limited edition drops, custom commission,
                dan desain eksklusif yang tidak akan kamu temukan di tempat lain.
            </p>

            {{-- CTA Buttons --}}
            <div class="mt-10 flex flex-wrap gap-4 animate-slide-up" style="animation-delay: 0.3s">
                <a href="{{ route('products.index') }}"
                   class="btn-primary flex items-center gap-2 text-base px-8 py-3.5">
                    Shop Now
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="{{ route('products.index', ['limited' => 1]) }}"
                   class="btn-secondary flex items-center gap-2 text-base px-8 py-3.5">
                    Lihat Drops
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
            </div>

            {{-- Stats --}}
            <div class="mt-16 flex flex-wrap gap-8 animate-fade-in" style="animation-delay: 0.5s">
                @foreach([['label' => 'Limited Pieces', 'value' => '< 100'], ['label' => 'Happy Customers', 'value' => '2K+'], ['label' => 'Drops Released', 'value' => '12']] as $stat)
                    <div>
                        <p class="text-2xl font-black text-void-accent">{{ $stat['value'] }}</p>
                        <p class="text-xs text-void-gray tracking-widest uppercase mt-0.5">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce">
        <span class="text-[9px] tracking-[0.3em] text-void-muted uppercase">Scroll</span>
        <svg class="w-4 h-4 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     MARQUEE — Running text brand
════════════════════════════════════════════════════════════ --}}
<div class="border-y border-void-border bg-void-dark py-4 overflow-hidden">
    <div class="flex animate-[marquee_20s_linear_infinite] whitespace-nowrap">
        @foreach(range(1, 6) as $i)
            <span class="flex items-center gap-6 px-6 text-xs font-bold tracking-[0.3em] text-void-muted uppercase">
                VOID SUPPLY
                <span class="w-1 h-1 rounded-full bg-void-muted"></span>
                LIMITED DROP
                <span class="w-1 h-1 rounded-full bg-void-muted"></span>
                STREETWEAR
                <span class="w-1 h-1 rounded-full bg-void-muted"></span>
                CUSTOM COMMISSION
                <span class="w-1 h-1 rounded-full bg-void-muted"></span>
            </span>
        @endforeach
    </div>
</div>

@push('styles')
<style>
    @keyframes marquee {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }
</style>
@endpush

{{-- ═══════════════════════════════════════════════════════════
     UPCOMING DROPS — Countdown Section
════════════════════════════════════════════════════════════ --}}
@if($upcomingDrops->count() > 0)
<section id="drops" class="py-24 border-b border-void-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-3">
                    — Upcoming
                </p>
                <h2 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">
                    Limited Drops
                </h2>
                <p class="text-void-gray text-sm mt-2">Jangan sampai kehabisan. Stok sangat terbatas.</p>
            </div>
            <a href="{{ route('products.index', ['status' => 'coming_soon']) }}" 
               class="hidden sm:flex items-center gap-2 text-sm text-void-gray hover:text-void-accent transition-colors">
                Semua drops
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        {{-- Drop Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach($upcomingDrops as $index => $drop)
                <div class="relative group bg-void-card border border-void-border rounded-2xl overflow-hidden
                            hover:border-void-muted transition-all duration-300
                            {{ $index === 0 ? 'lg:col-span-2' : '' }}">

                    {{-- Top: Image --}}
                    <div class="{{ $index === 0 ? 'aspect-[16/9]' : 'aspect-square' }} relative overflow-hidden bg-void-darker">
                        <img src="{{ $drop->primary_image_url }}"
                             alt="{{ $drop->name }}"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                             loading="lazy">

                        {{-- Dark overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-void-card via-transparent to-transparent"></div>

                        {{-- Limited badge --}}
                        <div class="absolute top-4 left-4">
                            <span class="badge-limited">Limited Drop</span>
                        </div>
                    </div>

                    {{-- Bottom: Info + Countdown --}}
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-void-white mb-1">{{ $drop->name }}</h3>
                        <p class="text-sm text-void-gray mb-4 line-clamp-2">{{ $drop->description }}</p>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            {{-- Countdown --}}
                            <div>
                                <p class="text-[9px] font-bold tracking-[0.25em] text-void-gray uppercase mb-3">
                                    Drop dalam
                                </p>
                                @include('components.countdown', [
                                    'releaseDate' => $drop->release_date,
                                    'id'          => 'drop-' . $drop->id,
                                ])
                            </div>

                            {{-- Price + Notify --}}
                            <div class="text-right">
                                <p class="text-2xl font-black text-void-accent">{{ $drop->formatted_price }}</p>
                                <button type="button"
                                    x-data="{ loading: false, notified: false }"
                                    @click.prevent="
                                        if (loading || notified) return;
                                        loading = true;
                                        fetch('{{ route('product.notify', $drop->id) }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ product_id: {{ $drop->id }} })
                                        })
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.success) {
                                                notified = true;
                                                if (window.showNotification) window.showNotification(data.message, 'success');
                                                else alert(data.message);
                                            } else {
                                                if (window.showNotification) window.showNotification(data.message || 'Gagal', 'error');
                                                else alert(data.message || 'Gagal');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            if (window.showNotification) window.showNotification('Terjadi kesalahan', 'error');
                                            else alert('Terjadi kesalahan');
                                        })
                                        .finally(() => loading = false);
                                    "
                                    :disabled="loading || notified"
                                    class="mt-2 text-xs font-semibold border border-void-border text-void-light
                                           px-4 py-2 rounded-lg hover:border-void-accent hover:text-void-accent
                                           transition-colors tracking-wide disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!loading && !notified">🔔 Notifikasi Saya</span>
                                    <span x-show="!loading && notified">✅ Terdaftar</span>
                                    <span x-show="loading" class="flex items-center justify-center gap-1">
                                        <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        Memproses...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════
     CATEGORIES
════════════════════════════════════════════════════════════ --}}
@if($categories->count() > 0)
<section class="py-24 border-b border-void-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-12">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-3">— Browse</p>
            <h2 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">Kategori</h2>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                   class="group relative aspect-square bg-void-card border border-void-border rounded-2xl
                          overflow-hidden hover:border-void-muted transition-all duration-300">

                    {{-- BG --}}
                    <div class="absolute inset-0 bg-void-dark group-hover:bg-void-muted/20 transition-colors duration-300"></div>

                    {{-- Letter mark jika tidak ada gambar --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                        @if($category->image)
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                                 class="w-16 h-16 object-cover rounded-xl mb-3 grayscale group-hover:grayscale-0 transition-all duration-300">
                        @else
                            <div class="w-14 h-14 rounded-2xl bg-void-muted flex items-center justify-center mb-3
                                        group-hover:bg-void-border transition-colors duration-300">
                                <span class="text-2xl font-black text-void-gray group-hover:text-void-light transition-colors">
                                    {{ strtoupper(substr($category->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif

                        <h3 class="text-sm font-bold text-void-light group-hover:text-void-accent
                                   transition-colors text-center tracking-wide">
                            {{ $category->name }}
                        </h3>
                        <p class="text-[10px] text-void-gray mt-0.5">
                            {{ $category->products_count }} produk
                        </p>
                    </div>

                    {{-- Arrow --}}
                    <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <svg class="w-4 h-4 text-void-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════
     FEATURED PRODUCTS
════════════════════════════════════════════════════════════ --}}
@if($featuredProducts->count() > 0)
<section class="py-24 border-b border-void-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-3">— New Arrival</p>
                <h2 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">
                    Featured Products
                </h2>
                <p class="text-void-gray text-sm mt-2">Pilihan terbaru yang siap dipesan sekarang.</p>
            </div>
            <a href="{{ route('products.index') }}" 
               class="hidden sm:flex items-center gap-2 text-sm text-void-gray hover:text-void-accent transition-colors">
                Semua produk
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        {{-- Product Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($featuredProducts as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>

        {{-- Load More CTA --}}
        <div class="text-center mt-12">
            <a href="{{ route('products.index') }}" class="btn-secondary inline-flex items-center gap-2">
                Lihat Semua Produk
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════
     PREORDER SECTION
════════════════════════════════════════════════════════════ --}}
@if($preorderProducts->count() > 0)
<section class="py-24 border-b border-void-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-3">— Open Now</p>
                <h2 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">
                    Preorder
                </h2>
                <p class="text-void-gray text-sm mt-2">Pesan sekarang sebelum kehabisan.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($preorderProducts as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════
     COMMISSION CTA
════════════════════════════════════════════════════════════ --}}
<section class="py-24 border-b border-void-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative bg-void-card border border-void-border rounded-3xl overflow-hidden p-10 sm:p-16">

            {{-- Background grid subtle --}}
            <div class="absolute inset-0 opacity-[0.02]"
                 style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 40px 40px;">
            </div>

            <div class="relative max-w-2xl">
                <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-4">
                    — Custom Order
                </p>
                <h2 class="text-3xl sm:text-5xl font-black text-void-accent tracking-tight leading-tight">
                    Desain Sendiri.<br>
                    <span class="text-void-gray">Kami Wujudkan.</span>
                </h2>
                <p class="text-void-gray text-base mt-5 leading-relaxed max-w-lg">
                    Punya ide desain yang unik? Submit commission request-mu dan tim VOID Supply
                    akan mengerjakan custom piece eksklusif untukmu.
                </p>

                <div class="flex flex-wrap gap-4 mt-8">
                    <a href="{{ route('commission.create') }}" class="btn-primary flex items-center gap-2">
                        Submit Commission
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    <a href="{{ route('commission.index') }}" class="btn-secondary">
                        Lihat Contoh Karya
                    </a>
                </div>

                {{-- Points --}}
                <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach([['icon' => '01', 'title' => 'Submit Request', 'desc' => 'Isi form dengan detail desain dan referensi'], ['icon' => '02', 'title' => 'Review & Quote', 'desc' => 'Tim kami review dan beri estimasi harga'], ['icon' => '03', 'title' => 'Produksi', 'desc' => 'Produk dibuat dan dikirim ke alamatmu']] as $step)
                        <div class="flex items-start gap-3">
                            <span class="text-xs font-black text-void-muted shrink-0 mt-0.5">{{ $step['icon'] }}</span>
                            <div>
                                <p class="text-sm font-semibold text-void-light">{{ $step['title'] }}</p>
                                <p class="text-xs text-void-gray mt-0.5 leading-relaxed">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     WHY VOID — Value Props
════════════════════════════════════════════════════════════ --}}
<section class="py-24 border-b border-void-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-3">— Kenapa Kami</p>
            <h2 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">Why VOID Supply</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'title' => 'Premium Quality', 'desc' => 'Bahan pilihan 220–380gsm. Setiap piece melewati quality control ketat.'],
                ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'title' => 'Secure Payment', 'desc' => 'Pembayaran aman via Midtrans. Mendukung semua metode populer Indonesia.'],
                ['icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'title' => 'Fast Shipping', 'desc' => 'Pengiriman via JNE, J&T, SiCepat, dan kurir lainnya ke seluruh Indonesia.'],
                ['icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'title' => 'Trusted Reviews', 'desc' => 'Ribuan customer puas. Rating rata-rata 4.8/5 dari verified buyers.'],
            ] as $prop)
                <div class="bg-void-card border border-void-border rounded-2xl p-6 hover:border-void-muted transition-colors duration-300 group">
                    <div class="w-10 h-10 rounded-xl bg-void-muted flex items-center justify-center mb-4
                                group-hover:bg-void-border transition-colors">
                        <svg class="w-5 h-5 text-void-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $prop['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-void-white mb-2">{{ $prop['title'] }}</h3>
                    <p class="text-xs text-void-gray leading-relaxed">{{ $prop['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     NEWSLETTER
════════════════════════════════════════════════════════════ --}}
<section class="py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-4">— Stay Updated</p>
            <h2 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight mb-4">
                Jangan Ketinggalan Drop
            </h2>
            <p class="text-void-gray text-sm leading-relaxed mb-8">
                Daftarkan email-mu dan jadilah yang pertama tahu tentang drop terbaru,
                restok, dan penawaran eksklusif member VOID Supply.
            </p>

            <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto" method="POST" action="#">
                @csrf
                <input type="email" name="email" placeholder="Email kamu" class="input-void flex-1" required>
                <button type="submit" class="btn-primary whitespace-nowrap">
                    Subscribe
                </button>
            </form>
            <p class="text-[11px] text-void-muted mt-4">
                Tidak ada spam. Unsubscribe kapan saja.
            </p>
        </div>
    </div>
</section>

@endsection