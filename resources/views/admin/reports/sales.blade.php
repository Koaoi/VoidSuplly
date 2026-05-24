@extends('layouts.admin')

@section('title', 'Laporan Penjualan')

@section('page-title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">
    
    {{-- Filter Form --}}
    <div class="bg-void-card border border-void-border rounded-2xl p-6">
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-void-gray mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-01')) }}" 
                       class="input-void w-48">
            </div>
            <div>
                <label class="block text-xs text-void-gray mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-t')) }}" 
                       class="input-void w-48">
            </div>
            <div>
                <button type="submit" class="btn-primary px-6 py-2.5">Filter</button>
            </div>
            @if(request('start_date') || request('end_date'))
                <div>
                    <a href="{{ route('admin.reports.sales') }}" class="btn-secondary px-6 py-2.5">Reset</a>
                </div>
            @endif
            <div>
                <a href="{{ route('admin.reports.print-sales', request()->all()) }}" target="_blank" 
                   class="bg-white text-black px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-void-light transition">
                    Cetak PDF
                </a>
            </div>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <p class="text-xs text-void-gray uppercase tracking-wider">Total Pesanan</p>
            <p class="text-2xl font-black text-void-white mt-1">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <p class="text-xs text-void-gray uppercase tracking-wider">Total Pendapatan</p>
            <p class="text-2xl font-black text-green-400 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <p class="text-xs text-void-gray uppercase tracking-wider">Total Produk Terjual</p>
            <p class="text-2xl font-black text-void-white mt-1">{{ number_format($totalProducts) }}</p>
        </div>
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <p class="text-xs text-void-gray uppercase tracking-wider">Rata-rata per Order</p>
            <p class="text-2xl font-black text-void-white mt-1">Rp {{ number_format($averageOrder, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Best Sellers --}}
    <div class="bg-void-card border border-void-border rounded-2xl p-6">
        <h3 class="text-sm font-bold text-void-white uppercase tracking-wider mb-4">Produk Terlaris</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-void-border">
                        <th class="text-left py-3 text-void-gray font-medium">Produk</th>
                        <th class="text-center py-3 text-void-gray font-medium">Terjual</th>
                        <th class="text-right py-3 text-void-gray font-medium">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bestSellers as $item)
                        <tr class="border-b border-void-border/50">
                            <td class="py-3">{{ $item->product->name ?? 'Produk Dihapus' }}</td>
                            <td class="py-3 text-center">{{ number_format($item->total_qty) }}</td>
                            <td class="py-3 text-right">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-void-gray">Belum ada data penjualan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-void-border">
            <h3 class="text-sm font-bold text-void-white uppercase tracking-wider">Daftar Pesanan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-void-border bg-void-dark/30">
                        <th class="text-left px-6 py-3 text-void-gray font-medium">Kode Order</th>
                        <th class="text-left px-6 py-3 text-void-gray font-medium">Customer</th>
                        <th class="text-center px-6 py-3 text-void-gray font-medium">Item</th>
                        <th class="text-right px-6 py-3 text-void-gray font-medium">Total</th>
                        <th class="text-center px-6 py-3 text-void-gray font-medium">Status</th>
                        <th class="text-center px-6 py-3 text-void-gray font-medium">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="border-b border-void-border/50 hover:bg-void-muted/10">
                            <td class="px-6 py-3 font-mono text-xs">{{ $order->order_code }}</td>
                            <td class="px-6 py-3">{{ $order->user->name }}</td>
                            <td class="px-6 py-3 text-center">{{ number_format($order->items->sum('quantity')) }}</td>
                            <td class="px-6 py-3 text-right font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-center">
                                @php
                                    $statusClass = 'bg-yellow-500/20 text-yellow-400';
                                    if ($order->status === 'completed') $statusClass = 'bg-green-500/20 text-green-400';
                                    elseif ($order->status === 'paid') $statusClass = 'bg-blue-500/20 text-blue-400';
                                    elseif ($order->status === 'processing') $statusClass = 'bg-purple-500/20 text-purple-400';
                                    elseif ($order->status === 'shipped') $statusClass = 'bg-orange-500/20 text-orange-400';
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold capitalize {{ $statusClass }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-void-gray">Tidak ada data pesanan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-right text-xs text-void-gray">
        Periode: {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}
    </div>
</div>
@endsection