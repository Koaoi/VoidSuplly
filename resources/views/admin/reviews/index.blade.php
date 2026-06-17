@extends('layouts.admin')
@section('title','Moderasi Review')
@section('page-title','Reviews')

@push('styles')
<style>
    /* ⭐ STYLE HITAM PUTIH ⭐ */
    .btn-void-filter, .btn-void-reset {
        padding: 10px 20px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        height: 44px !important;
        cursor: pointer !important;
        border: 1px solid #333333 !important;
        background: transparent !important;
        color: #aaaaaa !important;
        text-decoration: none !important;
    }
    
    .btn-void-filter:hover, .btn-void-reset:hover {
        background: rgba(255,255,255,0.05) !important;
        border-color: #666666 !important;
        color: #ffffff !important;
    }
    
    .btn-void-primary {
        padding: 10px 20px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        height: 44px !important;
        cursor: pointer !important;
        border: 1px solid #555555 !important;
        background: #222222 !important;
        color: #ffffff !important;
        white-space: nowrap !important;
    }
    .btn-void-primary:hover {
        background: #333333 !important;
        border-color: #777777 !important;
    }
    
    .btn-void-danger {
        padding: 10px 20px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        height: 44px !important;
        cursor: pointer !important;
        border: 1px solid #442222 !important;
        background: transparent !important;
        color: #cc6666 !important;
        white-space: nowrap !important;
    }
    .btn-void-danger:hover {
        background: rgba(255,50,50,0.05) !important;
        border-color: #884444 !important;
        color: #ff8888 !important;
    }
    
    .btn-void-sm {
        padding: 6px 16px !important;
        border-radius: 8px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        height: 32px !important;
        cursor: pointer !important;
        border: 1px solid #333333 !important;
        background: transparent !important;
        color: #aaaaaa !important;
        text-decoration: none !important;
    }
    .btn-void-sm:hover {
        background: rgba(255,255,255,0.05) !important;
        border-color: #666666 !important;
        color: #ffffff !important;
    }
    .btn-void-sm.success {
        border-color: #224422 !important;
        color: #66cc88 !important;
    }
    .btn-void-sm.success:hover {
        background: rgba(50,255,100,0.05) !important;
        border-color: #448844 !important;
        color: #88ffaa !important;
    }
    .btn-void-sm.danger {
        border-color: #442222 !important;
        color: #cc6666 !important;
    }
    .btn-void-sm.danger:hover {
        background: rgba(255,50,50,0.05) !important;
        border-color: #884444 !important;
        color: #ff8888 !important;
    }
    
    .input-void {
        background: #111111 !important;
        border: 1px solid #333333 !important;
        border-radius: 12px !important;
        padding: 10px 14px !important;
        color: #e5e7eb !important;
        font-size: 13px !important;
        height: 44px !important;
        transition: border-color 0.2s !important;
        width: 100%;
    }
    .input-void:focus {
        outline: none !important;
        border-color: #666666 !important;
        box-shadow: 0 0 0 3px rgba(255,255,255,0.05) !important;
    }
    .input-void option {
        background: #111111 !important;
        color: #e5e7eb !important;
    }
    
    .textarea-void {
        background: #111111 !important;
        border: 1px solid #333333 !important;
        border-radius: 12px !important;
        padding: 10px 14px !important;
        color: #e5e7eb !important;
        font-size: 13px !important;
        width: 100% !important;
        resize: vertical !important;
        min-height: 60px !important;
        transition: border-color 0.2s !important;
        font-family: inherit !important;
    }
    .textarea-void:focus {
        outline: none !important;
        border-color: #666666 !important;
        box-shadow: 0 0 0 3px rgba(255,255,255,0.05) !important;
    }
    .textarea-void::placeholder {
        color: #555555 !important;
    }
    
    .reply-section {
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        padding: 12px 16px;
        margin-top: 12px;
        border-left: 3px solid #555555;
    }
    .reply-section .reply-author {
        color: #888888;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: 0.5px;
    }
    .reply-section .reply-text {
        color: #cccccc;
        font-size: 13px;
        margin-top: 4px;
        line-height: 1.6;
    }
    .reply-section .reply-time {
        color: #555555;
        font-size: 10px;
        margin-top: 4px;
    }
    
    .badge-approved {
        font-size: 10px !important;
        font-weight: 600 !important;
        color: #66cc88 !important;
        background: rgba(50,200,100,0.08) !important;
        border: 1px solid #224422 !important;
        padding: 2px 10px !important;
        border-radius: 20px !important;
    }
    .badge-hidden {
        font-size: 10px !important;
        font-weight: 600 !important;
        color: #cc6666 !important;
        background: rgba(200,50,50,0.08) !important;
        border: 1px solid #442222 !important;
        padding: 2px 10px !important;
        border-radius: 20px !important;
    }
