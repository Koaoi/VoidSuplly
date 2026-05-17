@extends('layouts.app')

@section('title', 'Riwayat Pesanan')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">
                — Akun Saya
            </p>
            <h1 class="text-3xl font-black text-void-accent">Riwayat Pesanan</h1>
        </div>

        {{-- Status filter tabs --}}
        @php
            $tabStatuses = [
                ''           => 'Semua',
                'pending'    => 'Menunggu Bayar',
                'paid'       => 'Dibayar',
                'processing' => 'Diproses',
                'shipped'    => 'Dikirim',
                'completed'  => 'Selesai',
                'cancelled'  => 'Dibatalkan',
            ];
            $statusColors = [
                'pending'    => 'yellow',
                'paid'       => 'blue',
                'processing' => 'purple',
                'shipped'    => 'orange',
                'completed'  => 'green',
                'cancelled'  => 'red',
            ];
        @endphp

        <div class="flex items-center gap-2 flex-wrap mb-8 pb-4 border-b border-void-border">
            @foreach($tabStatuses as $val => $label)
                @php $count = $statusCounts[$val] ?? 0; @endphp
                <a href="{{ route('orders.index', $val ? ['status' => $val] : []) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs
                          font-semibold transition-all whitespace-nowrap
                          {{ request('status', '') === $val
                              ? 'bg-white text-black'
                              : 'bg-void-card border border-void-border text-void-gray hover:border-void-muted hover:text-void-light' }}">
                    {{ $label }}
                    @if($val && $count > 0)
                        <span class="tabular-nums opacity-70">({{ $count }})</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Order list --}}
        @if($orders->isNotEmpty())
            <div class="space-y-4">
                @foreach($orders as $order)
                    @php
                        $color = $statusColors[$order->status] ?? 'gray';
                        $colorMap = [
                            'yellow' => 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
                            'blue'   => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
                            'purple' => 'bg-purple-500/10 border-purple-500/30 text-purple-400',
                            'orange' => 'bg-orange-500/10 border-orange-500/30 text-orange-400',
                            'green'  => 'bg-green-500/10 border-green-500/30 text-green-400',
                            'red'    => 'bg-red-500/10 border-red-500/30 text-red-400',
                            'gray'   => 'bg-void-muted/30 border-void-border text-void-gray',
                        ];
                        $badgeClass = $colorMap[$color];
                    @endphp

                    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden
                                hover:border-void-muted transition-colors duration-200">

                        {{-- Order header --}}
                        <div class="flex flex-wrap items-center justify-between gap-3
                                    px-5 py-4 border-b border-void-border">
                            <div class="flex flex-wrap items-center gap-4">
                                <div>
                                    <p class="text-[10px] text-void-gray uppercase tracking-wider">Kode Pesanan</p>
                                    <p class="text-sm font-black text-void-accent tracking-wide mt-0.5">
                                        {{ $order->order_code }}
                                    </p>
                                </div>
                                <div class="hidden sm:block w-px h-8 bg-void-border"></div>
                                <div class="hidden sm:block">
                                    <p class="text-[10px] text-void-gray uppercase tracking-wider">Tanggal</p>
                                    <p class="text-sm text-void-light mt-0.5">
                                        {{ $order->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <div class="hidden sm:block w-px h-8 bg-void-border"></div>
                                <div class="hidden sm:block">
                                    <p class="text-[10px] text-void-gray uppercase tracking-wider">Total</p>
                                    <p class="text-sm font-black text-void-white mt-0.5">
                                        {{ $order->formatted_total }}
                                    </p>
                                </div>
                            </div>

                            {{-- Status badge --}}
                            <span class="inline-flex items-center text-[10px] font-bold tracking-widest
                                         uppercase border px-3 py-1.5 rounded-full {{ $badgeClass }}">
                                {{ $order->status_label }}
                            </span>
                        </div>

                        {{-- Items preview --}}
                        <div class="px-5 py-4">
                            <div class="flex items-center gap-3 overflow-x-auto pb-1">
                                @foreach($order->items->take(5) as $item)
                                    <a href="{{ $item->product ? route('products.show', $item->product->slug) : '#' }}"
                                       class="relative shrink-0 group/img">
                                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-void-dark
                                                    border border-void-border group-hover/img:border-void-muted
                                                    transition-colors">
                                            <img src="{{ $item->product?->primary_image_url ?? asset('images/product-placeholder.png') }}"
                                                 alt="{{ $item->product_name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        @if($item->quantity > 1)
                                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-white text-black
                                                         text-[9px] font-black rounded-full flex items-center
                                                         justify-center">
                                                {{ $item->quantity }}
                                            </span>
                                        @endif
                                    </a>
                                @endforeach

                                @if($order->items->count() > 5)
                                    <div class="w-14 h-14 rounded-xl bg-void-dark border border-void-border
                                                flex items-center justify-center shrink-0">
                                        <span class="text-xs text-void-gray font-bold">
                                            +{{ $order->items->count() - 5 }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Mobile: total & item count --}}
                                <div class="ml-auto shrink-0 sm:hidden text-right">
                                    <p class="text-[10px] text-void-gray">Total</p>
                                    <p class="text-sm font-black text-void-accent">
                                        {{ $order->formatted_total }}
                                    </p>
                                </div>
                            </div>

                            {{-- Item names --}}
                            <p class="text-xs text-void-gray mt-2 line-clamp-1">
                                {{ $order->items->pluck('product_name')->join(', ') }}
                            </p>
                        </div>

                        {{-- Footer actions --}}
                        <div class="px-5 py-3 border-t border-void-border bg-void-darker
                                    flex flex-wrap items-center gap-3">

                            <a href="{{ route('orders.show', $order->order_code) }}"
                               class="text-xs font-semibold text-void-light hover:text-void-accent
                                      transition-colors flex items-center gap-1.5">
                                Detail Pesanan
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            @if($order->status === 'pending')
                                <a href="{{ route('payment.show', $order->order_code) }}"
                                   class="text-xs font-bold text-yellow-400 hover:text-yellow-300
                                          transition-colors flex items-center gap-1.5 ml-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Bayar Sekarang
                                </a>
                            @endif

                            {{-- Kurir info --}}
                            @if($order->shippingAddress)
                                <span class="ml-auto text-[10px] text-void-muted">
                                    {{ $order->shippingAddress->courier }}
                                    {{ $order->shippingAddress->service }}
                                    @if($order->shippingAddress->estimated_days)
                                        · Est. {{ $order->shippingAddress->estimated_days }} hari
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $orders->links('components.pagination') }}
            </div>

        @else
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-24 h-24 rounded-2xl bg-void-card border border-void-border
                            flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-void-white mb-2">Belum ada pesanan</h2>
                <p class="text-sm text-void-gray mb-8 max-w-xs leading-relaxed">
                    @if(request()->filled('status'))
                        Tidak ada pesanan dengan status ini.
                    @else
                        Kamu belum pernah melakukan pemesanan. Yuk mulai belanja!
                    @endif
                </p>
                <div class="flex gap-3">
                    @if(request()->filled('status'))
                        <a href="{{ route('orders.index') }}" class="btn-secondary text-sm">
                            Lihat Semua
                        </a>
                    @endif
                    <a href="{{ route('products.index') }}" class="btn-primary text-sm">
                        Mulai Belanja
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection