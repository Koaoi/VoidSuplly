@extends('layouts.admin')
@section('title','Manajemen Kategori')
@section('page-title','Kategori')
@section('page-subtitle','Kelola kategori produk')
@section('header-actions')
    <a href="{{ route('admin.categories.create') }}" class="btn-primary text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Kategori
    </a>
@endsection

@section('content')
<div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-void-border">
                <th class="text-left px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Kategori</th>
                <th class="text-left px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider hidden sm:table-cell">Deskripsi</th>
                <th class="text-center px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Produk</th>
                <th class="text-center px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Status</th>
                <th class="text-right px-5 py-3 text-xs font-bold text-void-gray uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-void-border">
            @forelse($categories as $cat)
                <tr class="hover:bg-void-muted/10 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($cat->image)
                                <img src="{{ $cat->image_url }}" class="w-9 h-9 rounded-lg object-cover">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-void-dark flex items-center justify-center">
                                    <span class="text-sm font-black text-void-muted">{{ substr($cat->name,0,1) }}</span>
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-void-white">{{ $cat->name }}</p>
                                <p class="text-[10px] text-void-gray">{{ $cat->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell">
                        <p class="text-void-gray text-xs line-clamp-2">{{ $cat->description ?: '—' }}</p>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-void-white font-bold">{{ $cat->products_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if($cat->is_active)
                            <span class="text-[10px] font-bold text-green-400 bg-green-500/10 border border-green-500/30 px-2.5 py-1 rounded-full">Aktif</span>
                        @else
                            <span class="text-[10px] font-bold text-void-gray bg-void-muted/20 border border-void-border px-2.5 py-1 rounded-full">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.categories.edit',$cat) }}"
                               class="text-xs text-void-gray hover:text-void-accent transition-colors px-3 py-1.5 border border-void-border rounded-lg hover:border-void-muted">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.categories.destroy',$cat) }}"
                                  onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-400 hover:text-red-300 transition-colors px-3 py-1.5 border border-red-500/20 rounded-lg hover:bg-red-500/10">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-void-gray">Belum ada kategori.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($categories->hasPages())
        <div class="px-5 py-4 border-t border-void-border">
            {{ $categories->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection



