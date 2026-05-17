@extends('layouts.admin')
@section('title','Tambah Produk')
@section('page-title','Tambah Produk Baru')
@section('content')
<form method="POST" action="{{ route('admin.products.store') }}"
      enctype="multipart/form-data"
      x-data="{ sizes: @json(old('sizes',[])) }"
      class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf

    {{-- LEFT: Main fields --}}
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-void-card border border-void-border rounded-2xl p-6 space-y-5">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">Informasi Produk</h2>

            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Nama Produk *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="input-void @error('name') border-red-500/50 @enderror"
                       placeholder="VOID Core Hoodie Black">
                @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Kategori *</label>
                    <select name="category_id" class="input-void cursor-pointer @error('category_id') border-red-500/50 @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Status *</label>
                    <select name="status" class="input-void cursor-pointer">
                        @foreach(['available'=>'Available','preorder'=>'Preorder','coming_soon'=>'Coming Soon','sold_out'=>'Sold Out'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('status','available')===$v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Deskripsi *</label>
                <textarea name="description" rows="5"
                          class="input-void resize-none @error('description') border-red-500/50 @enderror"
                          placeholder="Deskripsi produk...">{{ old('description') }}</textarea>
                @error('description')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                    Detail / Spesifikasi
                    <span class="text-void-muted font-normal normal-case ml-1">(satu baris = satu poin)</span>
                </label>
                <textarea name="details" rows="4"
                          class="input-void resize-none font-mono text-xs"
                          placeholder="Material: 380gsm Fleece&#10;Fit: Oversized&#10;Cuci: Machine wash cold">{{ old('details') }}</textarea>
            </div>
        </div>

        {{-- Pricing & Stock --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-6 space-y-5">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">Harga & Stok</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Harga (Rp) *</label>
                    <input type="number" name="price" value="{{ old('price') }}" min="0"
                           class="input-void @error('price') border-red-500/50 @enderror"
                           placeholder="350000">
                    @error('price')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Stok *</label>
                    <input type="number" name="stock" value="{{ old('stock',0) }}" min="0"
                           class="input-void @error('stock') border-red-500/50 @enderror">
                    @error('stock')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Berat (gram) *</label>
                    <input type="number" name="weight" value="{{ old('weight',300) }}" min="0"
                           class="input-void @error('weight') border-red-500/50 @enderror">
                    @error('weight')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Sizes --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-6">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-4">Ukuran Tersedia</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(['S','M','L','XL','XXL','XXXL','FREE SIZE'] as $sz)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="sizes[]" value="{{ $sz }}"
                               {{ in_array($sz, old('sizes',[])) ? 'checked' : '' }}
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

        {{-- Images --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-6"
             x-data="{ previews: [] }">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-2">
                Gambar Produk
                <span class="text-void-muted font-normal normal-case ml-1">(maks. 6 foto, gambar pertama = primary)</span>
            </h2>

            <label class="block cursor-pointer mt-4">
                <input type="file" name="images[]" multiple accept="image/*"
                       class="sr-only"
                       @change="
                           previews = [];
                           Array.from($event.target.files).forEach(f => {
                               const r = new FileReader();
                               r.onload = e => previews.push(e.target.result);
                               r.readAsDataURL(f);
                           });
                       ">
                <div class="border-2 border-dashed border-void-border rounded-xl p-8 text-center
                            hover:border-void-muted transition-colors">
                    <svg class="w-10 h-10 text-void-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm text-void-light font-medium">Klik atau drag untuk upload</p>
                    <p class="text-xs text-void-gray mt-1">JPG, PNG, WEBP — maks. 3MB per file</p>
                </div>
            </label>

            <div x-show="previews.length > 0" class="grid grid-cols-3 sm:grid-cols-6 gap-3 mt-4">
                <template x-for="(src, i) in previews" :key="i">
                    <div class="relative aspect-square rounded-xl overflow-hidden bg-void-dark border border-void-border">
                        <img :src="src" class="w-full h-full object-cover">
                        <div x-show="i === 0"
                             class="absolute top-1 left-1 text-[8px] font-black bg-white text-black
                                    px-1.5 py-0.5 rounded-full">
                            PRIMARY
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- RIGHT: Sidebar options --}}
    <div class="space-y-5">

        <div class="bg-void-card border border-void-border rounded-2xl p-5 space-y-4">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">Opsi Produk</h2>

            <label class="flex items-center justify-between cursor-pointer">
                <span class="text-sm text-void-light">Limited Edition</span>
                <input type="checkbox" name="is_limited" value="1"
                       {{ old('is_limited') ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-void-dark border-void-border text-void-accent focus:ring-0">
            </label>

            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                    Release Date
                    <span class="text-void-muted font-normal normal-case ml-1">(untuk countdown)</span>
                </label>
                <input type="datetime-local" name="release_date" value="{{ old('release_date') }}"
                       class="input-void text-sm">
            </div>
        </div>

        <div class="bg-void-card border border-void-border rounded-2xl p-5 space-y-3">
            <h2 class="text-xs font-bold tracking-widest text-void-white uppercase">Validasi Error</h2>
            @if($errors->any())
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-xs text-red-400 flex items-start gap-1.5">
                            <svg class="w-3 h-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs text-void-muted">Tidak ada error.</p>
            @endif
        </div>

        <button type="submit" class="btn-primary w-full py-3.5">
            Simpan Produk
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary w-full py-3 text-center block text-sm">
            Batal
        </a>
    </div>
</form>
@endsection