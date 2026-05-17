{{--
    Product Card Component
    @param  \App\Models\Product  $product
--}}
<article class="group relative bg-void-card border border-void-border rounded-2xl overflow-hidden
                hover:border-void-muted transition-all duration-300 flex flex-col">

    {{-- Image Container --}}
    <div class="relative aspect-square overflow-hidden bg-void-dark shrink-0">
        <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
            <img src="{{ $product->primary_image_url }}"
                 alt="{{ $product->name }}"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.06]"
                 loading="lazy">
        </a>

        {{-- Badges --}}
        <div class="absolute top-3 left-3 flex flex-col gap-1.5 pointer-events-none">
            @if($product->is_limited)
                <span class="badge-limited">Limited</span>
            @endif
            @if($product->status === 'sold_out')
                <span class="badge-sold-out">Sold Out</span>
            @elseif($product->status === 'preorder')
                <span class="badge-preorder">Preorder</span>
            @elseif($product->status === 'coming_soon')
                <span class="badge-coming-soon">Coming Soon</span>
            @endif
        </div>

        {{-- Wishlist Button --}}
        @auth
            <button type="button"
                x-data="{ inWishlist: false, loading: false }"
                x-init="fetch('/wishlist/check/{{ $product->id }}', {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                }).then(r => r.json()).then(d => { if (d.in_wishlist !== undefined) inWishlist = d.in_wishlist; }).catch(() => {})"
                @click.prevent="if (loading) return; loading = true;
                    fetch('{{ route('wishlist.toggle') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ product_id: {{ $product->id }} })
                    }).then(r => r.json()).then(d => {
                        if (d.success) {
                            inWishlist = d.in_wishlist;
                            window.showNotification?.(d.in_wishlist ? '❤️ Ditambahkan ke wishlist' : '💔 Dihapus dari wishlist', 'success');
                        }
                    }).finally(() => loading = false)"
                :class="inWishlist ? 'text-red-400 bg-void-black/70' : 'text-void-gray bg-void-black/50'"
                class="absolute top-3 right-3 p-2 rounded-xl backdrop-blur-sm hover:text-red-400 transition-all duration-200
                       opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0"
                :title="inWishlist ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist'">
                <svg class="w-4 h-4" :fill="inWishlist ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        @endauth

        {{-- Stock Bar --}}
        @if($product->status === 'available' && $product->stock <= 10 && $product->stock > 0)
            <div class="absolute bottom-3 left-3 right-3">
                <div class="bg-void-black/70 backdrop-blur-sm rounded-lg px-3 py-1.5">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[9px] text-void-gray">Sisa stok</span>
                        <span class="text-[9px] font-bold text-orange-400">{{ $product->stock }} pcs</span>
                    </div>
                    <div class="w-full bg-void-border rounded-full h-0.5">
                        <div class="bg-orange-400 h-0.5 rounded-full" style="width: {{ min(($product->stock / 30) * 100, 100) }}%"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Sold Out Overlay --}}
        @if($product->status === 'sold_out')
            <div class="absolute inset-0 bg-void-black/50 flex items-center justify-center pointer-events-none">
                <span class="text-[10px] font-bold tracking-[0.3em] text-void-gray/80 uppercase">Habis Terjual</span>
            </div>
        @endif
    </div>

    {{-- Product Info --}}
    <div class="p-4 flex flex-col flex-1">
        {{-- Category --}}
        <p class="text-[10px] font-medium tracking-[0.15em] text-void-gray uppercase mb-1.5">
            {{ $product->category->name ?? '—' }}
        </p>

        {{-- Name --}}
        <a href="{{ route('products.show', $product->slug) }}" class="flex-1">
            <h3 class="text-sm font-bold text-void-white hover:text-void-accent transition-colors leading-snug line-clamp-2 mb-2">
                {{ $product->name }}
            </h3>
        </a>

        {{-- Rating --}}
        @if($product->reviews_count > 0)
            @php $avg = round($product->reviews_avg_rating ?? $product->reviews->avg('rating'), 1); @endphp
            <div class="flex items-center gap-1.5 mb-3">
                <div class="flex gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-3 h-3 {{ $i <= round($avg) ? 'text-yellow-400' : 'text-void-muted' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <span class="text-[10px] text-void-gray">({{ $product->reviews_count }})</span>
            </div>
        @else
            <div class="mb-3"></div>
        @endif

        {{-- Price & Actions --}}
        <div class="flex flex-col gap-2 mt-auto">
            {{-- Harga --}}
            <p class="text-base font-black text-void-accent">{{ $product->formatted_price }}</p>

            {{-- Action Button --}}
            <div class="mt-2">
                @if($product->status === 'available')
                    <a href="{{ route('products.show', $product->slug) }}"
                       class="w-full py-2 text-[11px] font-bold bg-white text-black rounded-lg hover:bg-void-light transition-colors tracking-wide
                              opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-200 flex items-center justify-center gap-1">
                        Beli
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @elseif($product->status === 'preorder')
                    <a href="{{ route('products.show', $product->slug) }}"
                       class="w-full py-2 text-[11px] font-bold border border-yellow-500/50 text-yellow-400 rounded-lg hover:bg-yellow-500/10 transition-colors tracking-wide
                              opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-200 flex items-center justify-center gap-1">
                        Preorder Sekarang
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @elseif($product->status === 'coming_soon')
                    <button type="button"
                        x-data="{ loading: false, notified: false }"
                        @click.prevent="if (loading || notified) return; loading = true;
                            fetch('{{ route('product.notify', $product->id) }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                            }).then(r => r.json()).then(data => {
                                if (data.success) {
                                    notified = true;
                                    window.showNotification?.(data.message, 'success');
                                } else {
                                    window.showNotification?.(data.message || 'Gagal', 'error');
                                }
                            }).catch(error => {
                                console.error('Error:', error);
                                window.showNotification?.('Terjadi kesalahan', 'error');
                            }).finally(() => loading = false)"
                        :disabled="loading || notified"
                        class="w-full py-2 text-[11px] font-bold border border-blue-500/50 text-blue-400 rounded-lg transition-colors tracking-wide
                               opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-200
                               disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1">
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
                @elseif($product->status === 'sold_out')
                    <button type="button"
                        x-data="{ loading: false, notified: false }"
                        @click.prevent="if (loading || notified) return; loading = true;
                            fetch('{{ route('product.notify', $product->id) }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                            }).then(r => r.json()).then(data => {
                                if (data.success) {
                                    notified = true;
                                    window.showNotification?.(data.message, 'success');
                                } else {
                                    window.showNotification?.(data.message || 'Gagal', 'error');
                                }
                            }).catch(error => {
                                console.error('Error:', error);
                                window.showNotification?.('Terjadi kesalahan', 'error');
                            }).finally(() => loading = false)"
                        :disabled="loading || notified"
                        class="w-full py-2 text-[11px] font-bold border border-red-500/50 text-red-400 rounded-lg transition-colors tracking-wide
                               opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-200
                               disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1">
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
                @endif
            </div>
        </div>
    </div>
</article>