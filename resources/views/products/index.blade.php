@extends('layouts.app')

@section('title', request()->filled('q') ? 'Hasil Pencarian: ' . request('q') : 'All Products')
@section('meta_description', 'Temukan koleksi streetwear premium VOID Supply — hoodie, t-shirt, jersey, dan limited edition drops.')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Page Header ──────────────────────────────────────────── --}}
        <div class="mb-8">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">
                @if(request()->filled('q'))
                    — Search Results
                @elseif(request()->filled('category'))
                    — Category
                @else
                    — Catalog
                @endif
            </p>
            
            <h1 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">
                @if(request()->filled('q'))
                    Hasil Pencarian: 
                    <span class="text-void-white">"{{ request('q') }}"</span>
                @elseif(request()->filled('category'))
                    {{ $categories->firstWhere('slug', request('category'))?->name ?? 'Products' }}
                @else
                    All Products
                @endif
            </h1>
            
            <div class="flex items-center flex-wrap gap-3 mt-2">
                <p class="text-void-gray text-sm">
                    <span class="text-void-light font-semibold">{{ $products->total() }}</span> produk ditemukan
                    @if(request()->filled('q'))
                        untuk "<span class="text-void-light">{{ request('q') }}</span>"
                    @endif
                </p>
                
                @if(request()->filled('q'))
                    <a href="{{ route('products.index') }}" 
                       class="text-xs text-void-muted hover:text-void-accent transition-colors flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset pencarian
                    </a>
                @endif
            </div>
            
            @if(request()->filled('q'))
                <p class="text-xs text-void-muted mt-1">
                    Menampilkan produk yang mengandung kata "{{ request('q') }}"
                </p>
            @endif
        </div>

        <div class="flex flex-col lg:flex-row gap-8">

            {{-- SIDEBAR FILTER --}}
            <aside x-data="{ open: window.innerWidth >= 1024 }" class="lg:w-64 shrink-0">
                <button @click="open = !open"
                    class="lg:hidden w-full flex items-center justify-between px-4 py-3 bg-void-card border border-void-border rounded-xl mb-4 text-sm font-medium text-void-light">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-void-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter & Sort
                        @if(request()->hasAny(['q','category','status','size','min_price','max_price','limited']))
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        @endif
                    </span>
                    <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-void-gray transition-transform duration-200"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-4">
                    <form method="GET" action="{{ route('products.index') }}" id="filter-form">
                        
                        {{-- Search --}}
                        <div class="bg-void-card border border-void-border rounded-2xl p-5">
                            <h3 class="text-[10px] font-bold tracking-[0.2em] text-void-white uppercase mb-3">Cari Produk</h3>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-void-gray pointer-events-none"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama produk..."
                                       class="input-void pl-10 text-sm" onchange="this.form.submit()">
                            </div>
                            @if(request()->filled('q'))
                                <div class="mt-2">
                                    <a href="{{ request()->fullUrlWithoutQuery(['q']) }}" 
                                       class="text-[10px] text-void-muted hover:text-red-400 transition-colors">
                                        ✕ Hapus pencarian
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Kategori --}}
                        <div class="bg-void-card border border-void-border rounded-2xl p-5">
                            <h3 class="text-[10px] font-bold tracking-[0.2em] text-void-white uppercase mb-3">Kategori</h3>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 border-void-border bg-void-dark text-void-accent focus:ring-0 focus:ring-offset-0"
                                           onchange="document.getElementById('filter-form').submit()">
                                    <span class="text-sm text-void-gray group-hover:text-void-light transition-colors">Semua Kategori</span>
                                </label>
                                @foreach($categories as $cat)
                                    <label class="flex items-center justify-between cursor-pointer group">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="category" value="{{ $cat->slug }}"
                                                   {{ request('category') === $cat->slug ? 'checked' : '' }}
                                                   class="w-3.5 h-3.5 border-void-border bg-void-dark text-void-accent focus:ring-0 focus:ring-offset-0"
                                                   onchange="document.getElementById('filter-form').submit()">
                                            <span class="text-sm text-void-gray group-hover:text-void-light transition-colors">{{ $cat->name }}</span>
                                        </div>
                                        <span class="text-[10px] text-void-muted tabular-nums">{{ $cat->products_count }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="bg-void-card border border-void-border rounded-2xl p-5">
                            <h3 class="text-[10px] font-bold tracking-[0.2em] text-void-white uppercase mb-3">Status</h3>
                            <div class="space-y-2">
                                @foreach([
                                    '' => ['Semua Status', 'text-void-gray'],
                                    'available' => ['Available', 'text-green-400'],
                                    'preorder' => ['Preorder', 'text-yellow-400'],
                                    'coming_soon' => ['Coming Soon', 'text-blue-400'],
                                    'sold_out' => ['Sold Out', 'text-red-400'],
                                ] as $val => [$label, $color])
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="status" value="{{ $val }}"
                                               {{ request('status', '') === $val ? 'checked' : '' }}
                                               class="w-3.5 h-3.5 border-void-border bg-void-dark text-void-accent focus:ring-0 focus:ring-offset-0"
                                               onchange="document.getElementById('filter-form').submit()">
                                        <span class="text-sm {{ $color }} group-hover:brightness-125 transition-all">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Ukuran --}}
                        <div class="bg-void-card border border-void-border rounded-2xl p-5">
                            <h3 class="text-[10px] font-bold tracking-[0.2em] text-void-white uppercase mb-3">Ukuran</h3>
                            <div class="flex flex-wrap gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="size" value="" {{ !request('size') ? 'checked' : '' }} class="sr-only peer"
                                           onchange="document.getElementById('filter-form').submit()">
                                    <span class="flex items-center justify-center w-10 h-10 rounded-xl border border-void-border text-[11px] font-bold text-void-gray
                                                 peer-checked:border-void-accent peer-checked:text-void-accent peer-checked:bg-void-muted/30 hover:border-void-muted hover:text-void-light transition-all">All</span>
                                </label>
                                @foreach(['S','M','L','XL','XXL'] as $size)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="size" value="{{ $size }}" {{ request('size') === $size ? 'checked' : '' }} class="sr-only peer"
                                               onchange="document.getElementById('filter-form').submit()">
                                        <span class="flex items-center justify-center w-10 h-10 rounded-xl border border-void-border text-[11px] font-bold text-void-gray
                                                     peer-checked:border-void-accent peer-checked:text-void-accent peer-checked:bg-void-muted/30 hover:border-void-muted hover:text-void-light transition-all">{{ $size }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Harga --}}
                        <div class="bg-void-card border border-void-border rounded-2xl p-5" x-data="{
                            minP: {{ (int) request('min_price', $priceRange['min']) }},
                            maxP: {{ (int) request('max_price', $priceRange['max']) }},
                            absMin: {{ $priceRange['min'] }},
                            absMax: {{ $priceRange['max'] }},
                            fmt(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(v); }
                        }">
                            <h3 class="text-[10px] font-bold tracking-[0.2em] text-void-white uppercase mb-3">Rentang Harga</h3>
                            <div class="flex justify-between text-xs text-void-gray mb-3">
                                <span x-text="fmt(minP)"></span>
                                <span x-text="fmt(maxP)"></span>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-[9px] text-void-muted uppercase tracking-wider mb-1">Minimum</p>
                                    <input type="range" name="min_price" :min="absMin" :max="absMax" step="10000" x-model="minP" class="w-full accent-white">
                                </div>
                                <div>
                                    <p class="text-[9px] text-void-muted uppercase tracking-wider mb-1">Maksimum</p>
                                    <input type="range" name="max_price" :min="absMin" :max="absMax" step="10000" x-model="maxP" class="w-full accent-white">
                                </div>
                            </div>
                        </div>

                        {{-- Limited only --}}
                        <div class="bg-void-card border border-void-border rounded-2xl p-5">
                            <label class="flex items-center justify-between cursor-pointer">
                                <div>
                                    <p class="text-sm font-semibold text-void-light">Limited Edition Only</p>
                                    <p class="text-[11px] text-void-gray mt-0.5">Hanya tampilkan produk limited</p>
                                </div>
                                <div class="relative">
                                    <input type="checkbox" name="limited" value="1" {{ request()->boolean('limited') ? 'checked' : '' }}
                                           @change="$el.form.submit()" class="sr-only peer">
                                    <div class="w-10 h-6 bg-void-muted rounded-full peer peer-checked:bg-white transition-colors cursor-pointer"></div>
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-void-dark rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                </div>
                            </label>
                        </div>

                        <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">

                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 btn-primary py-2.5 text-xs">Terapkan Filter</button>
                            <a href="{{ route('products.index') }}" class="flex-1 btn-secondary py-2.5 text-xs text-center">Reset</a>
                        </div>
                    </form>
                </div>
            </aside>

            {{-- MAIN CONTENT --}}
            <div class="flex-1 min-w-0">

                {{-- Toolbar --}}
                <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
                    <div class="flex flex-wrap gap-2">
                        @if(request()->filled('q'))
                            <span class="inline-flex items-center gap-1.5 bg-void-card border border-void-border text-xs text-void-light px-3 py-1.5 rounded-full">
                                <svg class="w-3 h-3 text-void-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                Pencarian: "{{ Str::limit(request('q'), 20) }}"
                                <a href="{{ request()->fullUrlWithoutQuery(['q']) }}" class="text-void-gray hover:text-red-400 transition-colors ml-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif
                        @if(request()->filled('category'))
                            <span class="inline-flex items-center gap-1.5 bg-void-card border border-void-border text-xs text-void-light px-3 py-1.5 rounded-full">
                                Kategori: {{ $categories->firstWhere('slug', request('category'))?->name }}
                                <a href="{{ request()->fullUrlWithoutQuery(['category']) }}" class="text-void-gray hover:text-red-400 transition-colors ml-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif
                        @if(request()->filled('status'))
                            <span class="inline-flex items-center gap-1.5 bg-void-card border border-void-border text-xs text-void-light px-3 py-1.5 rounded-full capitalize">
                                Status: {{ str_replace('_', ' ', request('status')) }}
                                <a href="{{ request()->fullUrlWithoutQuery(['status']) }}" class="text-void-gray hover:text-red-400 transition-colors ml-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif
                        @if(request()->filled('size'))
                            <span class="inline-flex items-center gap-1.5 bg-void-card border border-void-border text-xs text-void-light px-3 py-1.5 rounded-full">
                                Ukuran: {{ request('size') }}
                                <a href="{{ request()->fullUrlWithoutQuery(['size']) }}" class="text-void-gray hover:text-red-400 transition-colors ml-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif
                        @if(request()->boolean('limited'))
                            <span class="inline-flex items-center gap-1.5 bg-void-card border border-white/20 text-xs text-void-accent px-3 py-1.5 rounded-full">
                                Limited Edition Only
                                <a href="{{ request()->fullUrlWithoutQuery(['limited']) }}" class="text-void-gray hover:text-red-400 transition-colors ml-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 ml-auto shrink-0">
                        <label class="text-xs text-void-gray whitespace-nowrap hidden sm:block">Urutkan:</label>
                        <select name="sort" form="filter-form" onchange="document.getElementById('filter-form').submit()"
                            class="bg-void-card border border-void-border text-void-light text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-void-muted cursor-pointer min-w-[140px]">
                            @foreach(['latest' => 'Terbaru', 'popular' => 'Terpopuler', 'price_asc' => 'Harga Terendah', 'price_desc' => 'Harga Tertinggi', 'name_asc' => 'Nama A–Z'] as $val => $label)
                                <option value="{{ $val }}" {{ request('sort', 'latest') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Products Grid --}}
                @if($products->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                        @foreach($products as $product)
                            @include('components.product-card', ['product' => $product])
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-10">
                        {{ $products->appends(request()->query())->links('components.pagination') }}
                    </div>

                @else
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <div class="w-20 h-20 rounded-2xl bg-void-card border border-void-border flex items-center justify-center mb-5">
                            <svg class="w-8 h-8 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-void-white mb-2">
                            @if(request()->filled('q')) Tidak ada hasil untuk "{{ request('q') }}" @else Produk tidak ditemukan @endif
                        </h3>
                        <p class="text-sm text-void-gray max-w-xs leading-relaxed mb-6">
                            @if(request()->filled('q')) Coba gunakan kata kunci lain atau @endif lihat koleksi produk lainnya.
                        </p>
                        <a href="{{ route('products.index') }}" class="btn-secondary text-sm">Reset Semua Filter</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection