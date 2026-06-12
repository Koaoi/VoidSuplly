@extends('layouts.admin')
@section('title','Manajemen Commission')
@section('page-title','Commission Request')

@section('content')
@if(session('success'))
    <div class="mb-4 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-sm text-green-400">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-sm text-red-400">
        {{ session('error') }}
    </div>
@endif

<form method="GET" action="{{ route('admin.commissions.index') }}" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Judul / nama user..." class="input-void w-64 text-sm">
    <select name="status" class="input-void w-44 text-sm cursor-pointer">
        <option value="">Semua Status</option>
        @foreach(['pending'=>'Pending','reviewing'=>'Reviewing','accepted'=>'Accepted','in_progress'=>'In Progress','completed'=>'Completed','rejected'=>'Rejected','paid'=>'Paid'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-primary text-sm px-5">Filter</button>
    <a href="{{ route('admin.commissions.index') }}" class="btn-secondary text-sm px-5">Reset</a>
</form>

{{-- Status tabs --}}
<div class="flex flex-wrap gap-2 mb-5">
    @foreach([''=>'Semua','pending'=>'Pending','reviewing'=>'Review','accepted'=>'Accepted','in_progress'=>'In Progress','completed'=>'Selesai','rejected'=>'Ditolak','paid'=>'Dibayar'] as $v=>$l)
        <a href="{{ route('admin.commissions.index', $v ? ['status'=>$v] : []) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all
                  {{ request('status','')===$v ? 'bg-white text-black' : 'bg-void-card border border-void-border text-void-gray hover:border-void-muted' }}">
            {{ $l }}
            @if($v && isset($statusCounts[$v]) && $statusCounts[$v] > 0)
                <span class="opacity-70">({{ $statusCounts[$v] }})</span>
            @endif
        </a>
    @endforeach
</div>

<div class="space-y-4">
    @forelse($commissions as $comm)
        @php
            $cc=[
                'pending'=>'text-yellow-400 bg-yellow-500/10 border-yellow-500/30',
                'reviewing'=>'text-blue-400 bg-blue-500/10 border-blue-500/30',
                'accepted'=>'text-purple-400 bg-purple-500/10 border-purple-500/30',
                'in_progress'=>'text-orange-400 bg-orange-500/10 border-orange-500/30',
                'completed'=>'text-green-400 bg-green-500/10 border-green-500/30',
                'rejected'=>'text-red-400 bg-red-500/10 border-red-500/30',
                'paid'=>'text-emerald-400 bg-emerald-500/10 border-emerald-500/30',
            ];
        @endphp
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden hover:border-void-muted transition-colors">
            <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4 border-b border-void-border">
                <div>
                    <p class="text-sm font-bold text-void-white">{{ $comm->title }}</p>
                    <p class="text-xs text-void-gray mt-0.5">
                        {{ $comm->user->name }} · {{ $comm->product_type_label ?? ucfirst($comm->product_type) }} · {{ $comm->quantity }} pcs
                        · {{ $comm->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="text-[10px] font-bold border px-3 py-1 rounded-full {{ $cc[$comm->status] ?? '' }}">
                    {{ $comm->status_label }}
                </span>
            </div>

            {{-- BODY dengan Gambar Referensi --}}
            <div class="px-5 py-4 flex flex-col sm:flex-row gap-4">
                
                {{-- 🔥 GAMBAR REFERENSI --}}
                @if($comm->reference_image)
                    <div class="w-full sm:w-20 h-20 rounded-xl overflow-hidden bg-void-dark shrink-0">
                        <img src="{{ asset('storage/' . $comm->reference_image) }}"
                             alt="Referensi"
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null; this.src='{{ asset('images/placeholder.jpg') }}';">
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

                {{-- INFO --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-void-gray line-clamp-2 leading-relaxed">{{ $comm->description }}</p>

                    <div class="flex flex-wrap gap-4 mt-3 text-xs">
                        <div>
                            <span class="text-void-muted">Jumlah: </span>
                            <span class="text-void-light font-semibold">{{ $comm->quantity }} pcs</span>
                        </div>
                        @if($comm->budget)
                            <div>
                                <span class="text-void-muted">Budget: </span>
                                <span class="text-void-light font-semibold">
                                    {{ $comm->formatted_budget ?? 'Rp ' . number_format($comm->budget,0,',','.') }}
                                </span>
                            </div>
                        @endif
                        @if($comm->quoted_price)
                            <div>
                                <span class="text-void-muted">Quote Admin: </span>
                                <span class="text-green-400 font-bold">
                                    {{ $comm->formatted_quoted_price ?? 'Rp ' . number_format($comm->quoted_price,0,',','.') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @if($comm->admin_note)
                        <div class="mt-3 p-2 bg-void-dark rounded-lg border border-void-border">
                            <p class="text-[9px] font-bold text-void-gray uppercase tracking-wider mb-0.5">Catatan Admin</p>
                            <p class="text-xs text-void-light line-clamp-2">{{ $comm->admin_note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="px-5 py-3 border-t border-void-border bg-void-darker flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.commissions.show',$comm) }}"
                   class="text-xs text-void-gray hover:text-void-accent transition-colors
                          px-3 py-1.5 border border-void-border rounded-lg hover:border-void-muted">
                    Review Detail
                </a>

                @if($comm->quoted_price && $comm->status === 'accepted' && !$comm->order_id)
                    <span class="text-xs text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-full">
                        Menunggu Pembayaran
                    </span>
                @endif

                @if($comm->status === 'paid' && $comm->order_id)
                    <a href="{{ route('admin.orders.show', $comm->order_id) }}" 
                       class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                        Lihat Order #{{ $comm->order->order_code ?? $comm->order_id }}
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-void-card border border-void-border rounded-2xl p-12 text-center">
            <p class="text-void-gray">Tidak ada commission request.</p>
        </div>
    @endforelse

    @if($commissions->hasPages())
        <div class="mt-4">{{ $commissions->links('components.pagination') }}</div>
    @endif
</div>
@endsection