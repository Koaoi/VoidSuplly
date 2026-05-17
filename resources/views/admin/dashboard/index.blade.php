@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang, ' . auth()->user()->name)

@section('content')
{{-- ── Stat Cards ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'Total Customer', 'value' => number_format($stats['total_users']), 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'blue'],
        ['label' => 'Total Produk', 'value' => number_format($stats['total_products']), 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'purple'],
        ['label' => 'Total Order', 'value' => number_format($stats['total_orders']), 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'yellow'],
        ['label' => 'Total Revenue', 'value' => 'Rp ' . number_format($stats['total_revenue'], 0, ',', '.'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'green'],
    ] as $card)
        @php
            $colors = [
                'blue' => ['bg' => 'bg-blue-500/10', 'border' => 'border-blue-500/20', 'text' => 'text-blue-400'],
                'purple' => ['bg' => 'bg-purple-500/10', 'border' => 'border-purple-500/20', 'text' => 'text-purple-400'],
                'yellow' => ['bg' => 'bg-yellow-500/10', 'border' => 'border-yellow-500/20', 'text' => 'text-yellow-400'],
                'green' => ['bg' => 'bg-green-500/10', 'border' => 'border-green-500/20', 'text' => 'text-green-400'],
            ];
            $c = $colors[$card['color']];
        @endphp
        <div class="bg-void-card border border-void-border rounded-2xl p-5 hover:border-void-muted transition-all duration-300">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs text-void-gray font-medium uppercase tracking-wider">{{ $card['label'] }}</p>
                <div class="{{ $c['bg'] }} {{ $c['border'] }} border rounded-xl p-2">
                    <svg class="w-4 h-4 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-void-white">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

