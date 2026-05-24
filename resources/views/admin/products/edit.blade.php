@extends('layouts.admin')
@section('title','Edit Produk')
@section('page-title','Edit — ' . $product->name)
@section('content')
<form method="POST" action="{{ route('admin.products.update',$product) }}"
      enctype="multipart/form-data"
      class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf @method('PUT')

    {{-- LEFT --}}
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-void-card border border-void-border rounded-2xl p-6 space-y-5">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">Informasi Produk</h2>

            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Nama Produk *</label>
                <input type="text" name="name" value="{{ old('name',$product->name) }}"
                       class="input-void @error('name') border-red-500/50 @enderror">
                @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Kategori *</label>
                    <select name="category_id" class="input-void cursor-pointer">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id',$product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Status *</label>
                    <select name="status" class="input-void cursor-pointer">
                        @foreach(['available'=>'Available','preorder'=>'Preorder','coming_soon'=>'Coming Soon','sold_out'=>'Sold Out'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('status',$product->status)===$v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Deskripsi *</label>
                <textarea name="description" rows="5" class="input-void resize-none">{{ old('description',$product->description) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Detail / Spesifikasi</label>
                <textarea name="details" rows="4" class="input-void resize-none font-mono text-xs">{{ old('details',$product->details) }}</textarea>
            </div>
        </div>

        <div class="bg-void-card border border-void-border rounded-2xl p-6 space-y-5">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">Harga & Stok</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Harga (Rp) *</label>
                    <input type="number" name="price" value="{{ old('price',$product->price) }}" min="0" class="input-void">
                </div>
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Stok *</label>
                    <input type="number" name="stock" value="{{ old('stock',$product->stock) }}" min="0" class="input-void">
                </div>
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Berat (gram) *</label>
                    <input type="number" name="weight" value="{{ old('weight',$product->weight) }}" min="0" class="input-void">
                </div>
            </div>
        </div>

        <div class="bg-void-card border border-void-border rounded-2xl p-6">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Ukuran</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(['S','M','L','XL','XXL','XXXL','FREE SIZE'] as $sz)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="sizes[]" value="{{ $sz }}"
                               {{ in_array($sz, old('sizes', $product->sizes ?? [])) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="flex items-center justify-center min-w-[44px] h-10 px-3 rounded-xl
                                     border-2 border-void-border text-xs font-bold text-void-gray
                                     peer-checked:border-void-accent peer-checked:text-void-accent
                                     peer-checked:bg-void-muted/30 hover:border-void-muted
                                     hover:text-void-light transition-all cursor-pointer">
                            {{ $sz }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Current Images --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-6">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">
                Gambar Saat Ini ({{ $product->images->count() }})
            </h2>

            @if($product->images->isNotEmpty())
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-5">
                    @foreach($product->images as $img)
                        <div class="relative aspect-square rounded-xl overflow-hidden bg-void-dark border
                                    {{ $img->is_primary ? 'border-void-accent' : 'border-void-border' }}
                                    group">
                            <img src="{{ $img->url }}" class="w-full h-full object-cover">

                            @if($img->is_primary)
                                <div class="absolute top-1 left-1 text-[8px] font-black bg-white text-black px-1.5 py-0.5 rounded-full">
                                    PRIMARY
                                </div>
                            @endif

                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100
                                        transition-opacity flex flex-col items-center justify-center gap-2">
                                @if(!$img->is_primary)
                                    <button type="button"
                                            onclick="setPrimary({{ $img->id }}, this)"
                                            class="text-[9px] font-bold bg-white text-black px-2 py-1 rounded-lg">
                                        Set Primary
                                    </button>
                                @endif
                                <button type="button"
                                        onclick="deleteImg({{ $img->id }}, this)"
                                        class="text-[9px] font-bold bg-red-500 text-white px-2 py-1 rounded-lg">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-void-gray mb-4">Belum ada gambar.</p>
            @endif

            {{-- Upload new images --}}
            <div>
                <p class="text-xs font-bold text-void-light uppercase tracking-wider mb-2">Tambah Gambar Baru</p>
                <input type="file" name="new_images[]" multiple accept="image/*"
                       class="input-void file:mr-3 file:text-xs file:bg-void-dark file:border
                              file:border-void-border file:text-void-gray file:px-3 file:py-1.5
                              file:rounded-lg file:cursor-pointer">
                <p class="text-[10px] text-void-muted mt-1">Maks. 6 file, 3MB per file.</p>
            </div>
        </div>
    </div>

    {{-- RIGHT --}}
    <div class="space-y-5">
        <div class="bg-void-card border border-void-border rounded-2xl p-5 space-y-4">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">Opsi Produk</h2>
            
            <label class="flex items-center justify-between cursor-pointer">
                <span class="text-sm text-void-light">Limited Edition</span>
                <input type="checkbox" name="is_limited" value="1"
                       {{ old('is_limited',$product->is_limited) ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-void-dark border-void-border text-void-accent focus:ring-0">
            </label>
            
            {{-- RELEASE DATE --}}
            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Release Date</label>
                @php
                    $releaseDateValue = '';
                    if ($product->release_date) {
                        $releaseDateValue = \Carbon\Carbon::parse($product->release_date)->format('Y-m-d\TH:i');
                    }
                @endphp
                <input type="datetime-local" name="release_date"
                       value="{{ old('release_date', $releaseDateValue) }}"
                       class="input-void text-sm">
                <p class="text-[10px] text-void-muted mt-1">Kosongkan jika tidak ada (hanya untuk Coming Soon / Preorder)</p>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-4">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-xs text-red-400">• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button type="submit" class="btn-primary w-full py-3.5">Update Produk</button>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary w-full py-3 text-center block text-sm">Batal</a>
    </div>
</form>

@push('scripts')
<script>
async function deleteImg(id, btn) {
    if (!confirm('Hapus gambar ini?')) return;
    
    const url = `/admin/products/image/${id}`;
    
    try {
        const res = await fetch(url, {
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            btn.closest('.relative').remove();
        } else {
            alert('Gagal menghapus gambar');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    }
}

async function setPrimary(id, btn) {
    const url = `/admin/products/image/${id}/primary`;
    
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert('Gagal mengubah primary image');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    }
}
</script>
@endpush
@endsection