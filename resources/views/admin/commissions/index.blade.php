@extends('layouts.admin')
@section('title','Manajemen Commission')
@section('page-title','Commission Request')

@section('content')
<form method="GET" action="{{ route('admin.commissions.index') }}" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Judul / nama user..." class="input-void w-64 text-sm">
    <select name="status" class="input-void w-44 text-sm cursor-pointer">
        <option value="">Semua Status</option>
        @foreach(['pending'=>'Pending','reviewing'=>'Reviewing','accepted'=>'Accepted','in_progress'=>'In Progress','completed'=>'Completed','rejected'=>'Rejected'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-primary text-sm px-5">Filter</button>
    <a href="{{ route('admin.commissions.index') }}" class="btn-secondary text-sm px-5">Reset</a>
</form>

{{-- Status tabs --}}
<div class="flex flex-wrap gap-2 mb-5">
    @foreach([''=>'Semua','pending'=>'Pending','reviewing'=>'Review','accepted'=>'Accepted','in_progress'=>'In Progress','completed'=>'Selesai','rejected'=>'Ditolak'] as $v=>$l)
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
            $cc=['pending'=>'text-yellow-400 bg-yellow-500/10 border-yellow-500/30',
                 'reviewing'=>'text-blue-400 bg-blue-500/10 border-blue-500/30',
                 'accepted'=>'text-purple-400 bg-purple-500/10 border-purple-500/30',
                 'in_progress'=>'text-orange-400 bg-orange-500/10 border-orange-500/30',
                 'completed'=>'text-green-400 bg-green-500/10 border-green-500/30',
                 'rejected'=>'text-red-400 bg-red-500/10 border-red-500/30'];
        @endphp
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden hover:border-void-muted transition-colors">
            <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4 border-b border-void-border">
                <div>
                    <p class="text-sm font-bold text-void-white">{{ $comm->title }}</p>
                    <p class="text-xs text-void-gray mt-0.5">
                        {{ $comm->user->name }} · {{ ucfirst($comm->product_type) }} · {{ $comm->quantity }} pcs
                        · {{ $comm->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="text-[10px] font-bold border px-3 py-1 rounded-full {{ $cc[$comm->status] ?? '' }}">
                    {{ $comm->status_label }}
                </span>
            </div>
            <div class="flex items-center justify-between gap-4 px-5 py-3">
                <p class="text-xs text-void-gray line-clamp-1 flex-1">{{ $comm->description }}</p>
                @if($comm->budget)
                    <p class="text-xs text-void-light font-bold shrink-0">
                        Budget: Rp {{ number_format($comm->budget,0,',','.') }}
                    </p>
                @endif
                <a href="{{ route('admin.commissions.show',$comm) }}"
                   class="shrink-0 text-xs text-void-gray hover:text-void-accent transition-colors
                          px-3 py-1.5 border border-void-border rounded-lg hover:border-void-muted">
                    Review
                </a>
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