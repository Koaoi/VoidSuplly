cat > /mnt/user-data/outputs/void-supply/resources/views/admin/commissions/show.blade.php << 'BLADE'
@extends('layouts.admin')
@section('title','Commission — ' . $commission->title)
@section('page-title','Commission #' . $commission->id)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Detail --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-void-card border border-void-border rounded-2xl p-6">
            <h2 class="text-lg font-black text-void-white mb-1">{{ $commission->title }}</h2>
            <p class="text-xs text-void-gray mb-5">{{ ucfirst($commission->product_type) }} · {{ $commission->quantity }} pcs · {{ $commission->created_at->format('d M Y H:i') }}</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
                <div class="bg-void-dark border border-void-border rounded-xl p-3">
                    <p class="text-[9px] text-void-gray uppercase tracking-wider mb-1">Tipe Produk</p>
                    <p class="text-sm font-bold text-void-white capitalize">{{ $commission->product_type }}</p>
                </div>
                <div class="bg-void-dark border border-void-border rounded-xl p-3">
                    <p class="text-[9px] text-void-gray uppercase tracking-wider mb-1">Jumlah</p>
                    <p class="text-sm font-bold text-void-white">{{ $commission->quantity }} pcs</p>
                </div>
                @if($commission->budget)
                    <div class="bg-void-dark border border-void-border rounded-xl p-3">
                        <p class="text-[9px] text-void-gray uppercase tracking-wider mb-1">Budget</p>
                        <p class="text-sm font-bold text-void-white">Rp {{ number_format($commission->budget,0,',','.') }}</p>
                    </div>
                @endif
            </div>

            <div class="mb-5">
                <h3 class="text-xs font-bold text-void-white uppercase tracking-wider mb-2">Deskripsi</h3>
                <p class="text-sm text-void-light leading-relaxed whitespace-pre-line">{{ $commission->description }}</p>
            </div>

            @if($commission->reference_image_url)
                <div>
                    <h3 class="text-xs font-bold text-void-white uppercase tracking-wider mb-2">Gambar Referensi</h3>
                    <a href="{{ $commission->reference_image_url }}" target="_blank">
                        <img src="{{ $commission->reference_image_url }}"
                             class="max-h-80 rounded-xl object-contain border border-void-border hover:opacity-80 transition-opacity cursor-zoom-in">
                    </a>
                </div>
            @endif
        </div>

        {{-- Customer info --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            <h3 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Customer</h3>
            <div class="flex items-center gap-3">
                <img src="{{ $commission->user->avatar_url }}" class="w-10 h-10 rounded-full object-cover border border-void-border">
                <div>
                    <p class="text-sm font-bold text-void-white">{{ $commission->user->name }}</p>
                    <p class="text-xs text-void-gray">{{ $commission->user->email }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Update status --}}
    <div class="space-y-5">
        <div class="bg-void-card border border-void-border rounded-2xl p-5">
            @php
                $cc=['pending'=>'text-yellow-400 bg-yellow-500/10 border-yellow-500/30',
                     'reviewing'=>'text-blue-400 bg-blue-500/10 border-blue-500/30',
                     'accepted'=>'text-purple-400 bg-purple-500/10 border-purple-500/30',
                     'in_progress'=>'text-orange-400 bg-orange-500/10 border-orange-500/30',
                     'completed'=>'text-green-400 bg-green-500/10 border-green-500/30',
                     'rejected'=>'text-red-400 bg-red-500/10 border-red-500/30'];
            @endphp
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xs font-bold tracking-widest text-void-white uppercase">Status</h3>
                <span class="text-[10px] font-bold border px-3 py-1 rounded-full {{ $cc[$commission->status] ?? '' }}">
                    {{ $commission->status_label }}
                </span>
            </div>

            <form method="POST" action="{{ route('admin.commissions.status',$commission) }}"
                  class="space-y-4">
                @csrf @method('PATCH')

                <div>
                    <label class="block text-xs text-void-gray mb-2">Update Status</label>
                    <select name="status" class="input-void cursor-pointer text-sm">
                        @foreach(['pending'=>'Pending','reviewing'=>'Reviewing','accepted'=>'Accepted','in_progress'=>'In Progress','completed'=>'Completed','rejected'=>'Rejected'] as $v=>$l)
                            <option value="{{ $v }}" {{ $commission->status===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-void-gray mb-2">Harga Quote (Rp)</label>
                    <input type="number" name="quoted_price" min="0"
                           value="{{ old('quoted_price',$commission->quoted_price) }}"
                           class="input-void text-sm" placeholder="500000">
                </div>

                <div>
                    <label class="block text-xs text-void-gray mb-2">Catatan Admin</label>
                    <textarea name="admin_note" rows="4"
                              class="input-void resize-none text-sm"
                              placeholder="Tulis respons, catatan, atau alasan penolakan...">{{ old('admin_note',$commission->admin_note) }}</textarea>
                </div>

                <button type="submit" class="btn-primary w-full text-sm py-3">
                    Update Commission
                </button>
            </form>
        </div>

        <a href="{{ route('admin.commissions.index') }}" class="btn-secondary w-full text-center py-3 block text-sm">
            ← Kembali ke Daftar
        </a>
    </div>
</div>
@endsection