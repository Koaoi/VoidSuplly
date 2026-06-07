@extends('layouts.app')

@section('title', 'Cara Order - VOID Supply')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">Cara Order</h1>
            <div class="w-20 h-0.5 bg-void-accent mx-auto mt-4"></div>
        </div>
        
        <div class="space-y-6">
            <div class="bg-void-card border border-void-border rounded-2xl p-6 flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-void-accent/20 flex items-center justify-center shrink-0">
                    <span class="text-lg font-black text-void-accent">1</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-void-white mb-1">Pilih Produk</h3>
                    <p class="text-void-gray">Browse koleksi kami dan pilih produk yang kamu suka</p>
                </div>
            </div>
            <div class="bg-void-card border border-void-border rounded-2xl p-6 flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-void-accent/20 flex items-center justify-center shrink-0">
                    <span class="text-lg font-black text-void-accent">2</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-void-white mb-1">Tambahkan ke Keranjang</h3>
                    <p class="text-void-gray">Pilih ukuran dan quantity, lalu klik "Tambah ke Keranjang"</p>
                </div>
            </div>
            <div class="bg-void-card border border-void-border rounded-2xl p-6 flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-void-accent/20 flex items-center justify-center shrink-0">
                    <span class="text-lg font-black text-void-accent">3</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-void-white mb-1">Checkout</h3>
                    <p class="text-void-gray">Lengkapi data pengiriman dan pilih metode pembayaran</p>
                </div>
            </div>
            <div class="bg-void-card border border-void-border rounded-2xl p-6 flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-void-accent/20 flex items-center justify-center shrink-0">
                    <span class="text-lg font-black text-void-accent">4</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-void-white mb-1">Selesaikan Pembayaran</h3>
                    <p class="text-void-gray">Bayar sesuai instruksi, pesanan akan diproses</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection