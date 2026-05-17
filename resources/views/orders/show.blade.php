@extends('layouts.app')

@section('title', 'Detail Pesanan — ' . $order->order_code)

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('orders.index') }}"
           class="inline-flex items-center gap-2 text-xs text-void-gray
                  hover:text-void-accent transition-colors mb-8">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pesanan
        </a>

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
            <div>
                <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-1">
                    Detail Pesanan
                </p>
                <h1 class="text-2xl font-black text-void-accent">
                    {{ $order->order_code }}
                </h1>
                <p class="text-xs text-void-gray mt-1">
                    Dipesan {{ $order->created_at->format('d F Y, H:i') }} WIB
                </p>
            </div>

            @php
                $colorMap = [
                    'pending'    => 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
                    'paid'       => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
                    'processing' => 'bg-purple-500/10 border-purple-500/30 text-purple-400',
                    'shipped'    => 'bg-orange-500/10 border-orange-500/30 text-orange-400',
                    'completed'  => 'bg-green-500/10 border-green-500/30 text-green-400',
                    'cancelled'  => 'bg-red-500/10 border-red-500/30 text-red-400',
                ];
                $badgeClass = $colorMap[$order->status] ?? 'bg-void-muted/30 border-void-border text-void-gray';
            @endphp
            <span class="inline-flex items-center text-xs font-bold tracking-widest
                         uppercase border px-4 py-2 rounded-full {{ $badgeClass }}">
                {{ $order->status_label }}
            </span>
        </div>

        {{-- ── Status Tracker ───────────────────────────────────────── --}}
        @if($order->status !== 'cancelled')
            @php
                $steps = ['pending', 'paid', 'processing', 'shipped', 'completed'];
                $stepLabels = [
                    'pending'    => ['Menunggu Bayar', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'paid'       => ['Dikonfirmasi',   'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'processing' => ['Diproses',       'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    'shipped'    => ['Dikirim',        'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                    'completed'  => ['Selesai',        'M5 13l4 4L19 7'],
                ];
                $currentIdx = array_search($order->status, $steps);
                $currentIdx = $currentIdx === false ? 0 : $currentIdx;
            @endphp

            <div class="bg-void-card border border-void-border rounded-2xl p-6 mb-6">
                <h2 class="text-[10px] font-bold tracking-[0.2em] text-void-white uppercase mb-6">
                    Status Pesanan
                </h2>
                <div class="relative">
                    {{-- Background line --}}
                    <div class="absolute top-4 left-4 right-4 h-0.5 bg-void-border"></div>
                    {{-- Active line --}}
                    <div class="absolute top-4 left-4 h-0.5 bg-white transition-all duration-700"
                         style="width: {{ $currentIdx > 0 ? min(($currentIdx / (count($steps) - 1)) * 100, 100) : 0 }}%">
                    </div>

                    {{-- Step dots --}}
                    <div class="relative flex justify-between">
                        @foreach($steps as $i => $step)
                            @php $done = $i <= $currentIdx; @endphp
                            <div class="flex flex-col items-center gap-2.5" style="width: 20%">
                                <div class="relative z-10 w-8 h-8 rounded-full border-2 flex items-center
                                            justify-center transition-all duration-500
                                            {{ $done ? 'bg-white border-white' : 'bg-void-dark border-void-border' }}">
                                    <svg class="w-4 h-4 {{ $done ? 'text-black' : 'text-void-muted' }}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="1.5" d="{{ $stepLabels[$step][1] }}"/>
                                    </svg>
                                </div>
                                <span class="text-[9px] font-semibold text-center leading-tight uppercase
                                             tracking-wide {{ $done ? 'text-void-white' : 'text-void-muted' }}">
                                    {{ $stepLabels[$step][0] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-5 mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <p class="text-sm font-semibold text-red-400">Pesanan Dibatalkan</p>
                </div>
            </div>
        @endif

        {{-- ── Aksi Cepat (Bayar jika pending) ────────────────────── --}}
        @if($order->status === 'pending')
            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-2xl p-5 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-yellow-400 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Menunggu Pembayaran
                        </p>
                        <p class="text-xs text-void-gray mt-1">
                            Selesaikan pembayaran agar pesananmu segera diproses.
                        </p>
                    </div>
                    <a href="{{ route('payment.show', $order->order_code) }}"
                       class="btn-primary shrink-0 text-sm py-2.5 px-6 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Bayar Sekarang
                    </a>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── LEFT: Items + Review ─────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Order items --}}
                <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-void-border">
                        <h2 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase">
                            Item Pesanan ({{ $order->items->count() }} produk)
                        </h2>
                    </div>
                    <div class="divide-y divide-void-border">
                        @foreach($order->items as $item)
                            <div class="flex items-start gap-4 p-5">
                                <a href="{{ $item->product ? route('products.show', $item->product->slug) : '#' }}"
                                   class="shrink-0 w-16 h-16 rounded-xl overflow-hidden bg-void-dark group">
                                    <img src="{{ $item->product?->primary_image_url ?? asset('images/product-placeholder.png') }}"
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $item->product ? route('products.show', $item->product->slug) : '#' }}"
                                       class="text-sm font-bold text-void-white hover:text-void-accent transition-colors">
                                        {{ $item->product_name }}
                                    </a>
                                    <div class="flex flex-wrap gap-3 mt-1 text-xs text-void-gray">
                                        <span>Ukuran: <span class="text-void-light font-medium">{{ $item->size }}</span></span>
                                        <span>Qty: <span class="text-void-light font-medium">{{ $item->quantity }}</span></span>
                                        <span>Harga: <span class="text-void-light font-medium">{{ $item->formatted_price }}</span></span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-black text-void-accent">
                                        {{ $item->formatted_subtotal }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totals --}}
                    <div class="px-5 py-4 border-t border-void-border bg-void-darker space-y-2.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-void-gray">Subtotal Produk</span>
                            <span class="text-void-light">
                                Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-void-gray">
                                Ongkos Kirim
                                @if($order->shippingAddress)
                                    <span class="text-void-muted">
                                        ({{ $order->shippingAddress->courier }}
                                        {{ $order->shippingAddress->service }})
                                    </span>
                                @endif
                            </span>
                            <span class="text-void-light">
                                Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-base font-black pt-2 border-t border-void-border">
                            <span class="text-void-white">Total Pembayaran</span>
                            <span class="text-void-accent">{{ $order->formatted_total }}</span>
                        </div>
                    </div>
                </div>

                {{-- Review section (hanya untuk completed) --}}
                @if($order->status === 'completed')
                    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-void-border">
                            <h2 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase">
                                Ulasan Produk
                            </h2>
                            <p class="text-[11px] text-void-gray mt-0.5">
                                Bagikan pengalamanmu dengan produk yang sudah kamu beli.
                            </p>
                        </div>

                        <div class="divide-y divide-void-border">
                            @foreach($order->items as $item)
                                @php
                                    $alreadyReviewed = in_array($item->product_id, $reviewedProductIds);
                                @endphp
                                <div class="p-5">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-void-dark shrink-0">
                                            <img src="{{ $item->product?->primary_image_url ?? asset('images/product-placeholder.png') }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <p class="text-sm font-semibold text-void-light line-clamp-1 flex-1">
                                            {{ $item->product_name }}
                                        </p>
                                        @if($alreadyReviewed)
                                            <span class="text-xs text-green-400 flex items-center gap-1.5 shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Sudah diulas
                                            </span>
                                        @endif
                                    </div>

                                    @if(!$alreadyReviewed)
                                        <form method="POST" action="{{ route('reviews.store') }}"
                                              x-data="{ rating: 0, hover: 0 }"
                                              class="space-y-3">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                            <input type="hidden" name="order_id"   value="{{ $order->id }}">
                                            <input type="hidden" name="rating"      :value="rating">

                                            <div>
                                                <p class="text-xs text-void-gray mb-2">Rating *</p>
                                                <div class="flex gap-1">
                                                    @for($s = 1; $s <= 5; $s++)
                                                        <button type="button"
                                                                @click="rating = {{ $s }}"
                                                                @mouseenter="hover = {{ $s }}"
                                                                @mouseleave="hover = 0"
                                                                class="transition-transform hover:scale-110 active:scale-95">
                                                            <svg class="w-7 h-7 transition-colors duration-100"
                                                                 :class="(hover || rating) >= {{ $s }}
                                                                     ? 'text-yellow-400'
                                                                     : 'text-void-muted'"
                                                                 fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                            </svg>
                                                        </button>
                                                    @endfor
                                                </div>
                                            </div>

                                            <textarea name="comment" rows="2"
                                                      class="input-void resize-none text-sm"
                                                      placeholder="Ceritakan pengalamanmu (opsional)..."></textarea>

                                            <button type="submit"
                                                    :disabled="rating === 0"
                                                    class="btn-primary text-xs px-5 py-2
                                                           disabled:opacity-50 disabled:cursor-not-allowed">
                                                Kirim Ulasan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Catatan order --}}
                @if($order->notes)
                    <div class="bg-void-card border border-void-border rounded-2xl p-5">
                        <h3 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase mb-2">
                            Catatan Pesanan
                        </h3>
                        <p class="text-sm text-void-gray leading-relaxed">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- ── RIGHT: Sidebar info ──────────────────────────────── --}}
            <div class="space-y-5">

                {{-- Alamat pengiriman --}}
                @if($order->shippingAddress)
                    <div class="bg-void-card border border-void-border rounded-2xl p-5">
                        <h3 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase mb-4">
                            Alamat Pengiriman
                        </h3>
                        @php $addr = $order->shippingAddress; @endphp
                        <div class="space-y-1.5 text-sm">
                            <p class="font-bold text-void-white">{{ $addr->recipient_name }}</p>
                            <p class="text-void-gray text-xs">{{ $addr->phone }}</p>
                            <p class="text-void-gray text-xs leading-relaxed mt-2">
                                {{ $addr->address_detail }},
                                {{ $addr->city }},
                                {{ $addr->province }}
                                {{ $addr->postal_code }}
                            </p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-void-border">
                            <p class="text-xs font-bold text-void-white">
                                {{ $addr->courier }} — {{ $addr->service }}
                            </p>
                            @if($addr->service_description)
                                <p class="text-[10px] text-void-gray mt-0.5">{{ $addr->service_description }}</p>
                            @endif
                            @if($addr->estimated_days)
                                <p class="text-[10px] text-green-400 mt-1">
                                    Estimasi {{ $addr->estimated_days }} hari kerja
                                </p>
                            @endif
                            <p class="text-xs text-void-gray mt-2">
                                Ongkos kirim: <span class="text-void-light font-semibold">
                                    Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                                </span>
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Info pembayaran --}}
                @if($order->payment)
                    <div class="bg-void-card border border-void-border rounded-2xl p-5">
                        <h3 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase mb-4">
                            Info Pembayaran
                        </h3>
                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-void-gray">Metode</span>
                                <span class="text-void-light capitalize font-medium">
                                    {{ $order->payment->method
                                        ? str_replace('_', ' ', $order->payment->method)
                                        : '—' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-void-gray">Status</span>
                                @php
                                    $pStatusColor = [
                                        'paid'    => 'text-green-400',
                                        'pending' => 'text-yellow-400',
                                        'unpaid'  => 'text-void-gray',
                                        'failed'  => 'text-red-400',
                                    ][$order->payment->status] ?? 'text-void-gray';
                                @endphp
                                <span class="font-bold capitalize {{ $pStatusColor }}">
                                    {{ $order->payment->status }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-void-gray">Jumlah</span>
                                <span class="text-void-accent font-black">
                                    Rp {{ number_format($order->payment->amount ?? $order->total_price, 0, ',', '.') }}
                                </span>
                            </div>
                            @if($order->payment->paid_at)
                                <div class="flex justify-between items-center">
                                    <span class="text-void-gray">Dibayar pada</span>
                                    <span class="text-void-light">
                                        {{ $order->payment->paid_at->format('d M Y H:i') }}
                                    </span>
                                </div>
                            @endif
                            @if($order->payment->midtrans_transaction_id)
                                <div class="flex flex-col gap-0.5 pt-2 border-t border-void-border">
                                    <span class="text-void-gray">Transaction ID</span>
                                    <span class="text-void-muted font-mono text-[10px] break-all">
                                        {{ $order->payment->midtrans_transaction_id }}
                                    </span>
                                </div>
                            @endif
                            @if($order->payment->proof_image_url)
                                <div class="pt-2 border-t border-void-border">
                                    <a href="{{ $order->payment->proof_image_url }}" target="_blank"
                                       class="flex items-center gap-1.5 text-void-gray hover:text-void-accent transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Lihat Bukti Transfer
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="space-y-3">
                    @if($order->status === 'pending')
                        <a href="{{ route('payment.show', $order->order_code) }}"
                           class="btn-primary w-full text-center py-3 block text-sm">
                            Selesaikan Pembayaran
                        </a>
                    @endif
                    <a href="{{ route('orders.index') }}"
                       class="btn-secondary w-full text-center py-3 block text-sm">
                        Semua Pesanan
                    </a>
                    <a href="{{ route('products.index') }}"
                       class="w-full text-center py-2.5 block text-sm text-void-gray
                              hover:text-void-accent transition-colors">
                        Lanjut Belanja →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection