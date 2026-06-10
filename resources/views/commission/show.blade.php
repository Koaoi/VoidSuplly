@extends('layouts.app')

@section('title', $commission->title)

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <a href="{{ route('commission.index') }}"
           class="inline-flex items-center gap-2 text-xs text-void-gray hover:text-void-accent transition-colors mb-8">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Commission
        </a>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl">
                <p class="text-sm text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                <p class="text-sm text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
            <div>
                <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-1">Commission Request</p>
                <h1 class="text-2xl font-black text-void-white">{{ $commission->title }}</h1>
                <p class="text-xs text-void-gray mt-1">
                    Dikirim {{ $commission->created_at->diffForHumans() }}
                    &nbsp;·&nbsp;
                    {{ $commission->product_type_label }}
                </p>
            </div>

            @php
                $statusColors = [
                    'pending'     => 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
                    'reviewing'   => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
                    'accepted'    => 'bg-purple-500/10 border-purple-500/30 text-purple-400',
                    'in_progress' => 'bg-orange-500/10 border-orange-500/30 text-orange-400',
                    'completed'   => 'bg-green-500/10 border-green-500/30 text-green-400',
                    'rejected'    => 'bg-red-500/10 border-red-500/30 text-red-400',
                    'paid'        => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400',
                ];
            @endphp
            <span class="text-xs font-bold tracking-widest uppercase border px-4 py-2 rounded-full
                         {{ $statusColors[$commission->status] ?? 'bg-void-muted text-void-gray' }}">
                {{ $commission->status_label }}
            </span>
        </div>

        <div class="space-y-5">

            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <h2 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase mb-4">Deskripsi Desain</h2>
                <p class="text-sm text-void-light leading-relaxed whitespace-pre-line">{{ $commission->description }}</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-void-card border border-void-border rounded-xl p-4">
                    <p class="text-[10px] text-void-gray uppercase tracking-wider mb-1">Tipe Produk</p>
                    <p class="text-sm font-bold text-void-white">{{ $commission->product_type_label }}</p>
                </div>
                <div class="bg-void-card border border-void-border rounded-xl p-4">
                    <p class="text-[10px] text-void-gray uppercase tracking-wider mb-1">Jumlah</p>
                    <p class="text-sm font-bold text-void-white">{{ $commission->quantity }} pcs</p>
                </div>
                @if($commission->budget)
                    <div class="bg-void-card border border-void-border rounded-xl p-4">
                        <p class="text-[10px] text-void-gray uppercase tracking-wider mb-1">Budget</p>
                        <p class="text-sm font-bold text-void-white">{{ $commission->formatted_budget }}</p>
                    </div>
                @endif
                @if($commission->quoted_price)
                    <div class="bg-void-card border border-green-500/30 rounded-xl p-4">
                        <p class="text-[10px] text-void-gray uppercase tracking-wider mb-1">Harga Quote Admin</p>
                        <p class="text-sm font-bold text-green-400">{{ $commission->formatted_quoted_price }}</p>
                    </div>
                @endif
            </div>

            @if($commission->reference_image)
                <div class="bg-void-card border border-void-border rounded-2xl p-6">
                    <h2 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase mb-4">Gambar Referensi</h2>
                    <a href="{{ asset('storage/' . $commission->reference_image) }}" target="_blank">
                        <img src="{{ asset('storage/' . $commission->reference_image) }}"
                             alt="Referensi desain"
                             class="max-h-80 rounded-xl object-contain hover:opacity-90 transition-opacity cursor-zoom-in">
                    </a>
                    <p class="text-[10px] text-void-gray mt-2 text-center">Klik gambar untuk membuka ukuran penuh</p>
                </div>
            @endif

            @if($commission->admin_note)
                <div class="bg-void-card border border-blue-500/30 rounded-2xl p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h2 class="text-xs font-bold tracking-[0.2em] text-blue-400 uppercase">Respons dari Tim VOID Supply</h2>
                    </div>
                    <p class="text-sm text-void-light leading-relaxed">{{ $commission->admin_note }}</p>
                </div>
            @endif

            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <h2 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase mb-5">Progress Status</h2>
                @php
                    $steps = [
                        'pending'     => ['Dikirim', 'Request berhasil masuk ke sistem'],
                        'reviewing'   => ['Review', 'Tim sedang meninjau request kamu'],
                        'accepted'    => ['Diterima', 'Request disetujui, menunggu pembayaran'],
                        'in_progress' => ['Produksi', 'Sedang dikerjakan oleh tim VOID'],
                        'completed'   => ['Selesai', 'Commission selesai dan dikirim'],
                    ];
                    $statusOrder   = array_keys($steps);
                    $currentStatus = $commission->status;
                    $currentIdx    = array_search($currentStatus, $statusOrder);
                @endphp

                <div class="space-y-4">
                    @foreach($steps as $status => [$label, $desc])
                        @php
                            $idx  = array_search($status, $statusOrder);
                            $done = $currentStatus !== 'rejected' && $currentStatus !== 'paid' && $idx <= $currentIdx;
                            $isNow = $status === $currentStatus;
                        @endphp
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center border-2
                                            {{ $done ? 'bg-white border-white' : 'bg-void-dark border-void-border' }}">
                                    @if($done)
                                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @else
                                        <div class="w-2 h-2 rounded-full bg-void-muted"></div>
                                    @endif
                                </div>
                                @if(!$loop->last)
                                    <div class="w-0.5 h-6 {{ $done ? 'bg-white' : 'bg-void-border' }} mt-1"></div>
                                @endif
                            </div>
                            <div class="pb-4">
                                <p class="text-sm font-bold {{ $done ? 'text-void-white' : 'text-void-muted' }}">
                                    {{ $label }}
                                    @if($isNow && $currentStatus !== 'rejected' && $currentStatus !== 'paid')
                                        <span class="ml-2 text-[10px] font-bold text-yellow-400 tracking-widest uppercase">← Sekarang</span>
                                    @endif
                                </p>
                                <p class="text-xs {{ $done ? 'text-void-gray' : 'text-void-muted' }} mt-0.5">{{ $desc }}</p>
                            </div>
                        </div>
                    @endforeach

                    @if($commission->status === 'paid')
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 bg-emerald-500/10 border-emerald-500/30">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-emerald-400">Dibayar</p>
                                <p class="text-xs text-void-gray mt-0.5">Pembayaran telah dikonfirmasi, menunggu proses produksi</p>
                            </div>
                        </div>
                    @endif

                    @if($commission->status === 'rejected')
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 bg-red-500/10 border-red-500/30">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-red-400">Ditolak</p>
                                <p class="text-xs text-void-gray mt-0.5">Request tidak dapat diproses</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                @if($commission->status === 'pending')
                    <form method="POST" action="{{ route('commission.destroy', $commission) }}" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Yakin ingin membatalkan commission ini?')" 
                                class="btn-danger w-full py-3 text-sm">Batalkan Request</button>
                    </form>
                @endif

                @if($commission->status === 'accepted' && $commission->quoted_price && !$commission->order_id)
                    <form action="{{ route('commission.process-payment', $commission) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="btn-primary w-full py-3 text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Bayar Sekarang
                        </button>
                    </form>
                @endif

                @if($commission->status === 'paid' && $commission->order_id && $commission->order)
                    <a href="{{ route('orders.show', $commission->order->order_code) }}" 
                       class="btn-primary flex-1 py-3 text-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 5l7 7-7 7"/>
                        </svg>
                        Lihat Pesanan
                    </a>
                @endif

                <a href="{{ route('commission.create') }}" class="btn-secondary flex-1 text-center py-3 text-sm">
                    Buat Request Baru
                </a>
            </div>
        </div>
    </div>
</div>
@endsection