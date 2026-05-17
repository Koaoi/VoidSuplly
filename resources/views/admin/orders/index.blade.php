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
                <th class="text-left px-5 py-3 hidden lg:table-cell">Produk</th>
                <th class="text-right px-5 py-3">Total</th>
                <th class="text-center px-5 py-3">Status</th>
                <th class="text-right px-5 py-3">Aksi</th>
            </tr>
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
                        <p class="text-void-white text-xs font-medium">{{ $order->user->name }}</p>
                        <p class="text-[10px] text-void-gray truncate max-w-[140px]">{{ $order->user->email }}</p>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <p class="text-void-gray text-xs line-clamp-1">
                            {{ $order->items->pluck('product_name')->join(', ') }}
                        </p>
                        <p class="text-[10px] text-void-muted mt-0.5">{{ $order->items->count() }} item</p>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <p class="text-void-accent font-black text-xs">{{ $order->formatted_total }}</p>
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
