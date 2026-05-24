@extends('layouts.admin')

@section('title', 'Laporan')

@section('page-title', 'Laporan')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    {{-- Laporan Penjualan --}}
    <div class="bg-void-card border border-void-border rounded-2xl p-6 hover:border-void-accent transition-all">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-void-white">Laporan Penjualan</h3>
                <p class="text-xs text-void-gray">Lihat statistik penjualan, produk terlaris, dan pendapatan</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.reports.sales') }}" class="flex-1 btn-primary text-center py-2 text-sm">
                Lihat Laporan
            </a>
            <a href="{{ route('admin.reports.sales') }}?print=1" class="flex-1 btn-secondary text-center py-2 text-sm">
                Cetak PDF
            </a>
        </div>
    </div>

    {{-- Laporan Produk --}}
    <div class="bg-void-card border border-void-border rounded-2xl p-6 hover:border-void-accent transition-all">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-void-white">Laporan Produk</h3>
                <p class="text-xs text-void-gray">Lihat daftar produk, stok, dan nilai inventaris</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.reports.products') }}" class="flex-1 btn-primary text-center py-2 text-sm">
                Lihat Laporan
            </a>
            <a href="{{ route('admin.reports.products') }}?print=1" class="flex-1 btn-secondary text-center py-2 text-sm">
                Cetak PDF
            </a>
        </div>
    </div>

</div>
@endsection