{{-- ── Alert Cards ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
       class="flex items-center gap-4 bg-yellow-500/10 border border-yellow-500/30 rounded-2xl p-4 hover:border-yellow-500/60 transition-all duration-300 group">
        <div class="w-10 h-10 rounded-xl bg-yellow-500/20 flex items-center justify-center shrink-0 group-hover:bg-yellow-500/30 transition-all">
            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-yellow-400">{{ $stats['pending_orders'] }}</p>
            <p class="text-xs text-void-gray font-medium">Order Menunggu Bayar</p>
        </div>
    </a>

    <a href="{{ route('admin.commissions.index', ['status' => 'pending']) }}"
       class="flex items-center gap-4 bg-blue-500/10 border border-blue-500/30 rounded-2xl p-4 hover:border-blue-500/60 transition-all duration-300 group">
        <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center shrink-0 group-hover:bg-blue-500/30 transition-all">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-blue-400">{{ $stats['pending_commissions'] }}</p>
            <p class="text-xs text-void-gray font-medium">Commission Pending</p>
        </div>
    </a>

    <a href="{{ route('admin.products.index') }}"
       class="flex items-center gap-4 bg-red-500/10 border border-red-500/30 rounded-2xl p-4 hover:border-red-500/60 transition-all duration-300 group">
        <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center shrink-0 group-hover:bg-red-500/30 transition-all">
            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-red-400">{{ $stats['low_stock'] }}</p>
            <p class="text-xs text-void-gray font-medium">Produk Stok Rendah (≤5)</p>
        </div>
    </a>
</div>

{{-- ── Revenue Chart + Order Status ─────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Revenue Chart --}}
    <div class="lg:col-span-2 bg-void-card border border-void-border rounded-2xl p-6">
        <h2 class="text-sm font-bold text-void-white uppercase tracking-wider mb-6">Revenue 7 Hari Terakhir</h2>
        <div class="flex items-end gap-2 h-40" 
             x-data="{
                 values: {{ json_encode($chartValues) }},
                 days: {{ json_encode($chartDays) }},
                 max: Math.max(...{{ json_encode($chartValues) }}) || 1,
                 fmt(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(v); }
             }">
            <template x-for="(v, i) in values" :key="i">
                <div class="flex flex-col items-center gap-1 flex-1 group">
                    <p class="text-[9px] text-void-gray opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap" 
                       x-text="fmt(v)"></p>
                    <div class="w-full bg-void-dark rounded-t-lg overflow-hidden relative" style="height: 100px">
                        <div :style="'height: ' + Math.round((v / max) * 100) + '%'" 
                             class="absolute bottom-0 w-full bg-white/80 rounded-t-lg transition-all duration-700"></div>
                    </div>
                    <p class="text-[9px] text-void-gray" x-text="days[i]"></p>
                </div>
            </template>
        </div>
    </div>

    {{-- Order by status --}}
    <div class="bg-void-card border border-void-border rounded-2xl p-6">
        <h2 class="text-sm font-bold text-void-white uppercase tracking-wider mb-5">Status Order</h2>
        <div class="space-y-3">
            @php
                $total = array_sum($ordersByStatus);
                $statusColors = [
                    'pending' => 'bg-yellow-400',
                    'paid' => 'bg-blue-400',
                    'processing' => 'bg-purple-400',
                    'shipped' => 'bg-orange-400',
                    'completed' => 'bg-green-400',
                    'cancelled' => 'bg-red-400',
                ];
                $statusLabels = [
                    'pending' => 'Pending',
                    'paid' => 'Dibayar',
                    'processing' => 'Diproses',
                    'shipped' => 'Dikirim',
                    'completed' => 'Selesai',
                    'cancelled' => 'Batal',
                ];
            @endphp
            @foreach($statusLabels as $key => $label)
                @php
                    $count = $ordersByStatus[$key] ?? 0;
                    $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-void-gray">{{ $label }}</span>
                        <span class="text-void-light font-semibold">{{ $count }}</span>
                    </div>
                    <div class="w-full h-1.5 bg-void-dark rounded-full overflow-hidden">
                        <div class="{{ $statusColors[$key] }} h-full rounded-full transition-all duration-700" 
                             style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Recent Orders + Low Stock + Recent Commissions ───────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Recent Orders --}}
    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-void-border">
            <h2 class="text-sm font-bold text-void-white uppercase tracking-wider">Order Terbaru</h2>
            <a href="{{ route('admin.orders.index') }}" 
               class="text-xs text-void-gray hover:text-void-accent transition-colors">
                Lihat semua →
            </a>
        </div>
        <div class="divide-y divide-void-border">
            @forelse($recentOrders as $order)
                @php
                    $statusTextColor = [
                        'pending' => 'text-yellow-400',
                        'paid' => 'text-blue-400',
                        'processing' => 'text-purple-400',
                        'shipped' => 'text-orange-400',
                        'completed' => 'text-green-400',
                        'cancelled' => 'text-red-400',
                    ];
                @endphp
                <a href="{{ route('admin.orders.show', $order) }}" 
                   class="flex items-center justify-between px-5 py-3 hover:bg-void-muted/20 transition-colors">
                    <div>
                        <p class="text-xs font-bold text-void-white">{{ $order->order_code }}</p>
                        <p class="text-[10px] text-void-gray mt-0.5">
                            {{ $order->user->name }} · {{ $order->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-black text-void-accent">{{ $order->formatted_total }}</p>
                        <p class="text-[10px] font-semibold {{ $statusTextColor[$order->status] ?? 'text-void-gray' }} capitalize">
                            {{ $order->status_label }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="text-center py-8">
                    <p class="text-sm text-void-gray">Belum ada order.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right Column --}}
    <div class="space-y-4">
        {{-- Low Stock Products --}}
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-void-border">
                <h2 class="text-sm font-bold text-void-white uppercase tracking-wider">Stok Rendah</h2>
                <a href="{{ route('admin.products.index') }}" 
                   class="text-xs text-void-gray hover:text-void-accent transition-colors">
                    Lihat semua →
                </a>
            </div>
            <div class="divide-y divide-void-border">
                @forelse($lowStockProducts as $product)
                    <a href="{{ route('admin.products.edit', $product) }}" 
                       class="flex items-center justify-between px-5 py-3 hover:bg-void-muted/20 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg overflow-hidden bg-void-dark shrink-0">
                                <img src="{{ $product->primary_image_url }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-void-white line-clamp-1">{{ $product->name }}</p>
                                <p class="text-[10px] text-void-gray mt-0.5">{{ $product->category->name ?? '—' }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-black {{ $product->stock <= 2 ? 'text-red-400' : 'text-orange-400' }}">
                            {{ $product->stock }} pcs
                        </span>
                    </a>
                @empty
                    <div class="text-center py-6">
                        <p class="text-sm text-void-gray">Semua stok aman.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Commissions --}}
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-void-border">
                <h2 class="text-sm font-bold text-void-white uppercase tracking-wider">Commission Terbaru</h2>
                <a href="{{ route('admin.commissions.index') }}" 
                   class="text-xs text-void-gray hover:text-void-accent transition-colors">
                    Lihat semua →
                </a>
            </div>
            <div class="divide-y divide-void-border">
                @forelse($recentCommissions as $commission)
                    @php
                        $commissionColors = [
                            'pending' => 'text-yellow-400',
                            'reviewing' => 'text-blue-400',
                            'accepted' => 'text-purple-400',
                            'in_progress' => 'text-orange-400',
                            'completed' => 'text-green-400',
                            'rejected' => 'text-red-400',
                        ];
                    @endphp
                    <a href="{{ route('admin.commissions.show', $commission) }}" 
                       class="flex items-center justify-between px-5 py-3 hover:bg-void-muted/20 transition-colors">
                        <div>
                            <p class="text-xs font-semibold text-void-white line-clamp-1">{{ $commission->title }}</p>
                            <p class="text-[10px] text-void-gray mt-0.5">
                                {{ $commission->user->name }} · {{ $commission->product_type }}
                            </p>
                        </div>
                        <span class="text-[10px] font-bold {{ $commissionColors[$commission->status] ?? 'text-void-gray' }} capitalize">
                            {{ $commission->status_label }}
                        </span>
                    </a>
                @empty
                    <div class="text-center py-6">
                        <p class="text-sm text-void-gray">Belum ada commission.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection