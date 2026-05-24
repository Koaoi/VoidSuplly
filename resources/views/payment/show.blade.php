@extends('layouts.app')

@section('title', 'Pembayaran — ' . $order->order_code)

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center mb-10">
            @if($order->status === 'pending')
                <div class="w-16 h-16 rounded-2xl bg-yellow-500/10 border border-yellow-500/30
                            flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-void-white">Selesaikan Pembayaran</h1>
                <p class="text-void-gray text-sm mt-2">
                    Order: <span class="font-black text-void-accent">{{ $order->order_code }}</span>
                </p>
            @elseif(in_array($order->status, ['paid','processing','shipped','completed']))
                <div class="w-16 h-16 rounded-2xl bg-green-500/10 border border-green-500/30
                            flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-void-white">Pembayaran Dikonfirmasi!</h1>
                <p class="text-void-gray text-sm mt-2">Terima kasih! Pesananmu sedang diproses.</p>
            @else
                <div class="w-16 h-16 rounded-2xl bg-red-500/10 border border-red-500/30
                            flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-void-white">Pesanan Dibatalkan</h1>
            @endif
        </div>

        {{-- Status Tracker --}}
        @php
            $steps = ['pending', 'paid', 'processing', 'shipped', 'completed'];
            $stepLabel = [
                'pending' => 'Menunggu Bayar',
                'paid' => 'Dikonfirmasi',
                'processing' => 'Diproses',
                'shipped' => 'Dikirim',
                'completed' => 'Selesai'
            ];
            $currentIdx = array_search($order->status, $steps);
            $currentIdx = ($currentIdx === false) ? 0 : $currentIdx;
        @endphp

        @if($order->status !== 'cancelled')
            <div class="bg-void-card border border-void-border rounded-2xl p-6 mb-6">
                <h2 class="text-[10px] font-bold tracking-widest text-void-white uppercase mb-6">Status Pesanan</h2>
                <div class="relative">
                    <div class="absolute top-4 left-4 right-4 h-0.5 bg-void-border"></div>
                    <div class="absolute top-4 left-4 h-0.5 bg-white transition-all duration-700"
                         style="width:{{ $currentIdx > 0 ? min(($currentIdx/(count($steps)-1))*100,100) : 0 }}%">
                    </div>
                    <div class="relative flex justify-between">
                        @foreach($steps as $i => $step)
                            @php $done = $i <= $currentIdx; @endphp
                            <div class="flex flex-col items-center gap-2" style="width:20%">
                                <div class="relative z-10 w-8 h-8 rounded-full border-2 flex items-center
                                            justify-center transition-all duration-500
                                            {{ $done ? 'bg-white border-white' : 'bg-void-dark border-void-border' }}">
                                    @if($done)
                                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @else
                                        <div class="w-2 h-2 rounded-full bg-void-muted"></div>
                                    @endif
                                </div>
                                <span class="text-[9px] font-semibold text-center uppercase tracking-wide
                                             {{ $done ? 'text-void-white' : 'text-void-muted' }}">
                                    {{ $stepLabel[$step] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Payment Methods (jika masih pending) --}}
        @if($order->status === 'pending')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

                {{-- Midtrans Snap --}}
                @if($snapToken)
                    <div class="bg-void-card border border-void-border rounded-2xl p-6 flex flex-col">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/30
                                        flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-void-white">Bayar via Midtrans</h3>
                                <p class="text-xs text-void-gray">Transfer, QRIS, e-wallet, kartu kredit</p>
                            </div>
                        </div>
                        <button id="pay-button"
                                class="btn-primary w-full py-3 text-sm mt-auto flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Bayar Sekarang
                        </button>
                        <p class="text-[10px] text-void-gray text-center mt-2">Aman & terenkripsi</p>
                    </div>
                @endif

                {{-- Upload Bukti Transfer Manual --}}
                <div class="bg-void-card border border-void-border rounded-2xl p-6"
                     x-data="{ preview: null, dragover: false }">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30
                                    flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-void-white">Upload Bukti Transfer</h3>
                            <p class="text-xs text-void-gray">JPG, PNG, WEBP — maks. 3MB</p>
                        </div>
                    </div>

                    {{-- Info rekening --}}
                    <div class="bg-void-dark rounded-xl p-3 mb-4 text-xs space-y-1.5">
                        <p class="text-void-gray font-semibold mb-1.5">Transfer ke:</p>
                        <div class="flex justify-between">
                            <span class="text-void-gray">Bank</span>
                            <span class="text-void-white font-bold">BCA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-void-gray">Rekening</span>
                            <span class="text-void-white font-bold tracking-wider">1234-5678-90</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-void-gray">Atas Nama</span>
                            <span class="text-void-white font-bold">VOID Supply</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-void-border">
                            <span class="text-void-gray font-semibold">Total Transfer</span>
                            <span class="text-void-accent font-black text-sm">{{ $order->formatted_total }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('payment.proof', $order->order_code) }}"
                          enctype="multipart/form-data">
                        @csrf

                        <label class="block cursor-pointer mb-3"
                               @dragover.prevent="dragover=true"
                               @dragleave="dragover=false"
                               @drop.prevent="dragover=false; const f=$event.dataTransfer.files[0];
                                              if(f){ const r=new FileReader(); r.onload=e=>preview=e.target.result; r.readAsDataURL(f); }">
                            <input type="file" name="proof_image" accept="image/*" class="sr-only"
                                   @change="const f=$event.target.files[0];
                                            if(f){ const r=new FileReader(); r.onload=e=>preview=e.target.result; r.readAsDataURL(f); }">

                            <template x-if="preview">
                                <div class="relative aspect-video rounded-xl overflow-hidden border border-void-border group">
                                    <img :src="preview" class="w-full h-full object-contain bg-void-dark">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100
                                                transition-opacity flex items-center justify-center">
                                        <span class="text-xs text-white font-medium">Klik untuk ganti</span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!preview">
                                <div :class="dragover ? 'border-void-accent' : 'border-void-border'"
                                     class="border-2 border-dashed rounded-xl p-6 text-center
                                            hover:border-void-muted transition-colors">
                                    <svg class="w-8 h-8 text-void-muted mx-auto mb-2"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-xs text-void-gray">Drag & drop atau klik upload</p>
                                </div>
                            </template>
                        </label>

                        @error('proof_image')
                            <p class="text-xs text-red-400 mb-2">{{ $message }}</p>
                        @enderror

                        <button type="submit"
                                :disabled="!preview"
                                class="btn-primary w-full py-2.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Kirim Bukti Bayar
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bukti sudah diupload sebelumnya --}}
            @if($order->payment?->proof_image)
                <div class="bg-green-500/10 border border-green-500/30 rounded-2xl p-4 mb-6 flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-green-400">Bukti transfer sudah dikirim</p>
                        <p class="text-xs text-void-gray mt-0.5">Admin akan memverifikasi dalam 1×24 jam.</p>
                    </div>
                    <a href="{{ $order->payment->proof_image_url }}" target="_blank"
                       class="text-xs text-void-gray hover:text-void-accent transition-colors underline shrink-0">
                        Lihat
                    </a>
                </div>
            @endif
        @endif

        {{-- Order Detail Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

            {{-- Items --}}
            <div class="bg-void-card border border-void-border rounded-2xl p-5">
                <h3 class="text-[10px] font-bold tracking-widest text-void-white uppercase mb-4">Item Pesanan</h3>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl overflow-hidden bg-void-dark shrink-0">
                                <img src="{{ $item->product?->primary_image_url ?? asset('images/product-placeholder.png') }}"
                                     class="w-full h-full object-cover"
                                     alt="{{ $item->product_name }}">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-void-light line-clamp-1">{{ $item->product_name }}</p>
                                <p class="text-[10px] text-void-gray">Size {{ $item->size }} × {{ $item->quantity }}</p>
                            </div>
                            <p class="text-xs font-bold text-void-white shrink-0">{{ $item->formatted_subtotal }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-3 border-t border-void-border space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-void-gray">Subtotal</span>
                        <span class="text-void-light">Rp {{ number_format($order->subtotal,0,',','.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-void-gray">Ongkos Kirim</span>
                        <span class="text-void-light">Rp {{ number_format($order->shipping_cost,0,',','.') }}</span>
                    </div>
                    <div class="flex justify-between font-black text-sm pt-2 border-t border-void-border">
                        <span class="text-void-white">Total</span>
                        <span class="text-void-accent">{{ $order->formatted_total }}</span>
                    </div>
                </div>
            </div>

            {{-- Alamat kirim --}}
            @if($order->shippingAddress)
                <div class="bg-void-card border border-void-border rounded-2xl p-5">
                    <h3 class="text-[10px] font-bold tracking-widest text-void-white uppercase mb-4">Alamat Pengiriman</h3>
                    @php $a = $order->shippingAddress; @endphp
                    <div class="text-xs space-y-1.5">
                        <p class="font-bold text-void-white text-sm">{{ $a->recipient_name }}</p>
                        <p class="text-void-gray">{{ $a->phone }}</p>
                        <p class="text-void-gray leading-relaxed mt-2">
                            {{ $a->address_detail }},<br>
                            {{ $a->city }}, {{ $a->province }} {{ $a->postal_code }}
                        </p>
                        <div class="pt-3 border-t border-void-border">
                            <p class="font-bold text-void-white">{{ $a->courier }} — {{ $a->service }}</p>
                            @if($a->service_description)
                                <p class="text-void-gray mt-0.5">{{ $a->service_description }}</p>
                            @endif
                            @if($a->estimated_days)
                                <p class="text-green-400 mt-1">
                                    Est. {{ $a->estimated_days }} hari kerja
                                </p>
                            @endif
                            <p class="text-void-gray mt-1">
                                Ongkos kirim:
                                <span class="text-void-light font-bold">
                                    Rp {{ number_format($order->shipping_cost,0,',','.') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('orders.show', $order->order_code) }}"
               class="btn-secondary flex-1 text-center py-3 text-sm">
                Detail Pesanan
            </a>
            <a href="{{ route('orders.index') }}"
               class="btn-secondary flex-1 text-center py-3 text-sm">
                Semua Pesanan
            </a>
            <a href="{{ route('products.index') }}"
               class="btn-primary flex-1 text-center py-3 text-sm">
                Lanjut Belanja
            </a>
        </div>

    </div>
</div>
@endsection

{{-- Midtrans Snap.js --}}
@if(isset($snapToken) && $snapToken && $order->status === 'pending')
@push('scripts')
<script src="{{ config('midtrans.is_production')
    ? 'https://app.midtrans.com/snap/snap.js'
    : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ $clientKey }}"></script>
<script>
const payBtn = document.getElementById('pay-button');
if (payBtn) {
    payBtn.addEventListener('click', function () {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function (result) {
                window.location.href = '{{ route('orders.show', $order->order_code) }}';
            },
            onPending: function (result) {
                window.location.reload();
            },
            onError: function (result) {
                alert('Pembayaran gagal. Silakan coba lagi atau gunakan metode lain.');
            },
            onClose: function () {
                // User tutup popup tanpa bayar
            }
        });
    });
}
</script>
@endpush
@endif