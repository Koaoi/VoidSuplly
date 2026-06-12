@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit($product->short_description ?? $product->description, 150))

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <nav class="flex text-xs text-void-gray">
                <a href="{{ route('home') }}" class="hover:text-void-accent">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('products.index') }}" class="hover:text-void-accent">Products</a>
                <span class="mx-2">/</span>
                <span class="text-void-light">{{ $product->name }}</span>
            </nav>
        </div>
        
        {{-- Product Detail --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            
            {{-- Product Images --}}
            <div>
                <div class="aspect-square rounded-2xl overflow-hidden bg-void-card border border-void-border">
                    @if($product->primary_image_url)
                        <img src="{{ $product->primary_image_url }}" 
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-20 h-20 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Product Info --}}
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-void-white mb-2">{{ $product->name }}</h1>
                
                {{-- Rating --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= round($avgRating) ? 'text-yellow-400 fill-yellow-400' : 'text-void-muted' }}" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-xs text-void-gray">({{ $reviewsCount }} ulasan)</span>
                </div>
                
                {{-- Price --}}
                <div class="mb-6">
                    <span class="text-3xl font-black text-void-accent">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                </div>
                
                {{-- Status Badge --}}
                <div class="mb-6">
                    @if($product->status === 'available')
                        <span class="text-xs font-bold text-green-400 bg-green-500/10 border border-green-500/30 px-3 py-1 rounded-full">Tersedia</span>
                    @elseif($product->status === 'preorder')
                        <span class="text-xs font-bold text-yellow-400 bg-yellow-500/10 border border-yellow-500/30 px-3 py-1 rounded-full">Preorder</span>
                    @elseif($product->status === 'coming_soon')
                        <span class="text-xs font-bold text-blue-400 bg-blue-500/10 border border-blue-500/30 px-3 py-1 rounded-full">Coming Soon</span>
                    @else
                        <span class="text-xs font-bold text-red-400 bg-red-500/10 border border-red-500/30 px-3 py-1 rounded-full">Sold Out</span>
                    @endif
                    
                    @if($product->is_limited)
                        <span class="text-xs font-bold text-white bg-black px-3 py-1 rounded-full ml-2">Limited Edition</span>
                    @endif
                </div>
                
                {{-- Size Selection --}}
                @php
                    $hasVariants = false;
                    if (method_exists($product, 'variants') && $product->variants && $product->variants->isNotEmpty()) {
                        $hasVariants = true;
                    }
                    $hasSizes = $product->sizes && is_array($product->sizes) && count($product->sizes) > 0;
                @endphp

                @if($hasVariants || $hasSizes)
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Pilih Ukuran</label>
                        <div class="flex flex-wrap gap-2">
                            @if($hasVariants)
                                @foreach($product->variants as $variant)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="size" value="{{ $variant->size }}" 
                                               data-stock="{{ $variant->stock }}"
                                               {{ $loop->first ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <span class="flex items-center justify-center w-12 h-10 rounded-xl
                                                     border border-void-border text-xs font-bold text-void-gray
                                                     peer-checked:border-void-accent peer-checked:text-void-accent
                                                     peer-checked:bg-void-muted/30 hover:border-void-muted
                                                     hover:text-void-light transition-all">
                                            {{ $variant->size }}
                                        </span>
                                    </label>
                                @endforeach
                            @elseif($hasSizes)
                                @foreach($product->sizes as $size)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="size" value="{{ $size }}" 
                                               {{ $loop->first ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <span class="flex items-center justify-center w-12 h-10 rounded-xl
                                                     border border-void-border text-xs font-bold text-void-gray
                                                     peer-checked:border-void-accent peer-checked:text-void-accent
                                                     peer-checked:bg-void-muted/30 hover:border-void-muted
                                                     hover:text-void-light transition-all">
                                            {{ $size }}
                                        </span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @else
                    <input type="hidden" name="size" value="FREE SIZE" id="default-size">
                @endif
                
                {{-- Description --}}
                <p class="text-sm text-void-gray leading-relaxed mb-6">
                    {{ $product->description ?? $product->short_description }}
                </p>
                
                {{-- Buttons: Add to Cart & Buy Now --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    {{-- Tombol Tambah ke Keranjang --}}
                    <button 
                        id="add-to-cart-btn"
                        onclick="addToCart()"
                        class="flex-1 bg-void-card border border-void-border hover:border-void-accent 
                               text-void-white font-bold py-3 rounded-xl transition-all duration-300
                               flex items-center justify-center gap-2"
                        {{ !in_array($product->status, ['available', 'preorder']) ? 'disabled' : '' }}
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M18 13l1.5 6M9 21h6M12 18v3"/>
                        </svg>
                        Tambah ke Keranjang
                    </button>
                    
                    {{-- Tombol Beli Sekarang --}}
                    <button 
                        id="buy-now-btn"
                        onclick="buyNow()"
                        class="flex-1 bg-void-card border border-void-border hover:border-void-accent 
                               text-void-white font-bold py-3 rounded-xl transition-all duration-300
                               flex items-center justify-center gap-2"
                        {{ !in_array($product->status, ['available', 'preorder']) ? 'disabled' : '' }}
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Beli Sekarang
                    </button>
                    
                    {{-- Tombol Wishlist --}}
                    <button 
                        id="wishlist-btn"
                        onclick="toggleWishlist()"
                        class="w-12 h-12 rounded-xl border border-void-border hover:border-void-accent 
                               transition-colors flex items-center justify-center shrink-0"
                    >
                        @if($inWishlist) ❤️ @else 🤍 @endif
                    </button>
                </div>
                
                {{-- Additional Info --}}
                <div class="mt-6 pt-6 border-t border-void-border">
                    <div class="flex items-center gap-4 text-xs text-void-gray">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                            Original Product
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Garansi 100%
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- TABS: Deskripsi / Detail / Ulasan --}}
        <div x-data="{ activeTab: 'description' }" class="mt-16">
            
            {{-- Tab Navigation --}}
            <div class="flex border-b border-void-border gap-6 mb-6 overflow-x-auto">
                <button 
                    @click="activeTab = 'description'"
                    :class="{ 'text-void-accent border-b-2 border-void-accent': activeTab === 'description', 'text-void-gray hover:text-void-light': activeTab !== 'description' }"
                    class="pb-3 text-sm font-semibold transition-colors whitespace-nowrap">
                    Deskripsi
                </button>
                <button 
                    @click="activeTab = 'details'"
                    :class="{ 'text-void-accent border-b-2 border-void-accent': activeTab === 'details', 'text-void-gray hover:text-void-light': activeTab !== 'details' }"
                    class="pb-3 text-sm font-semibold transition-colors whitespace-nowrap">
                    Detail Produk
                </button>
                <button 
                    @click="activeTab = 'reviews'"
                    :class="{ 'text-void-accent border-b-2 border-void-accent': activeTab === 'reviews', 'text-void-gray hover:text-void-light': activeTab !== 'reviews' }"
                    class="pb-3 text-sm font-semibold transition-colors whitespace-nowrap">
                    Ulasan ({{ $reviewsCount }})
                </button>
            </div>
            
            {{-- Tab Content: Description --}}
            <div x-show="activeTab === 'description'" class="prose prose-invert max-w-none">
                <p class="text-void-gray leading-relaxed">
                    {{ $product->description ?? $product->short_description ?? 'Tidak ada deskripsi untuk produk ini.' }}
                </p>
            </div>
            
            {{-- Tab Content: Details --}}
            <div x-show="activeTab === 'details'" class="space-y-3">
                <div class="flex gap-3">
                    <span class="text-void-gray w-28 shrink-0">Kategori</span>
                    <span class="text-void-light">{{ $product->category->name ?? 'Uncategorized' }}</span>
                </div>
                <div class="flex gap-3">
                    <span class="text-void-gray w-28 shrink-0">SKU</span>
                    <span class="text-void-light">{{ $product->sku ?? '-' }}</span>
                </div>
                @if($product->weight)
                <div class="flex gap-3">
                    <span class="text-void-gray w-28 shrink-0">Berat</span>
                    <span class="text-void-light">{{ $product->weight }} gram</span>
                </div>
                @endif
            </div>
            
            {{-- Tab Content: Reviews --}}
            <div x-show="activeTab === 'reviews'" class="space-y-6">
                @if($reviewsCount > 0)
                    @foreach($product->reviews as $review)
                        <div class="border-b border-void-border pb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-yellow-400 fill-yellow-400' : 'text-void-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-xs text-void-gray">{{ $review->user->name ?? 'Anonymous' }}</span>
                                <span class="text-[10px] text-void-muted">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-void-gray">{{ $review->comment }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-void-gray py-8">Belum ada ulasan untuk produk ini.</p>
                @endif
            </div>
        </div>
        
        {{-- Related Products --}}
        @if($relatedProducts->count() > 0)
            <div class="mt-16">
                <h2 class="text-xl font-bold text-void-white mb-6">Produk Terkait</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($relatedProducts as $related)
                        @include('components.product-card', ['product' => $related])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function getSelectedSize() {
    const sizeRadio = document.querySelector('input[name=size]:checked');
    if (sizeRadio) {
        return sizeRadio.value;
    }
    return 'FREE SIZE';
}

function addToCart() {
    const btn = document.getElementById('add-to-cart-btn');
    const originalText = btn.innerHTML;
    const size = getSelectedSize();
    
    btn.innerHTML = `
        <span class="flex items-center justify-center gap-2">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses...
        </span>
    `;
    btn.disabled = true;
    
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: {{ $product->id }},
            size: size,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.showNotification) {
                window.showNotification(data.message, 'success');
            } else {
                alert(data.message);
            }
            window.dispatchEvent(new CustomEvent('cart-updated', { 
                detail: { count: data.count } 
            }));
        } else {
            if (window.showNotification) {
                window.showNotification(data.message || 'Gagal menambahkan ke keranjang', 'error');
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showNotification) {
            window.showNotification('Terjadi kesalahan, silakan coba lagi', 'error');
        } else {
            alert('Terjadi kesalahan, silakan coba lagi');
        }
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function buyNow() {
    const btn = document.getElementById('buy-now-btn');
    const originalText = btn.innerHTML;
    const size = getSelectedSize();
    
    btn.innerHTML = `
        <span class="flex items-center justify-center gap-2">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Memproses...
        </span>
    `;
    btn.disabled = true;
    
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: {{ $product->id }},
            size: size,
            quantity: 1,
            buy_now: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("checkout.index") }}';
        } else {
            if (window.showNotification) {
                window.showNotification(data.message || 'Gagal memproses', 'error');
            } else {
                alert(data.message || 'Gagal memproses');
            }
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showNotification) {
            window.showNotification('Terjadi kesalahan, silakan coba lagi', 'error');
        } else {
            alert('Terjadi kesalahan, silakan coba lagi');
        }
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function toggleWishlist() {
    const btn = document.getElementById('wishlist-btn');
    
    fetch('{{ route("wishlist.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ product_id: {{ $product->id }} })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = data.in_wishlist ? '❤️' : '🤍';
            if (window.showNotification) {
                window.showNotification(data.in_wishlist ? 'Ditambahkan ke wishlist' : 'Dihapus dari wishlist', 'success');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush
@endsection