@extends('layouts.admin')
@section('title', 'Detail Order — ' . $order->order_code)
@section('page-title', 'Order — ' . $order->order_code)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Items --}}
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-void-border">
                <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">
                    Item ({{ $order->items->count() }})
                </h2>
            </div>
            <div class="divide-y divide-void-border">
                @foreach($order->items as $item)
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-void-dark shrink-0">
                            <img src="{{ $item->product?->primary_image_url ?? asset('images/product-placeholder.png') }}"
                                 class="w-full h-full object-cover"
                                 alt="{{ $item->product_name }}">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-void-white">{{ $item->product_name }}</p>
                            <p class="text-xs text-void-gray">Size {{ $item->size }} × {{ $item->quantity }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-black text-void-accent">{{ $item->formatted_subtotal }}</p>
                            <p class="text-[10px] text-void-gray">@ {{ $item->formatted_price }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="px-5 py-4 border-t border-void-border bg-void-dark/30 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-void-gray">Subtotal</span>
                    <span class="text-void-light">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-void-gray">Ongkos Kirim</span>
                    <span class="text-void-light">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-black text-base pt-2 border-t border-void-border">
                    <span class="text-void-white">Total</span>
                    <span class="text-void-accent">{{ $order->formatted_total }}</span>
                </div>
            </div>
        </div>

        {{-- Update Status --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-6">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Update Status</h2>
            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}"
                  class="flex flex-wrap items-end gap-3">
                @csrf 
                @method('PUT')
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-void-gray mb-2">Status Baru</label>
                    <select name="status" class="input-void cursor-pointer">
                        @foreach(['pending' => 'Pending', 'paid' => 'Paid', 'processing' => 'Processing', 'shipped' => 'Shipped', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $v => $l)
                            <option value="{{ $v }}" {{ $order->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary text-sm px-6 py-2.5">Update</button>
            </form>

            @if(in_array($order->status, ['pending', 'paid']) && $order->payment && $order->payment->proof_image)
                <div class="mt-4 pt-4 border-t border-void-border">
                    <form method="POST" action="{{ route('admin.orders.confirm', $order) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="text-sm font-bold bg-green-500/10 border border-green-500/30 text-green-400
                                       px-6 py-2.5 rounded-xl hover:bg-green-500/20 transition-colors">
                            ✓ Konfirmasi Pembayaran
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- RIGHT --}}
    <div class="space-y-5">

        {{-- Customer --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <h3 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Customer</h3>
            <div class="flex items-center gap-3 mb-3">
                <img src="{{ $order->user->avatar_url }}" 
                     class="w-10 h-10 rounded-full object-cover border border-void-border"
                     alt="{{ $order->user->name }}">
                <div>
                    <p class="text-sm font-bold text-void-white">{{ $order->user->name }}</p>
                    <p class="text-xs text-void-gray">{{ $order->user->email }}</p>
                </div>
            </div>
            <a href="{{ route('admin.users.show', $order->user) }}"
               class="text-xs text-void-gray hover:text-void-accent transition-colors">
                Lihat profil user →
            </a>
        </div>

        {{-- Shipping --}}
        @if($order->shippingAddress)
            <div class="bg-void-card border border-void-border rounded-2xl p-5">
                <h3 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Alamat Kirim</h3>
                @php $a = $order->shippingAddress; @endphp
                <div class="space-y-1.5 text-xs">
                    <p class="font-bold text-void-white text-sm">{{ $a->recipient_name }}</p>
                    <p class="text-void-gray">{{ $a->phone }}</p>
                    <p class="text-void-gray leading-relaxed mt-2">
                        {{ $a->address_detail }}, {{ $a->city }}, {{ $a->province }} {{ $a->postal_code }}
                    </p>
                    <div class="pt-3 border-t border-void-border">
                        <p class="font-semibold text-void-white">{{ $a->courier }} — {{ $a->service }}</p>
                        @if($a->estimated_days)
                            <p class="text-void-gray">Est. {{ $a->estimated_days }} hari kerja</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Payment --}}
        @if($order->payment)
            <div class="bg-void-card border border-void-border rounded-2xl p-5">
                <h3 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Pembayaran</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-void-gray">Metode</span>
                        <span class="text-void-light capitalize">{{ str_replace('_', ' ', $order->payment->method ?? '—') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-void-gray">Status</span>
                        <span class="{{ $order->payment->status === 'paid' ? 'text-green-400' : ($order->payment->status === 'pending' ? 'text-yellow-400' : 'text-red-400') }} font-bold capitalize">
                            {{ $order->payment->status }}
                        </span>
                    </div>
                    @if($order->payment->paid_at)
                        <div class="flex justify-between">
                            <span class="text-void-gray">Dibayar</span>
                            <span class="text-void-light">{{ $order->payment->paid_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    @if($order->payment->proof_image_url)
                        <div class="pt-2 border-t border-void-border">
                            <p class="text-void-gray mb-2">Bukti Transfer:</p>
                            <a href="{{ $order->payment->proof_image_url }}" target="_blank">
                                <img src="{{ $order->payment->proof_image_url }}"
                                     class="w-full rounded-xl object-cover border border-void-border hover:opacity-80 transition-opacity"
                                     alt="Bukti Transfer">
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection