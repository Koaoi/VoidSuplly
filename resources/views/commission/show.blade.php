@extends('layouts.app')

@section('title', 'Detail Commission')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('commission.index') }}"
           class="inline-flex items-center gap-2 text-void-gray hover:text-void-white text-sm mb-6 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Commission
        </a>

        {{-- Flash messages --}}
        @foreach(['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $type => $color)
            @if(session($type))
                <div class="mb-6 bg-{{ $color }}-500/10 border border-{{ $color }}-500/30 rounded-xl p-4">
                    <p class="text-sm text-{{ $color }}-400">{{ session($type) }}</p>
                </div>
            @endif
        @endforeach

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Detail Panel (kiri) --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Info Utama --}}
                <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
                    <div class="border-b border-void-border px-6 py-4 flex items-center justify-between">
                        <h2 class="font-bold text-void-white">Detail Commission</h2>
                        @php
                            $statusConfig = [
                                'pending'     => ['Menunggu Review',   'bg-yellow-500/20 text-yellow-400'],
                                'reviewing'   => ['Sedang Direview',   'bg-blue-500/20 text-blue-400'],
                                'accepted'    => ['Disetujui',          'bg-emerald-500/20 text-emerald-400'],
                                'in_progress' => ['Sedang Dikerjakan', 'bg-purple-500/20 text-purple-400'],
                                'completed'   => ['Selesai',            'bg-green-500/20 text-green-400'],
                                'rejected'    => ['Ditolak',            'bg-red-500/20 text-red-400'],
                                'paid'        => ['Dibayar',            'bg-green-600/20 text-green-300'],
                            ];
                            [$statusLabel, $statusClass] = $statusConfig[$commission->status] ?? [ucfirst($commission->status), 'bg-gray-500/20 text-gray-400'];
                        @endphp
                        <span class="text-xs font-bold px-3 py-1 rounded-full {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-void-gray mb-1">Judul</p>
                                <p class="text-sm font-semibold text-void-white">{{ $commission->title }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-void-gray mb-1">Jenis Produk</p>
                                <p class="text-sm font-semibold text-void-white">{{ $commission->product_type_label }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-void-gray mb-1">Jumlah</p>
                                <p class="text-sm font-semibold text-void-white">{{ $commission->quantity }} pcs</p>
                            </div>
                            <div>
                                <p class="text-xs text-void-gray mb-1">Budget Awal</p>
                                <p class="text-sm font-semibold text-void-white">{{ $commission->formatted_budget }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-void-gray mb-1">Tanggal Request</p>
                                <p class="text-sm font-semibold text-void-white">{{ $commission->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            @if($commission->order_id)
                                <div>
                                    <p class="text-xs text-void-gray mb-1">Nomor Order</p>
                                    <p class="text-sm font-mono text-void-accent">{{ $commission->order->order_code }}</p>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-void-gray mb-1">Deskripsi</p>
                            <p class="text-sm text-void-light leading-relaxed">{{ $commission->description }}</p>
                        </div>
                    </div>
                </div>

                {{-- 🔥 REFERENSI GAMBAR (DIPERBAIKI) --}}
                @if($commission->reference_image)
                    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
                        <div class="border-b border-void-border px-6 py-4">
                            <h3 class="font-bold text-void-white text-sm">Gambar Referensi</h3>
                        </div>
                        <div class="p-6">
                            <a href="{{ asset('storage/' . $commission->reference_image) }}" target="_blank" class="block">
                                <img src="{{ asset('storage/' . $commission->reference_image) }}"
                                     alt="Referensi desain"
                                     class="max-h-64 rounded-xl border border-void-border object-contain mx-auto hover:opacity-90 transition-opacity cursor-zoom-in"
                                     onerror="this.onerror=null; this.src='{{ asset('images/placeholder.jpg') }}';">
                            </a>
                            <p class="text-center text-xs text-void-gray mt-3">
                                Klik gambar untuk memperbesar
                            </p>
                        </div>
                    </div>
                @else
                    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
                        <div class="border-b border-void-border px-6 py-4">
                            <h3 class="font-bold text-void-white text-sm">Gambar Referensi</h3>
                        </div>
                        <div class="p-6 text-center">
                            <svg class="w-12 h-12 text-void-muted mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-void-gray">Tidak ada gambar referensi</p>
                        </div>
                    </div>
                @endif

                {{-- Catatan Admin --}}
                @if($commission->admin_note)
                    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
                        <div class="border-b border-void-border px-6 py-4">
                            <h3 class="font-bold text-void-white text-sm">Catatan Admin</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-void-light leading-relaxed">{{ $commission->admin_note }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Action Panel (kanan) --}}
            <div class="space-y-4">

                {{-- Harga --}}
                @if($commission->quoted_price)
                    <div class="bg-void-card border border-void-accent/30 rounded-2xl p-6">
                        <p class="text-xs text-void-gray mb-1">Harga yang Ditetapkan</p>
                        <p class="text-3xl font-black text-void-accent">
                            {{ $commission->formatted_quoted_price }}
                        </p>
                    </div>
                @endif

                {{-- Panel Aksi berdasarkan status --}}
                <div class="bg-void-card border border-void-border rounded-2xl p-6">

                    @if(in_array($commission->status, ['pending', 'reviewing']))
                        <div class="text-center">
                            <div class="w-12 h-12 rounded-2xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-void-white mb-1">Sedang Diproses</p>
                            <p class="text-xs text-void-gray">Tim kami sedang mereview commission kamu. Mohon tunggu ya!</p>
                        </div>

                    @elseif($commission->status === 'rejected')
                        <div class="text-center">
                            <div class="w-12 h-12 rounded-2xl bg-red-500/10 border border-red-500/30 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-void-white mb-1">Ditolak</p>
                            <p class="text-xs text-void-gray">Maaf, commission ini tidak dapat kami proses.</p>
                        </div>

                    @elseif($commission->status === 'accepted')
                        @php
                            $canPay = !$commission->order_id
                                || ($commission->order && $commission->order->status === 'cancelled');
                            $hasPendingOrder = $commission->order_id
                                && $commission->order
                                && $commission->order->status === 'pending';
                        @endphp

                        @if($canPay)
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-void-white mb-1">Commission Disetujui!</p>
                                <p class="text-xs text-void-gray mb-4">Selesaikan pembayaran untuk memulai proses pengerjaan.</p>

                                <form action="{{ route('commission.process-payment', $commission) }}"
                                      method="POST"
                                      onsubmit="return confirm('Lanjutkan ke halaman pembayaran?')">
                                    @csrf
                                    <button type="submit" class="btn-primary w-full py-3 text-sm font-bold">
                                        Bayar Sekarang
                                    </button>
                                </form>

                                <p class="text-xs text-void-muted mt-3">
                                    Tersedia: Transfer Bank, Minimarket
                                </p>
                            </div>

                        @elseif($hasPendingOrder)
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-2xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-void-white mb-1">Menunggu Pembayaran</p>
                                <p class="text-xs text-void-gray mb-4">Order sudah dibuat. Selesaikan pembayaran sebelum kadaluarsa.</p>

                                <a href="{{ route('payment.show', $commission->order->order_code) }}"
                                   class="btn-primary w-full py-3 text-sm font-bold text-center block">
                                    Lanjutkan Bayar
                                </a>
                            </div>
                        @endif

                    @elseif(in_array($commission->status, ['in_progress', 'completed']))
                        <div class="text-center">
                            <div class="w-12 h-12 rounded-2xl bg-green-500/10 border border-green-500/30 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-void-white mb-1">
                                {{ $commission->status === 'completed' ? 'Selesai!' : 'Sedang Dikerjakan' }}
                            </p>
                            <p class="text-xs text-void-gray">
                                {{ $commission->status === 'completed'
                                    ? 'Commission kamu sudah selesai dibuat!'
                                    : 'Tim kami sedang mengerjakan commission kamu.' }}
                            </p>
                        </div>

                    @elseif($commission->status === 'paid')
                        <div class="text-center">
                            <div class="w-12 h-12 rounded-2xl bg-green-500/10 border border-green-500/30 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-void-white mb-1">Pembayaran Berhasil</p>
                            <p class="text-xs text-void-gray mb-4">Commission kamu sudah dibayar. Tim kami akan segera memproses.</p>

                            @if($commission->order_id && $commission->order)
                                <a href="{{ route('orders.show', $commission->order->order_code) }}"
                                   class="btn-primary w-full py-3 text-sm font-bold text-center block">
                                    Lihat Detail Pesanan
                                </a>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- Hapus (hanya jika masih pending) --}}
                @if($commission->status === 'pending')
                    <div class="bg-void-card border border-void-border rounded-2xl p-4">
                        <form action="{{ route('commission.destroy', $commission) }}"
                              method="POST"
                              onsubmit="return confirm('Yakin ingin membatalkan commission ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full py-2 text-sm font-semibold text-red-400 hover:text-red-300 border border-red-500/30 hover:border-red-500/50 rounded-xl transition-all">
                                Batalkan Commission
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection