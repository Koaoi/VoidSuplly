@extends('layouts.app')

@section('title', 'Pembayaran — ' . $order->order_code)

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Success header --}}
        <div class="text-center mb-10">
            <div class="w-16 h-16 rounded-2xl bg-green-500/10 border border-green-500/30
                        flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-void-white">Order Berhasil Dibuat!</h1>
            <p class="text-void-gray text-sm mt-2">
                Kode Order: <span class="font-bold text-void-accent">{{ $order->order_code }}</span>
            </p>
        </div>

        {{-- Order summary --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-6 mb-6">
            <h2 class="text-sm font-bold text-void-white uppercase tracking-wider mb-4">Ringkasan Pesanan</h2>

            <div class="space-y-3 mb-4">
                @foreach($order->items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-void-gray">
                            {{ $item->product_name }}
                            <span class="text-void-muted">({{ $item->size }} × {{ $item->quantity }})</span>
                        </span>
                        <span class="text-void-light">{{ $item->formatted_subtotal }}</span>
                    </div>
                @endforeach
            </div>

            <div class="pt-3 border-t border-void-border space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-void-gray">Ongkos Kirim</span>
                    <span class="text-void-light">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-base font-bold">
                    <span class="text-void-white">Total Pembayaran</span>
                    <span class="text-void-accent">{{ $order->formatted_total }}</span>
                </div>
            </div>
        </div>

        {{-- Payment section — placeholder Fase 7 --}}
        <div class="bg-void-card border border-yellow-500/30 rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-3 mb-3">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm font-bold text-yellow-400">Menunggu Pembayaran</h2>
            </div>
            <p class="text-sm text-void-gray leading-relaxed">
                Integrasi Midtrans akan ditambahkan di Fase 7.
                Sementara ini catat kode order kamu:
                <strong class="text-void-accent">{{ $order->order_code }}</strong>
            </p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('home') }}" class="flex-1 btn-secondary text-center py-3">
                Kembali ke Toko
            </a>
            <a href="{{ route('home') }}" class="flex-1 btn-primary text-center py-3">
                Lihat Pesanan
            </a>
        </div>
    </div>
</div>
@endsection