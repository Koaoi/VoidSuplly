@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" 
         x-data="cartComponent()" 
         x-init="initCart()">
        
        <h1 class="text-2xl font-black text-void-white mb-6">Keranjang Belanja</h1>
        
        {{-- Alert Notification --}}
        <div x-show="alert.message" 
             x-transition.duration.300ms 
             class="mb-4 p-3 rounded-lg text-sm"
             :class="alert.type === 'success' ? 'bg-green-500/10 border border-green-500/30 text-green-400' : 'bg-red-500/10 border border-red-500/30 text-red-400'"
             x-text="alert.message">
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="text-center py-16">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
            <p class="text-void-gray mt-2">Memuat keranjang...</p>
        </div>

        {{-- Empty Cart --}}
        <template x-if="!loading && items.length === 0">
            <div class="text-center py-16">
                <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-void-card border border-void-border flex items-center justify-center">
                    <svg class="w-8 h-8 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-void-white mb-2">Keranjang Kosong</h3>
                <p class="text-void-gray mb-6">Belum ada produk di keranjang kamu.</p>
                <a href="{{ route('products.index') }}" class="btn-primary">Mulai Belanja</a>
            </div>
        </template>

        {{-- Cart Items --}}
        <template x-if="!loading && items.length > 0">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Items List --}}
                <div class="lg:col-span-2 space-y-4">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="bg-void-card border border-void-border rounded-2xl p-4 flex gap-4">
                            {{-- Image --}}
                            <div class="w-20 h-20 rounded-xl overflow-hidden bg-void-dark shrink-0">
                                <img :src="item.product.primary_image_url || '/images/placeholder.jpg'" 
                                     class="w-full h-full object-cover"
                                     :alt="item.product.name">
                            </div>
                            
                            {{-- Info --}}
                            <div class="flex-1">
                                <a :href="'/products/' + item.product.slug" class="hover:text-void-accent">
                                    <h3 class="font-semibold text-void-white" x-text="item.product.name"></h3>
                                </a>
                                <p class="text-xs text-void-gray mt-0.5">
                                    <span class="font-semibold text-void-accent">Rp <span x-text="formatNumber(item.price)"></span></span>
                                    <span x-show="item.size" class="ml-2" x-text="'Size: ' + item.size"></span>
                                </p>
                                
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="flex items-center gap-2">
                                        <button @click="updateQuantity(item, item.quantity - 1)" 
                                                :disabled="updating"
                                                class="w-7 h-7 rounded-lg border border-void-border text-void-gray hover:text-void-accent disabled:opacity-50">
                                            -
                                        </button>
                                        <span class="w-8 text-center text-void-white text-sm" x-text="item.quantity"></span>
                                        <button @click="updateQuantity(item, item.quantity + 1)" 
                                                :disabled="updating"
                                                class="w-7 h-7 rounded-lg border border-void-border text-void-gray hover:text-void-accent disabled:opacity-50">
                                            +
                                        </button>
                                    </div>
                                    
                                    <button @click="removeItem(item, index)" 
                                            :disabled="updating"
                                            class="ml-auto text-xs text-red-400 hover:text-red-300 disabled:opacity-50">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Total --}}
                            <div class="text-right shrink-0">
                                <p class="font-bold text-void-accent">
                                    Rp <span x-text="formatNumber(item.price * item.quantity)"></span>
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
                
                {{-- Summary --}}
                <div class="bg-void-card border border-void-border rounded-2xl p-6 h-fit sticky top-24">
                    <h2 class="text-sm font-bold text-void-white mb-4">Ringkasan Belanja</h2>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-void-gray">Subtotal</span>
                            <span class="text-void-white font-semibold">Rp <span x-text="formatNumber(total)"></span></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-void-gray">Ongkir</span>
                            <span class="text-void-white">Dihitung saat checkout</span>
                        </div>
                        <div class="border-t border-void-border pt-3 mt-2">
                            <div class="flex justify-between font-bold">
                                <span class="text-void-white">Total</span>
                                <span class="text-void-accent text-lg">Rp <span x-text="formatNumber(total)"></span></span>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('checkout.index') }}" 
                       class="btn-primary w-full py-3 text-center block">
                        Lanjut ke Checkout →
                    </a>
                    
                    <a href="{{ route('products.index') }}" 
                       class="text-center text-xs text-void-gray hover:text-void-accent transition-colors block mt-3">
                        ← Lanjutkan Belanja
                    </a>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function cartComponent() {
    return {
        items: [],
        total: 0,
        loading: true,
        updating: false,
        alert: { message: '', type: 'success' },
        
        async initCart() {
            await this.loadCart();
        },
        
        async loadCart() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("cart.index") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                console.log('Cart data:', data); // Debug: lihat di console browser
                
                if (data.success) {
                    this.items = data.items || [];
                    this.total = data.total || 0;
                } else {
                    this.items = [];
                    this.total = 0;
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                this.showAlert('Gagal memuat keranjang', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async updateQuantity(item, newQuantity) {
            if (newQuantity < 1 || newQuantity > 99) return;
            if (this.updating) return;
            
            this.updating = true;
            
            try {
                const response = await fetch(`/cart/${item.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: newQuantity })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    item.quantity = newQuantity;
                    // Update total
                    this.total = this.items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
                    this.showAlert('Jumlah produk diperbarui', 'success');
                    this.updateNavbarCount(data.count);
                } else {
                    this.showAlert(data.message || 'Gagal memperbarui jumlah', 'error');
                }
            } catch (error) {
                console.error('Error updating quantity:', error);
                this.showAlert('Terjadi kesalahan', 'error');
            } finally {
                this.updating = false;
            }
        },
        
        async removeItem(item, index) {
            if (!confirm('Hapus item ini dari keranjang?')) return;
            if (this.updating) return;
            
            this.updating = true;
            
            try {
                const response = await fetch(`/cart/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.items.splice(index, 1);
                    // Update total
                    this.total = this.items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
                    this.showAlert('Item dihapus dari keranjang', 'success');
                    this.updateNavbarCount(data.count);
                    
                    if (this.items.length === 0) {
                        location.reload();
                    }
                } else {
                    this.showAlert(data.message || 'Gagal menghapus item', 'error');
                }
            } catch (error) {
                console.error('Error removing item:', error);
                this.showAlert('Terjadi kesalahan', 'error');
            } finally {
                this.updating = false;
            }
        },
        
        formatNumber(value) {
            if (!value) return '0';
            return new Intl.NumberFormat('id-ID').format(value);
        },
        
        showAlert(message, type) {
            this.alert = { message, type };
            setTimeout(() => {
                this.alert.message = '';
            }, 3000);
        },
        
        updateNavbarCount(count) {
            // Dispatch event untuk navbar
            window.dispatchEvent(new CustomEvent('cart-updated', { 
                detail: { count: count } 
            }));
            
            // Update badge di navbar jika ada
            const badgeElements = document.querySelectorAll('.cart-badge, [x-text="cartCount"]');
            badgeElements.forEach(el => {
                if (el.__x && el.__x.$data && typeof el.__x.$data.cartCount !== 'undefined') {
                    el.__x.$data.cartCount = count;
                }
            });
        }
    }
}
</script>
@endpush
@endsection