@extends('layouts.admin')
@section('title','Manajemen Produk')
@section('page-title','Produk')
@section('header-actions')
    <a href="{{ route('admin.products.create') }}" class="btn-primary text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Produk
    </a>
@endsection

@section('content')
{{-- Filter --}}
<form method="GET" action="{{ route('admin.products.index') }}"
      class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Cari nama produk..." class="input-void w-56 text-sm">
    <select name="category" class="input-void w-44 text-sm cursor-pointer">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <select name="status" class="input-void w-44 text-sm cursor-pointer">
        <option value="">Semua Status</option>
        @foreach(['available'=>'Available','sold_out'=>'Sold Out','preorder'=>'Preorder','coming_soon'=>'Coming Soon'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')===$v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-primary text-sm px-5">Filter</button>
    <a href="{{ route('admin.products.index') }}" class="btn-secondary text-sm px-5">Reset</a>
</form>

<div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-void-border">
                <th class="text-left px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Produk</th>
                <th class="text-left px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider hidden md:table-cell">Kategori</th>
                <th class="text-right px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Harga</th>
                <th class="text-center px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Stok</th>
                <th class="text-center px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Status</th>
                <th class="text-right px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-void-border">
            @forelse($products as $product)
                @php
                    $sc=['available'=>'text-green-400 bg-green-500/10 border-green-500/30',
                         'sold_out'=>'text-red-400 bg-red-500/10 border-red-500/30',
                         'preorder'=>'text-yellow-400 bg-yellow-500/10 border-yellow-500/30',
                         'coming_soon'=>'text-blue-400 bg-blue-500/10 border-blue-500/30'];
                    $sl=['available'=>'Available','sold_out'=>'Sold Out','preorder'=>'Preorder','coming_soon'=>'Coming Soon'];
                @endphp
                <tr class="hover:bg-void-muted/10 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            {{-- 🔥 GAMBAR PRODUK (DIPERBAIKI) --}}
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-void-dark shrink-0">
                                @if($product->primary_image_url)
                                    <img src="{{ $product->primary_image_url }}" 
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.src='{{ asset('images/placeholder.jpg') }}';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-void-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-void-white line-clamp-1">{{ $product->name }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($product->is_limited)
                                        <span class="badge-limited text-[8px]">Limited</span>
                                    @endif
                                    <p class="text-[10px] text-void-gray">{{ $product->order_items_count }} terjual</p>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        <span class="text-void-gray text-xs">{{ $product->category->name ?? '-' }}</span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <span class="text-void-white font-bold text-xs">{{ $product->formatted_price }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-bold text-sm {{ $product->stock <= 5 ? 'text-red-400' : ($product->stock <= 15 ? 'text-orange-400' : 'text-void-white') }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-[10px] font-bold border px-2.5 py-1 rounded-full {{ $sc[$product->status] ?? 'text-void-gray' }}">
                            {{ $sl[$product->status] ?? $product->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.products.edit',$product) }}"
                               class="text-xs text-void-gray hover:text-void-accent transition-colors px-3 py-1.5 border border-void-border rounded-lg hover:border-void-muted">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy',$product) }}"
                                  onsubmit="return confirm('Soft delete produk ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-300 px-3 py-1.5 border border-red-500/20 rounded-lg hover:bg-red-500/10">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-void-gray">Belum ada produk.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($products->hasPages())
        <div class="px-5 py-4 border-t border-void-border">
            {{ $products->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection