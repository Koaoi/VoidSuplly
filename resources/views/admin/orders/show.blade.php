@extends('layouts.admin')

@section('title', 'Detail Order — ' . $order->order_code)
@section('page-title', 'Order — ' . $order->order_code)

@section('content')
{{-- ── Header dengan Gambar/Logo (Konsisten dengan Dashboard) ─────── --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-void-accent/20 to-void-accent/5 
                    border border-void-accent/30 flex items-center justify-center">
            <img src="{{ asset('image/favicon.png') }}" 
                 alt="VOID Logo" 
                 class="w-7 h-7 object-contain"
                 onerror="this.src='https://placehold.co/50x50/1e293b/ffffff?text=V'">
        </div>
        <div class="h-10 w-px bg-void-border"></div>
        <div>
            <h1 class="text-2xl font-black text-void-white tracking-tight">
                Order — <span class="text-void-accent">{{ $order->order_code }}</span>
            </h1>
            <p class="text-xs text-void-gray mt-0.5">
                Detail pesanan #{{ $order->order_code }}
            </p>
        </div>
    </div>
    
    <div class="px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2
        @if($order->status == 'pending') bg-yellow-500/10 text-yellow-400 border border-yellow-500/30
        @elseif($order->status == 'paid') bg-blue-500/10 text-blue-400 border border-blue-500/30
        @elseif($order->status == 'processing') bg-purple-500/10 text-purple-400 border border-purple-500/30
        @elseif($order->status == 'shipped') bg-orange-500/10 text-orange-400 border border-orange-500/30
        @elseif($order->status == 'completed') bg-green-500/10 text-green-400 border border-green-500/30
        @else bg-red-500/10 text-red-400 border border-red-500/30
        @endif">
        @if($order->status == 'pending')
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        @elseif($order->status == 'completed')
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        @else
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        @endif
        {{ ucfirst($order->status) }}
    </div>
</div>

{{-- ── Main Content ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT COLUMN --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Items (Produk + Komisi) --}}
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-void-border">
                <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">
                    Item ({{ ($order->items->count() + ($order->commissions->count() ?? 0)) }})
                </h2>
            </div>
            <div class="divide-y divide-void-border">
                {{-- PRODUK ITEMS --}}
                @foreach($order->items as $item)
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-void-dark shrink-0">
                            <img src="{{ $item->product?->primary_image_url ?? asset('images/product-placeholder.png') }}"
                                 class="w-full h-full object-cover"
                                 alt="{{ $item->product_name }}"
                                 onerror="this.src='https://placehold.co/56x56/1e293b/64748b?text=No+Image'">
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

                {{-- KOMISI ITEMS (dengan GAMBAR REFERENSI) --}}
                @if(isset($order->commissions) && $order->commissions->count() > 0)
                    @foreach($order->commissions as $commission)
                        <div class="flex items-center gap-4 px-5 py-4 bg-purple-500/5">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-purple-500/10 shrink-0">
                                @if($commission->reference_image_url ?? $commission->image_url ?? false)
                                    <img src="{{ $commission->reference_image_url ?? $commission->image_url }}" 
                                         class="w-full h-full object-cover"
                                         alt="{{ $commission->title ?? 'Komisi' }}"
                                         onerror="this.src='https://placehold.co/56x56/1e293b/9333ea?text=Komisi'">
                                @elseif($commission->product && $commission->product->primary_image_url)
                                    <img src="{{ $commission->product->primary_image_url }}" 
                                         class="w-full h-full object-cover"
                                         alt="{{ $commission->title }}"
                                         onerror="this.src='https://placehold.co/56x56/1e293b/9333ea?text=Komisi'">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-purple-500/20">
                                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-purple-400">
                                    <span class="text-void-gray">Komisi:</span> {{ $commission->title ?? 'Commission' }}
                                </p>
                                <p class="text-xs text-void-gray">
                                    {{ $commission->pivot->quantity ?? 1 }} × 
                                    Rp {{ number_format($commission->pivot->commission_rate ?? $commission->commission_rate ?? 0, 0, ',', '.') }}
                                </p>
                                @if($commission->description)
                                    <p class="text-[10px] text-void-muted mt-1">{{ Str::limit($commission->description, 100) }}</p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-black text-purple-400">
                                    Rp {{ number_format($commission->pivot->subtotal ?? ($commission->pivot->quantity * $commission->commission_rate) ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="px-5 py-4 border-t border-void-border bg-void-dark/30 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-void-gray">Subtotal Produk</span>
                    <span class="text-void-light">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @php
                    $totalCommission = $order->commissions->sum(function($item) {
                        return (float) ($item->pivot->subtotal ?? 0);
                    }) ?? 0;
                @endphp
                @if($totalCommission > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-void-gray">Subtotal Komisi</span>
                        <span class="text-purple-400">Rp {{ number_format($totalCommission, 0, ',', '.') }}</span>
                    </div>
                @endif
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
                    <form method="POST" action="{{ route('admin.orders.confirm-payment', $order) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="text-sm font-bold bg-green-500/10 border border-green-500/30 text-green-400
                                       px-6 py-2.5 rounded-xl hover:bg-green-500/20 transition-colors">
                            Konfirmasi Pembayaran
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.reject-payment', $order) }}" class="inline ml-2">
                        @csrf
                        <button type="submit"
                                class="text-sm font-bold bg-red-500/10 border border-red-500/30 text-red-400
                                       px-6 py-2.5 rounded-xl hover:bg-red-500/20 transition-colors">
                            Tolak Pembayaran
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="space-y-5">

        {{-- Customer dengan Avatar --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <h3 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Customer</h3>
            <div class="flex items-center gap-3 mb-3">
                <img src="{{ $order->user->avatar_url ?? asset('images/default-avatar.png') }}" 
                     class="w-12 h-12 rounded-full object-cover border-2 border-void-accent/30"
                     alt="{{ $order->user->name }}"
                     onerror="this.src='https://placehold.co/48x48/1e293b/64748b?text={{ substr($order->user->name, 0, 1) }}'">
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
                        <span class="text-void-white font-semibold capitalize">
                            @php
                                $methodName = $order->payment->method ?? $order->payment->midtrans_payment_type ?? '—';
                                $methodDisplay = [
                                    'bca_va' => 'BCA Virtual Account',
                                    'mandiri_va' => 'Mandiri Virtual Account',
                                    'bni_va' => 'BNI Virtual Account',
                                    'bri_va' => 'BRI Virtual Account',
                                    'cimb_va' => 'CIMB Virtual Account',
                                    'bank_transfer' => 'Transfer Bank (Midtrans)',
                                    'qris' => 'QRIS',
                                    'gopay' => 'GoPay',
                                    'shopeepay' => 'ShopeePay',
                                    'ovo' => 'OVO',
                                    'dana' => 'DANA',
                                    'linkaja' => 'LinkAja',
                                    'alfamart' => 'Alfamart',
                                    'indomaret' => 'Indomaret',
                                    'manual_transfer' => 'Transfer Manual',
                                    'credit_card' => 'Kartu Kredit',
                                ][$methodName] ?? ucfirst(str_replace('_', ' ', $methodName));
                            @endphp
                            {{ $methodDisplay }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-void-gray">Status</span>
                        <span class="{{ $order->payment->status === 'paid' ? 'text-green-400' : ($order->payment->status === 'pending' ? 'text-yellow-400' : 'text-red-400') }} font-bold capitalize">
                            @php
                                $statusDisplay = [
                                    'paid' => 'Lunas',
                                    'pending' => 'Menunggu Pembayaran',
                                    'failed' => 'Gagal',
                                    'expired' => 'Kadaluarsa',
                                    'cancelled' => 'Dibatalkan',
                                ][$order->payment->status] ?? ucfirst($order->payment->status);
                            @endphp
                            {{ $statusDisplay }}
                        </span>
                    </div>
                    @if($order->payment->paid_at)
                        <div class="flex justify-between">
                            <span class="text-void-gray">Dibayar</span>
                            <span class="text-void-light">{{ $order->payment->paid_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    
                    {{-- Midtrans Transaction Details --}}
                    @if($order->payment->payment_details)
                        @php
                            $details = is_string($order->payment->payment_details) 
                                ? json_decode($order->payment->payment_details, true) 
                                : $order->payment->payment_details;
                        @endphp
                        @if(isset($details) && is_array($details))
                            @if(isset($details['va_numbers']) && !empty($details['va_numbers']))
                                <div class="pt-2 border-t border-void-border">
                                    <p class="text-void-gray mb-1">Virtual Account:</p>
                                    @foreach($details['va_numbers'] as $va)
                                        <div class="bg-void-muted/20 rounded-lg p-2 mb-2">
                                            <p class="text-void-light font-mono text-sm">{{ strtoupper($va['bank']) }}: {{ $va['va_number'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            @if(isset($details['payment_type']) && $details['payment_type'] == 'qris')
                                <div class="pt-2 border-t border-void-border">
                                    <p class="text-void-gray mb-1">QRIS:</p>
                                    <div class="bg-void-muted/20 rounded-lg p-2">
                                        @if(isset($details['qr_code_url']))
                                            <img src="{{ $details['qr_code_url'] }}" 
                                                 class="w-32 h-32 mx-auto rounded-xl"
                                                 alt="QR Code">
                                            <p class="text-void-light text-xs text-center mt-2">Scan QR Code</p>
                                        @else
                                            <p class="text-void-light text-sm">Scan QR Code menggunakan aplikasi payment favoritmu</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            @if(isset($details['payment_type']) && in_array($details['payment_type'], ['gopay', 'shopeepay', 'ovo', 'dana']))
                                <div class="pt-2 border-t border-void-border">
                                    <p class="text-void-gray mb-1">E-Wallet:</p>
                                    <div class="bg-void-muted/20 rounded-lg p-2">
                                        <div class="flex items-center gap-2">
                                            <img src="{{ asset('images/payment/'.$details['payment_type'].'.png') }}" 
                                                 class="w-8 h-8 rounded-lg"
                                                 alt="{{ $details['payment_type'] }}"
                                                 onerror="this.style.display='none'">
                                            <p class="text-void-light text-sm capitalize">{{ $details['payment_type'] }}</p>
                                        </div>
                                        @if(isset($details['qr_code_url']))
                                            <img src="{{ $details['qr_code_url'] }}" 
                                                 class="w-32 h-32 mx-auto mt-2 rounded-xl"
                                                 alt="QR Code">
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex justify-between pt-1">
                                <span class="text-void-gray">Tipe</span>
                                <span class="text-void-light capitalize">
                                    @php
                                        $typeDisplay = [
                                            'bank_transfer' => 'Transfer Bank',
                                            'credit_card' => 'Kartu Kredit',
                                            'qris' => 'QRIS',
                                            'gopay' => 'GoPay',
                                            'shopeepay' => 'ShopeePay',
                                            'alfamart' => 'Alfamart',
                                            'indomaret' => 'Indomaret',
                                        ][$details['payment_type']] ?? $details['payment_type'];
                                    @endphp
                                    {{ $typeDisplay }}
                                </span>
                            </div>
                            
                            @if(isset($details['transaction_time']))
                                <div class="flex justify-between">
                                    <span class="text-void-gray">Waktu Transaksi</span>
                                    <span class="text-void-light">{{ \Carbon\Carbon::parse($details['transaction_time'])->format('d M Y H:i') }}</span>
                                </div>
                            @endif
                        @endif
                    @endif
                    
                    {{-- GAMBAR BUKTI TRANSFER --}}
                    @if($order->payment->proof_image_url)
                        <div class="pt-2 border-t border-void-border">
                            <p class="text-void-gray mb-2">Bukti Transfer:</p>
                            <a href="{{ $order->payment->proof_image_url }}" target="_blank" class="block">
                                <img src="{{ $order->payment->proof_image_url }}"
                                     class="w-full rounded-xl object-cover border border-void-border hover:opacity-80 transition-opacity"
                                     alt="Bukti Transfer"
                                     onerror="this.src='https://placehold.co/400x300/1e293b/64748b?text=Bukti+Transfer'">
                            </a>
                        </div>
                    @endif

                    @if($methodName && $methodName !== '—')
                        <div class="mt-3 pt-3 border-t border-void-border">
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $badgeClass = match(true) {
                                        str_contains($methodName, 'va') => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                                        str_contains($methodName, 'qris') => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                                        in_array($methodName, ['gopay', 'shopeepay', 'ovo', 'dana']) => 'bg-green-500/20 text-green-400 border-green-500/30',
                                        $methodName === 'manual_transfer' || $methodName === 'Transfer Manual' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                        default => 'bg-gray-500/20 text-gray-400 border-gray-500/30'
                                    };
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full border {{ $badgeClass }}">
                                    {{ $methodDisplay }}
                                </span>
                            </div>
                        </div>
                    @endif

                    @if($order->payment->snap_token)
                        <div class="pt-2 border-t border-void-border">
                            <p class="text-void-gray text-[10px]">Snap Token: {{ substr($order->payment->snap_token, 0, 30) }}...</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Order Info --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <h3 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Informasi Order</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-void-gray">Kode Order</span>
                    <span class="text-void-light font-mono">{{ $order->order_code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-void-gray">Dibuat</span>
                    <span class="text-void-light">{{ $order->created_at->format('d M Y H:i') }}</span>
                </div>
                @if($order->updated_at != $order->created_at)
                    <div class="flex justify-between">
                        <span class="text-void-gray">Terakhir Update</span>
                        <span class="text-void-light">{{ $order->updated_at->format('d M Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection