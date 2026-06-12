@extends('layouts.app')

@section('title', 'Pembayaran — ' . $order->order_code)

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center mb-10">
            <div class="w-16 h-16 rounded-2xl bg-green-500/10 border border-green-500/30
                        flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-void-white">
                {{ $isCommission ? 'Commission Siap Dibayar!' : 'Order Berhasil Dibuat!' }}
            </h1>
            <p class="text-void-gray text-sm mt-2">
                Kode Order: <span class="font-bold text-void-accent">{{ $order->order_code }}</span>
            </p>
        </div>

        {{-- Order summary --}}
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden mb-6">
            <div class="border-b border-void-border px-6 py-4">
                <h3 class="text-sm font-bold text-void-white uppercase tracking-wider">Ringkasan Pesanan</h3>
            </div>

            <div class="p-6">
                @if(session()->has('success'))
                    <div class="mb-4 bg-green-500/10 border border-green-500/30 rounded-xl p-3">
                        <p class="text-sm text-green-400">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session()->has('error'))
                    <div class="mb-4 bg-red-500/10 border border-red-500/30 rounded-xl p-3">
                        <p class="text-sm text-red-400">{{ session('error') }}</p>
                    </div>
                @endif

                @if(session()->has('info'))
                    <div class="mb-4 bg-blue-500/10 border border-blue-500/30 rounded-xl p-3">
                        <p class="text-sm text-blue-400">{{ session('info') }}</p>
                    </div>
                @endif

                @if($order && ($order->items->count() > 0 || $isCommission))

                    {{-- Tabel item: untuk order produk biasa --}}
                    @if($order->items->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-void-border">
                                        <th class="text-left py-3 text-void-gray font-medium">Produk</th>
                                        <th class="text-center py-3 text-void-gray font-medium">Harga</th>
                                        <th class="text-center py-3 text-void-gray font-medium">Quantity</th>
                                        <th class="text-right py-3 text-void-gray font-medium">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalHarga = 0; @endphp
                                    @foreach($order->items as $item)
                                        @php $totalHarga += $item->price * $item->quantity; @endphp
                                        <tr class="border-b border-void-border/50">
                                            <td class="py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-void-dark shrink-0">
                                                        <img src="{{ $item->product->primary_image_url ?? asset('images/placeholder.jpg') }}"
                                                             alt="{{ $item->product_name }}"
                                                             class="w-full h-full object-cover">
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-void-white">{{ $item->product_name }}</p>
                                                        <p class="text-xs text-void-muted">Size: {{ $item->size }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center text-void-light">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            <td class="text-center text-void-light">{{ $item->quantity }}</td>
                                            <td class="text-right text-void-accent font-semibold">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-t border-void-border">
                                    <tr>
                                        <td colspan="3" class="py-3 text-right font-semibold text-void-white">Subtotal</td>
                                        <td class="py-3 text-right text-void-light">Rp {{ number_format($totalHarga, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="py-2 text-right text-sm text-void-gray">Ongkos Kirim</td>
                                        <td class="py-2 text-right text-void-light">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="border-t border-void-border">
                                        <td colspan="3" class="py-4 text-right text-lg font-bold text-void-white">TOTAL BAYAR</td>
                                        <td class="py-4 text-right text-xl font-black text-void-accent">Rp {{ number_format($totalHarga + $order->shipping_cost, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    {{-- Summary untuk commission order --}}
                    @if($isCommission)
                        <div class="rounded-xl border border-void-border p-5 mb-2">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-void-accent/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-void-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-void-white">{{ $order->notes }}</p>
                                    <p class="text-xs text-void-gray mt-1">Custom commission order</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-black text-void-accent">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-void-gray">Total Pembayaran</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Payment Methods Section (TANPA QRIS) --}}
                    <div class="mt-8 pt-6 border-t border-void-border">
                        <h4 class="text-sm font-bold text-void-white uppercase tracking-wider mb-4">Metode Pembayaran</h4>

                        {{-- Loading Indicator --}}
                        <div id="loading-indicator" class="hidden text-center py-8">
                            <svg class="w-8 h-8 animate-spin text-void-accent mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <p class="text-sm text-void-gray">Memproses...</p>
                        </div>

                        {{-- Payment Method Tabs (Tanpa QRIS) --}}
                        <div class="flex flex-wrap gap-2 border-b border-void-border mb-6">
                            <button type="button"
                                    class="payment-tab py-2 px-4 text-sm font-medium transition-all border-b-2 -mb-px rounded-t-lg text-void-accent border-void-accent"
                                    data-method="bank_transfer">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 6h12M6 18h12M3 14h18"/>
                                    </svg>
                                    Transfer Bank
                                </span>
                            </button>
                            <button type="button"
                                    class="payment-tab py-2 px-4 text-sm font-medium transition-all border-b-2 -mb-px rounded-t-lg text-void-gray border-transparent hover:text-void-white"
                                    data-method="convenience_store">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Minimarket
                                </span>
                            </button>
                        </div>

                        {{-- Bank Transfer Section --}}
                        <div id="bank_transfer-section" class="payment-section">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                                @foreach([
                                    ['bca_va',     'BCA Virtual Account',    'ATM, m-BCA, KlikBCA',      'BCA',     'bg-blue-500/20 text-blue-400'],
                                    ['mandiri_va', 'Mandiri Virtual Account', "ATM, Livin' by Mandiri",  'Mandiri', 'bg-orange-500/20 text-orange-400'],
                                    ['bni_va',     'BNI Virtual Account',    'ATM, BNI Mobile Banking',  'BNI',     'bg-blue-600/20 text-blue-500'],
                                    ['bri_va',     'BRI Virtual Account',    'ATM, BRImo',               'BRI',     'bg-red-500/20 text-red-400'],
                                    ['cimb_va',    'CIMB Niaga VA',          'ATM, CIMB Clicks',         'CIMB',    'bg-green-500/20 text-green-400'],
                                ] as [$val, $name, $desc, $label, $cls])
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-void-border cursor-pointer hover:border-void-accent transition-all">
                                        <input type="radio" name="payment_method" value="{{ $val }}" class="w-4 h-4 text-void-accent" data-type="bank_transfer">
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-void-white">{{ $name }}</p>
                                            <p class="text-xs text-void-gray">{{ $desc }}</p>
                                        </div>
                                        <span class="text-xs font-bold px-2 py-1 rounded {{ $cls }}">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Convenience Store Section --}}
                        <div id="convenience_store-section" class="payment-section" style="display: none;">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-void-border cursor-pointer hover:border-void-accent transition-all">
                                    <input type="radio" name="payment_method" value="alfamart" class="w-4 h-4 text-void-accent" data-type="convenience_store">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-void-white">Alfamart</p>
                                        <p class="text-xs text-void-gray">Bayar di gerai Alfamart terdekat</p>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-1 rounded bg-blue-500/20 text-blue-400">Alfamart</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-void-border cursor-pointer hover:border-void-accent transition-all">
                                    <input type="radio" name="payment_method" value="indomaret" class="w-4 h-4 text-void-accent" data-type="convenience_store">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-void-white">Indomaret</p>
                                        <p class="text-xs text-void-gray">Bayar di gerai Indomaret terdekat</p>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-1 rounded bg-orange-500/20 text-orange-400">Indomaret</span>
                                </label>
                            </div>
                            <div class="bg-void-muted/10 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-void-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-xs font-semibold text-void-white">Cara Bayar:</p>
                                </div>
                                <ol class="text-xs text-void-gray space-y-1 ml-5 list-decimal">
                                    <li>Datang ke gerai Alfamart/Indomaret terdekat</li>
                                    <li>Informasikan kode order: <strong class="text-void-accent">{{ $order->order_code }}</strong></li>
                                    <li>Bayar sesuai nominal yang tertera</li>
                                    <li>Pembayaran otomatis terkonfirmasi</li>
                                </ol>
                            </div>
                        </div>

                        {{-- Pay Button --}}
                        <div class="mt-8">
                            <button id="pay-button" class="btn-primary w-full py-3.5 text-center font-bold text-base">
                                Bayar Sekarang
                            </button>
                        </div>
                    </div>

                @else
                    <div class="text-center py-8">
                        <p class="text-void-gray">Pesanan tidak ditemukan atau sudah tidak aktif.</p>
                        <a href="{{ route('home') }}" class="inline-block mt-4 px-6 py-2 rounded-xl bg-void-accent text-white text-sm">
                            Kembali ke Toko
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Bottom navigation --}}
        <div class="flex flex-col sm:flex-row gap-3 mt-6">
            @if($isCommission)
                <a href="{{ route('commission.index') }}"
                   class="flex-1 bg-void-card border border-void-border text-center py-3 rounded-xl text-sm font-semibold text-void-white hover:bg-void-dark transition-all duration-200">
                    Kembali ke Commission
                </a>
            @else
                <a href="{{ route('home') }}"
                   class="flex-1 bg-void-card border border-void-border text-center py-3 rounded-xl text-sm font-semibold text-void-white hover:bg-void-dark transition-all duration-200">
                    Kembali ke Toko
                </a>
            @endif
            <a href="{{ route('orders.show', $order->order_code) }}"
               class="flex-1 bg-void-card border border-void-accent text-center py-3 rounded-xl text-sm text-void-accent font-bold hover:bg-void-accent hover:text-white transition-all duration-200 shadow-md">
                Lihat Pesanan
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript"
    src="{{ config('midtrans.is_production')
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>
<script>
let currentSnapToken = null;
let isLoading        = false;
let paymentCompleted = false;

// ─── Tab switching (Bank & Minimarket only) ──────────────────────────────
document.querySelectorAll('.payment-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        const method = this.dataset.method;

        document.querySelectorAll('.payment-tab').forEach(t => {
            t.classList.remove('text-void-accent', 'border-void-accent');
            t.classList.add('text-void-gray', 'border-transparent');
        });
        this.classList.remove('text-void-gray', 'border-transparent');
        this.classList.add('text-void-accent', 'border-void-accent');

        document.querySelectorAll('.payment-section').forEach(s => s.style.display = 'none');
        document.getElementById(`${method}-section`).style.display = 'block';

        document.querySelectorAll('input[name="payment_method"]').forEach(r => r.checked = false);
        currentSnapToken = null;
    });
});

// ─── Pilih metode pembayaran → ambil snap token ───────────────────────────
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', async function () {
        if (!this.checked || isLoading) return;

        isLoading = true;
        const loadingDiv = document.getElementById('loading-indicator');
        const payBtn     = document.getElementById('pay-button');

        loadingDiv.classList.remove('hidden');
        payBtn.disabled     = true;
        payBtn.textContent  = 'Memproses...';

        try {
            const res  = await fetch(`/payment/{{ $order->order_code }}/snap-token`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    payment_method: this.value,
                    payment_type:   this.dataset.type,
                }),
            });

            const data = await res.json();

            if (data.snap_token) {
                currentSnapToken    = data.snap_token;
                payBtn.disabled     = false;
                payBtn.textContent  = 'Bayar Sekarang';
            } else {
                alert('Gagal memproses: ' + (data.message || 'Unknown error'));
                payBtn.disabled = true;
                this.checked    = false;
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan. Silakan coba lagi.');
            payBtn.disabled = true;
            this.checked    = false;
        } finally {
            isLoading = false;
            loadingDiv.classList.add('hidden');
        }
    });
});

// ─── Kirim manual callback ke server ─────────────────────────────────────
async function updatePaymentStatus(result) {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    try {
        const res = await fetch(`/payment/{{ $order->order_code }}/callback-manual`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                ...result,
                payment_method: selected?.value ?? 'unknown',
                payment_type:   selected?.dataset?.type ?? 'unknown',
                result,
            }),
        });
        return await res.json();
    } catch (err) {
        console.error('Manual callback error:', err);
        return null;
    }
}

// ─── Tombol Bayar Sekarang ────────────────────────────────────────────────
document.getElementById('pay-button').addEventListener('click', function () {
    if (!document.querySelector('input[name="payment_method"]:checked')) {
        alert('Silakan pilih metode pembayaran terlebih dahulu');
        return;
    }
    if (!currentSnapToken) {
        alert('Silakan tunggu sebentar, token sedang diproses.');
        return;
    }
    if (paymentCompleted) {
        alert('Pembayaran sudah diproses. Silakan tunggu.');
        return;
    }

    const btn          = this;
    const originalText = btn.innerText;
    btn.innerText      = 'Memproses...';
    btn.disabled       = true;

    window.snap.pay(currentSnapToken, {
        onSuccess: async function (result) {
            paymentCompleted = true;
            await updatePaymentStatus(result);
            alert('Pembayaran berhasil!');
            @if($isCommission)
                window.location.href = '{{ route("commission.index") }}?payment=success';
            @else
                window.location.href = '{{ route("orders.show", $order->order_code) }}?payment=success';
            @endif
        },
        onPending: function (result) {
            alert('Menunggu pembayaran Anda! Silakan selesaikan pembayaran.');
            btn.innerText = originalText;
            btn.disabled  = false;
        },
        onError: function (result) {
            alert('Pembayaran gagal! Silakan coba lagi.');
            btn.innerText = originalText;
            btn.disabled  = false;
        },
        onClose: function () {
            alert('Anda menutup popup sebelum menyelesaikan pembayaran.');
            btn.innerText = originalText;
            btn.disabled  = false;
        },
    });
});
</script>
@endpush