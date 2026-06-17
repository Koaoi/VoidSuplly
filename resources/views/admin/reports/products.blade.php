@extends('layouts.admin')

@section('title', 'Laporan Produk')

@section('page-title', 'Laporan Produk')

@push('styles')
<style>
    /* ⭐ TOMBOL CETAK ⭐ */
    .btn-print {
        padding: 10px 24px !important;
        border-radius: 12px !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        height: 44px !important;
        cursor: pointer !important;
        border: none !important;
        background: white !important;
        color: black !important;
    }
    .btn-print:hover {
        background: #f0f0f0 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <p class="text-xs text-void-gray uppercase tracking-wider">Total Produk</p>
            <p class="text-2xl font-black text-void-white mt-1">{{ number_format($totalProducts) }}</p>
        </div>
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <p class="text-xs text-void-gray uppercase tracking-wider">Total Stok</p>
            <p class="text-2xl font-black text-void-white mt-1">{{ number_format($totalStock) }}</p>
        </div>
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <p class="text-xs text-void-gray uppercase tracking-wider">Nilai Inventaris</p>
            <p class="text-2xl font-black text-green-400 mt-1">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <p class="text-xs text-void-gray uppercase tracking-wider">Stok Menipis</p>
            <p class="text-2xl font-black text-yellow-400 mt-1">{{ number_format($lowStockProducts) }}</p>
        </div>
    </div>

    {{-- Products Table --}}
    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-void-border flex justify-between items-center">
            <h3 class="text-sm font-bold text-void-white uppercase tracking-wider">Daftar Produk</h3>
            
            {{-- ⭐ TOMBOL CETAK DENGAN PREVIEW ⭐ --}}
            <button onclick="printReport()" class="btn-print">🖨️ Cetak Laporan</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-void-border bg-void-dark/30">
                        <th class="text-left px-6 py-3 text-void-gray font-medium">Produk</th>
                        <th class="text-left px-6 py-3 text-void-gray font-medium">Kategori</th>
                        <th class="text-right px-6 py-3 text-void-gray font-medium">Harga</th>
                        <th class="text-center px-6 py-3 text-void-gray font-medium">Stok</th>
                        <th class="text-center px-6 py-3 text-void-gray font-medium">Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="border-b border-void-border/50 hover:bg-void-muted/10">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-void-dark">
                                        <img src="{{ $product->primary_image_url ?? asset('images/placeholder.jpg') }}" 
                                             class="w-full h-full object-cover"
                                             alt="{{ $product->name }}">
                                    </div>
                                    <span class="font-medium text-void-white">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3">{{ $product->category->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-right">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-center">
                                @php
                                    $stockClass = 'bg-green-500/20 text-green-400';
                                    if ($product->stock <= 5) {
                                        $stockClass = 'bg-red-500/20 text-red-400';
                                    } elseif ($product->stock <= 10) {
                                        $stockClass = 'bg-yellow-500/20 text-yellow-400';
                                    }
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $stockClass }}">
                                    {{ number_format($product->stock) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">{{ number_format($product->total_sold ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-void-gray">
                                Tidak ada data produk
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-void-border">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ⭐ SCRIPT UNTUK PRINT PREVIEW ⭐ --}}
@push('scripts')
<script>
function printReport() {
    window.print();
}
</script>

<style media="print">
    header, 
    button[onclick="printReport()"],
    .no-print {
        display: none !important;
    }
    
    body, 
    .bg-void-card, 
    .bg-void-dark,
    .bg-void-black {
        background: white !important;
        color: black !important;
    }
    
    .border-void-border {
        border-color: #ccc !important;
    }
    
    .text-void-white,
    .text-void-light,
    .text-void-gray {
        color: black !important;
    }
    
    .bg-void-card {
        background: white !important;
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .bg-green-500\/20 { background: #d1fae5 !important; color: #065f46 !important; }
    .bg-red-500\/20 { background: #fee2e2 !important; color: #991b1b !important; }
    .bg-yellow-500\/20 { background: #fef3c7 !important; color: #92400e !important; }
    
    img {
        display: block !important;
        max-width: 40px !important;
    }
    
    * {
        color: black !important;
    }
    
    .backdrop-blur-md {
        backdrop-filter: none !important;
        background: white !important;
    }
    
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    
    th, td {
        border-bottom: 1px solid #ddd !important;
        padding: 8px 12px !important;
    }
    
    th {
        background: #f5f5f5 !important;
        font-weight: bold !important;
    }
</style>
@endpush
@endsection