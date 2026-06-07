@extends('layouts.app')

@section('title', 'Kebijakan Return - VOID Supply')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">Kebijakan Return</h1>
            <div class="w-20 h-0.5 bg-void-accent mx-auto mt-4"></div>
        </div>
        
        <div class="bg-void-card border border-void-border rounded-2xl p-8 space-y-4">
            <p class="text-void-gray">Karena produk VOID Supply adalah <strong class="text-void-accent">limited edition</strong>, kami tidak menerima return atau refund kecuali:</p>
            <ul class="list-disc list-inside text-void-gray space-y-2 ml-4">
                <li>Produk mengalami kerusakan produksi (cacat)</li>
                <li>Produk yang diterima tidak sesuai dengan yang dipesan</li>
                <li>Pengiriman salah barang</li>
            </ul>
            <p class="text-void-gray mt-4">Untuk pengajuan return, hubungi customer service kami maksimal 3x24 jam setelah barang diterima dengan menyertakan foto/video bukti.</p>
            <div class="mt-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-xl">
                <p class="text-xs text-yellow-400">⚠️ Tidak ada return karena salah ukuran. Silakan cek size guide sebelum membeli.</p>
            </div>
        </div>
    </div>
</div>
@endsection