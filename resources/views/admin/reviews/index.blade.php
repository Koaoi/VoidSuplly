@extends('layouts.admin')
@section('title','Moderasi Review')
@section('page-title','Reviews')

@section('content')
<form method="GET" action="{{ route('admin.reviews.index') }}" class="flex flex-wrap gap-3 mb-5">
    <select name="approved" class="input-void w-44 text-sm cursor-pointer">
        <option value="">Semua Review</option>
        <option value="1" {{ request('approved')==='1'?'selected':'' }}>Approved</option>
        <option value="0" {{ request('approved')==='0'?'selected':'' }}>Pending/Rejected</option>
    </select>
    <button type="submit" class="btn-primary text-sm px-5">Filter</button>
    <a href="{{ route('admin.reviews.index') }}" class="btn-secondary text-sm px-5">Reset</a>
</form>

<div class="space-y-3">
    @forelse($reviews as $review)
        <div class="bg-void-card border border-void-border rounded-2xl p-5 hover:border-void-muted transition-colors">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div class="flex items-center gap-3">
                    <img src="{{ $review->user->avatar_url }}" class="w-9 h-9 rounded-full object-cover border border-void-border shrink-0">
                    <div>
                        <p class="text-sm font-bold text-void-white">{{ $review->user->name }}</p>
                        <p class="text-[10px] text-void-gray">
                            untuk <span class="text-void-light">{{ $review->product->name ?? '—' }}</span>
                            · {{ $review->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    {{-- Stars --}}
                    <div class="flex gap-0.5">
                        @for($i=1;$i<=5;$i++)
                            <svg class="w-3.5 h-3.5 {{ $i<=$review->rating ? 'text-yellow-400':'text-void-muted' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    {{-- Approved badge --}}
                    @if($review->is_approved)
                        <span class="text-[10px] font-bold text-green-400 bg-green-500/10 border border-green-500/30 px-2 py-0.5 rounded-full">Approved</span>
                    @else
                        <span class="text-[10px] font-bold text-red-400 bg-red-500/10 border border-red-500/30 px-2 py-0.5 rounded-full">Hidden</span>
                    @endif
                </div>
            </div>

            @if($review->comment)
                <p class="text-sm text-void-light leading-relaxed mb-3">{{ $review->comment }}</p>
            @endif

            @if($review->image_url)
                <div class="mb-3">
                    <a href="{{ $review->image_url }}" target="_blank">
                        <img src="{{ $review->image_url }}" class="w-20 h-20 rounded-xl object-cover border border-void-border hover:opacity-80 transition-opacity">
                    </a>
                </div>
            @endif

            <div class="flex items-center gap-3 pt-3 border-t border-void-border">
                <form method="POST" action="{{ route('admin.reviews.approve',$review) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="text-xs px-4 py-1.5 rounded-lg border transition-colors
                                   {{ $review->is_approved
                                       ? 'border-void-border text-void-gray hover:border-void-muted'
                                       : 'border-green-500/30 text-green-400 hover:bg-green-500/10' }}">
                        {{ $review->is_approved ? 'Sembunyikan' : 'Approve' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.reviews.destroy',$review) }}"
                      onsubmit="return confirm('Hapus review ini permanen?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs px-4 py-1.5 rounded-lg border border-red-500/20 text-red-400 hover:bg-red-500/10 transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-void-card border border-void-border rounded-2xl p-12 text-center">
            <p class="text-void-gray">Tidak ada review.</p>
        </div>
    @endforelse

    @if($reviews->hasPages())
        <div class="mt-4">{{ $reviews->links('components.pagination') }}</div>
    @endif
</div>
@endsection