</style>
@endpush

@section('content')
{{-- Filter --}}
<form method="GET" action="{{ route('admin.reviews.index') }}" class="flex flex-wrap gap-3 mb-5">
    <select name="approved" class="input-void w-44 cursor-pointer">
        <option value="">Semua Review</option>
        <option value="1" {{ request('approved')==='1'?'selected':'' }}>Approved</option>
        <option value="0" {{ request('approved')==='0'?'selected':'' }}>Pending / Rejected</option>
    </select>
    <button type="submit" class="btn-void-filter">Filter</button>
    <a href="{{ route('admin.reviews.index') }}" class="btn-void-reset">Reset</a>
</form>

<div class="space-y-3">
    @forelse($reviews as $review)
        <div class="bg-void-card border border-void-border rounded-2xl p-5 hover:border-void-muted transition-colors">
            {{-- Header Review --}}
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
                    @if($review->is_approved)
                        <span class="badge-approved">Approved</span>
                    @else
                        <span class="badge-hidden">Hidden</span>
                    @endif
                </div>
            </div>

            {{-- Comment --}}
            @if($review->comment)
                <p class="text-sm text-void-light leading-relaxed mb-3">{{ $review->comment }}</p>
            @endif

            {{-- Image --}}
            @if($review->image_url)
                <div class="mb-3">
                    <a href="{{ $review->image_url }}" target="_blank">
                        <img src="{{ $review->image_url }}" class="w-20 h-20 rounded-xl object-cover border border-void-border hover:opacity-80 transition-opacity">
                    </a>
                </div>
            @endif

            {{-- ⭐ BALASAN REVIEW (jika ada) ⭐ --}}
            @if($review->admin_reply)
                <div class="reply-section">
                    <div class="flex items-center gap-2">
                        <span class="reply-author">Admin Reply</span>
                        <span class="text-[10px] text-void-gray">· {{ $review->admin_reply_updated_at ? $review->admin_reply_updated_at->diffForHumans() : '' }}</span>
                    </div>
                    <p class="reply-text">{{ $review->admin_reply }}</p>
                </div>
            @endif

            {{-- ⭐ FORM BALAS REVIEW ⭐ --}}
            <div class="mt-3">
                <form id="reply-form-{{ $review->id }}" action="{{ route('admin.reviews.reply', $review) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="flex flex-wrap gap-3 items-start">
                        <textarea 
                            name="admin_reply" 
                            placeholder="Reply to this review as admin..." 
                            rows="2"
                            class="textarea-void flex-1 min-w-[200px]"
                        >{{ $review->admin_reply }}</textarea>
                        
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <button type="submit" class="btn-void-primary">
                                Send Reply
                            </button>
                            
                            @if($review->admin_reply)
                                <button type="submit" 
                                        formaction="{{ route('admin.reviews.reply', $review) }}?delete=1"
                                        class="btn-void-danger"
                                        onclick="return confirm('Hapus balasan ini?')">
                                    Delete
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Aksi Moderasi --}}
            <div class="flex items-center gap-3 pt-3 border-t border-void-border mt-3">
                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="btn-void-sm {{ $review->is_approved ? 'danger' : 'success' }}">
                        {{ $review->is_approved ? 'Hide' : 'Approve' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                      id="delete-review-form-{{ $review->id }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="btn-void-sm danger"
                            onclick="return confirm('Hapus review ini permanen?')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-void-card border border-void-border rounded-2xl p-12 text-center">
            <p class="text-void-gray">No reviews found.</p>
        </div>
    @endforelse

    @if($reviews->hasPages())
        <div class="mt-4">{{ $reviews->links('components.pagination') }}</div>
    @endif
</div>
@endsection