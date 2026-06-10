@extends('layouts.app')

@section('title', 'Commission Saya')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">— Custom Order</p>
                <h1 class="text-3xl font-black text-void-accent">Commission Saya</h1>
            </div>
            <a href="{{ route('commission.create') }}" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Request Baru
            </a>
        </div>

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

        @if($commissions->isNotEmpty())
            <div class="space-y-4">
                @foreach($commissions as $commission)
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
                        $colorClass = $statusColors[$commission->status] ?? 'bg-void-muted text-void-gray';
                    @endphp

                    <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden hover:border-void-muted transition-colors">

                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-void-border">
                            <div>
                                <p class="text-sm font-bold text-void-white">{{ $commission->title }}</p>
                                <p class="text-xs text-void-gray mt-0.5">
                                    {{ $commission->product_type_label }}
                                    &nbsp;·&nbsp;
                                    {{ $commission->created_at->format('d M Y') }}
                                </p>
                            </div>
                            <span class="text-[10px] font-bold tracking-widest uppercase border px-3 py-1 rounded-full {{ $colorClass }}">
                                {{ $commission->status_label }}
                            </span>
                        </div>

                        <div class="px-5 py-4 flex flex-col sm:flex-row gap-4">

                            @if($commission->reference_image)
                                <div class="w-full sm:w-20 h-20 rounded-xl overflow-hidden bg-void-dark shrink-0">
                                    <img src="{{ asset('storage/' . $commission->reference_image) }}"
                                         alt="Referensi"
                                         class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="hidden sm:flex w-20 h-20 rounded-xl bg-void-dark shrink-0
                                            items-center justify-center border border-void-border">
                                    <svg class="w-8 h-8 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-void-gray line-clamp-2 leading-relaxed">{{ $commission->description }}</p>

                                <div class="flex flex-wrap gap-4 mt-3 text-xs">
                                    <div>
                                        <span class="text-void-muted">Jumlah: </span>
                                        <span class="text-void-light font-semibold">{{ $commission->quantity }} pcs</span>
                                    </div>
                                    @if($commission->budget)
                                        <div>
                                            <span class="text-void-muted">Budget: </span>
                                            <span class="text-void-light font-semibold">{{ $commission->formatted_budget }}</span>
                                        </div>
                                    @endif
                                    @if($commission->quoted_price)
                                        <div>
                                            <span class="text-void-muted">Quote Admin: </span>
                                            <span class="text-green-400 font-bold">{{ $commission->formatted_quoted_price }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if($commission->admin_note)
                                    <div class="mt-3 p-2 bg-void-dark rounded-lg border border-void-border">
                                        <p class="text-[9px] font-bold text-void-gray uppercase tracking-wider mb-0.5">Catatan Admin</p>
                                        <p class="text-xs text-void-light line-clamp-1">{{ $commission->admin_note }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- FOOTER ACTIONS --}}
                        <div class="px-5 py-3 border-t border-void-border bg-void-darker flex flex-wrap items-center gap-3">
                            <a href="{{ route('commission.show', $commission) }}"
                               class="text-xs font-semibold text-void-light hover:text-void-accent transition-colors flex items-center gap-1.5">
                                Lihat Detail
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            {{-- 🔥 TOMBOL BAYAR UNTUK STATUS ACCEPTED --}}
                            @if($commission->status === 'accepted' && $commission->quoted_price && !$commission->order_id)
                                <form action="{{ route('commission.process-payment', $commission) }}" method="POST" class="ml-auto">
                                    @csrf
                                    <button type="submit" class="btn-primary text-xs py-1.5 px-4 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Bayar Sekarang
                                    </button>
                                </form>
                            @endif

                            {{-- 🔥 TOMBOL LIHAT PESANAN UNTUK STATUS PAID --}}
                            @if($commission->status === 'paid' && $commission->order_id && $commission->order)
                                <a href="{{ route('orders.show', $commission->order->order_code) }}" 
                                   class="ml-auto btn-secondary text-xs py-1.5 px-4 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M9 5l7 7-7 7"/>
                                    </svg>
                                    Lihat Pesanan
                                </a>
                            @endif

                            {{-- TOMBOL BATALKAN UNTUK STATUS PENDING --}}
                            @if($commission->status === 'pending')
                                <form method="POST" action="{{ route('commission.destroy', $commission) }}" class="ml-auto" onsubmit="return confirm('Yakin ingin membatalkan commission ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Batalkan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $commissions->links('components.pagination') }}
            </div>

        @else
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-24 h-24 rounded-2xl bg-void-card border border-void-border flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-void-white mb-2">Belum ada commission</h2>
                <p class="text-sm text-void-gray mb-8 max-w-xs leading-relaxed">
                    Punya ide desain? Submit commission request dan kami wujudkan untukmu.
                </p>
                <a href="{{ route('commission.create') }}" class="btn-primary">Buat Commission Request</a>
            </div>
        @endif
    </div>
</div>
@endsection