@extends('layouts.admin')
@section('title','Manajemen Pesanan')
@section('page-title','Pesanan')

@section('content')
{{-- Filter bar --}}
<form method="GET" action="{{ route('admin.orders.index') }}"
      class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Kode order / nama customer..." class="input-void w-64 text-sm">
    <select name="status" class="input-void w-44 text-sm cursor-pointer">
        <option value="">Semua Status</option>
        @foreach(['pending'=>'Pending','paid'=>'Paid','processing'=>'Processing','shipped'=>'Shipped','completed'=>'Completed','cancelled'=>'Cancelled'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')===$v ? 'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-primary text-sm px-5">Filter</button>
    <a href="{{ route('admin.orders.index') }}" class="btn-secondary text-sm px-5">Reset</a>
</form>

{{-- Status tabs --}}
<div class="flex flex-wrap gap-2 mb-5">
    @php
        $tabList = [''=> 'Semua','pending'=>'Pending','paid'=>'Paid','processing'=>'Processing','shipped'=>'Shipped','completed'=>'Selesai','cancelled'=>'Batal'];
        $tabColors = ['pending'=>'yellow','paid'=>'blue','processing'=>'purple','shipped'=>'orange','completed'=>'green','cancelled'=>'red'];
    @endphp
    @foreach($tabList as $v=>$l)
        <a href="{{ route('admin.orders.index', array_merge(request()->except('status','page'), $v ? ['status'=>$v] : [])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all
                  {{ request('status','')===$v ? 'bg-white text-black' : 'bg-void-card border border-void-border text-void-gray hover:border-void-muted' }}">
            {{ $l }}
            @if($v && isset($statusCounts[$v]))
                <span class="tabular-nums opacity-70">({{ $statusCounts[$v] }})</span>
            @endif
        </a>
    @endforeach
</div>

<div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-void-border text-xs font-bold text-void-gray uppercase tracking-wider">
                <th class="text-left px-5 py-3">Order</th>
                <th class="text-left px-5 py-3 hidden sm:table-cell">Customer</th>
                <th class="text-left px-5 py-3 hidden lg:table-cell">Produk / Komisi</th>
                <th class="text-right px-5 py-3">Total</th>
                <th class="text-center px-5 py-3">Status</th>
                <th class="text-right px-5 py-3">Aksi</th>
            </td>
        </thead>
        <tbody class="divide-y divide-void-border">
            @forelse($orders as $order)
                @php
                    $bc=['pending'=>'text-yellow-400 bg-yellow-500/10 border-yellow-500/30',
                         'paid'=>'text-blue-400 bg-blue-500/10 border-blue-500/30',
                         'processing'=>'text-purple-400 bg-purple-500/10 border-purple-500/30',
                         'shipped'=>'text-orange-400 bg-orange-500/10 border-orange-500/30',
                         'completed'=>'text-green-400 bg-green-500/10 border-green-500/30',
                         'cancelled'=>'text-red-400 bg-red-500/10 border-red-500/30'];
                @endphp
                <tr class="hover:bg-void-muted/10 transition-colors">
                    <td class="px-5 py-4">
                        <p class="font-black text-void-accent text-xs tracking-wide">{{ $order->order_code }}</p>
                        <p class="text-[10px] text-void-gray mt-0.5">{{ $order->created_at->format('d M Y H:i') }}</p>
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell">
                        <div class="flex items-center gap-2">
                            {{-- Avatar Customer --}}
                            <div class="w-7 h-7 rounded-full overflow-hidden bg-void-dark shrink-0">
                                @if($order->user->avatar_url)
                                    <img src="{{ $order->user->avatar_url }}" 
                                         alt="{{ $order->user->name }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[10px] font-bold text-void-gray">
                                        {{ strtoupper(substr($order->user->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-void-white text-xs font-medium">{{ $order->user->name }}</p>
                                <p class="text-[10px] text-void-gray truncate max-w-[140px]">{{ $order->user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        {{-- PRODUK DENGAN GAMBAR + KOMISI --}}
                        <div class="flex flex-col gap-3">
                            {{-- Produk Items --}}
                            @foreach($order->items->take(2) as $item)
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-md overflow-hidden bg-void-dark shrink-0">
                                        @if($item->product && $item->product->primary_image_url)
                                            <img src="{{ $item->product->primary_image_url }}" 
                                                 alt="{{ $item->product_name }}"
                                                 class="w-full h-full object-cover"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/placeholder.jpg') }}';">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-void-gray text-xs line-clamp-1">{{ $item->product_name }}</p>
                                        <p class="text-[9px] text-void-muted">{{ $item->quantity }} x {{ $item->formatted_price }}</p>
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- GAMBAR KOMISI / COMMISSION --}}
                            @if($order->commission && $order->commission->count() > 0)
                                @foreach($order->commission->take(2) as $commission)
                                    <div class="flex items-center gap-2 pt-1 border-t border-void-border/30">
                                        {{-- Icon/Gambar Komisi --}}
                                        <div class="w-8 h-8 rounded-md overflow-hidden bg-purple-500/10 shrink-0 flex items-center justify-center">
                                            @if($commission->image_url)
                                                <img src="{{ $commission->image_url }}" 
                                                     alt="{{ $commission->title }}"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.onerror=null; this.src='{{ asset('images/commission-placeholder.png') }}';">
                                            @else
                                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-void-gray text-xs line-clamp-1">
                                                <span class="text-purple-400">Komisi:</span> {{ $commission->title }}
                                            </p>
                                            <p class="text-[9px] text-void-muted">
                                                {{ $commission->pivot->quantity ?? 1 }} x Rp {{ number_format($commission->commission_rate ?? 0, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                                @if($order->commission->count() > 2)
                                    <p class="text-[10px] text-void-muted">+{{ $order->commission->count() - 2 }} komisi lainnya</p>
                                @endif
                            @endif
                            
                            @if($order->items->count() > 2 && $order->commission->count() == 0)
                                <p class="text-[10px] text-void-muted">+{{ $order->items->count() - 2 }} item lainnya</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <p class="text-void-accent font-black text-xs">{{ $order->formatted_total }}</p>
                        @if($order->commission_total > 0)
                            <p class="text-[9px] text-purple-400">Komisi: Rp {{ number_format($order->commission_total, 0, ',', '.') }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-[10px] font-bold border px-2.5 py-1 rounded-full {{ $bc[$order->status] ?? '' }}">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('admin.orders.show',$order) }}"
                           class="text-xs text-void-gray hover:text-void-accent transition-colors
                                  px-3 py-1.5 border border-void-border rounded-lg hover:border-void-muted">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-void-gray">Tidak ada order.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($orders->hasPages())
        <div class="px-5 py-4 border-t border-void-border">
            {{ $orders->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection