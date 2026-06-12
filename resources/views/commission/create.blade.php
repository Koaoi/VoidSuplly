@extends('layouts.app')

@section('title', 'Buat Commission Request - VOID Supply')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-purple-500/10 border border-purple-500/30
                        flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-1">— Custom Order</p>
            <h1 class="text-3xl font-black text-void-accent">Buat Commission Request</h1>
            <p class="text-void-gray text-sm mt-2">Isi detail desain yang ingin kamu wujudkan</p>
        </div>

        {{-- Form --}}
        <div class="bg-void-card border border-void-border rounded-2xl p-6">
            <form action="{{ route('commission.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Judul --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-void-white uppercase tracking-wider mb-2">
                        Judul Commission <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="w-full bg-void-dark border border-void-border text-void-white placeholder-void-gray
                                  rounded-xl px-4 py-3 text-sm
                                  focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted
                                  @error('title') border-red-500 @enderror"
                           placeholder="Contoh: Desain Hoodie Streetwear Premium" required>
                    @error('title')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipe Produk --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-void-white uppercase tracking-wider mb-2">
                        Tipe Produk <span class="text-red-400">*</span>
                    </label>
                    <select name="product_type" 
                            class="w-full bg-void-dark border border-void-border text-void-white
                                   rounded-xl px-4 py-3 text-sm appearance-none
                                   focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted
                                   @error('product_type') border-red-500 @enderror"
                            required>
                        <option value="">Pilih tipe produk</option>
                        @foreach($productTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('product_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('product_type')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-void-white uppercase tracking-wider mb-2">
                        Deskripsi Desain <span class="text-red-400">*</span>
                    </label>
                    <textarea name="description" rows="6"
                              class="w-full bg-void-dark border border-void-border text-void-white placeholder-void-gray
                                     rounded-xl px-4 py-3 text-sm resize-none
                                     focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted
                                     @error('description') border-red-500 @enderror"
                              placeholder="Jelaskan detail desain yang kamu inginkan...
&#10;Contoh:
- Warna: Hitam dengan aksen merah
- Motif: Grafis abstrak di bagian dada
- Bahan: Cotton combed 30s
- Referensi style: Streetwear Jepang"
                              required>{{ old('description') }}</textarea>
                    <p class="text-[10px] text-void-muted mt-1">Minimal 30 karakter. Semakin detail, semakin baik.</p>
                    @error('description')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jumlah & Budget --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-void-white uppercase tracking-wider mb-2">
                            Jumlah <span class="text-red-400">*</span>
                        </label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                               class="w-full bg-void-dark border border-void-border text-void-white placeholder-void-gray
                                      rounded-xl px-4 py-3 text-sm
                                      focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted
                                      @error('quantity') border-red-500 @enderror"
                               min="1" max="100" required>
                        <p class="text-[10px] text-void-muted mt-1">Maksimal 100 pcs</p>
                        @error('quantity')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-void-white uppercase tracking-wider mb-2">
                            Budget <span class="text-void-muted font-normal">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-void-gray text-sm">Rp</span>
                            <input type="number" name="budget" value="{{ old('budget') }}"
                                   class="w-full bg-void-dark border border-void-border text-void-white placeholder-void-gray
                                          rounded-xl pl-8 pr-4 py-3 text-sm
                                          focus:outline-none focus:border-void-muted focus:ring-1 focus:ring-void-muted
                                          @error('budget') border-red-500 @enderror"
                                   min="0" placeholder="Estimasi budget">
                        </div>
                        <p class="text-[10px] text-void-muted mt-1">Estimasi budget agar tim bisa memberikan quote yang sesuai</p>
                        @error('budget')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Gambar Referensi --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-void-white uppercase tracking-wider mb-2">
                        Gambar Referensi <span class="text-void-muted font-normal">(Opsional)</span>
                    </label>
                    <div class="border-2 border-dashed border-void-border rounded-xl p-6 text-center 
                                hover:border-void-accent transition-colors cursor-pointer"
                         onclick="document.getElementById('reference_image').click()">
                        <svg class="w-10 h-10 text-void-muted mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-void-gray">Klik untuk upload gambar referensi</p>
                        <p class="text-[10px] text-void-muted mt-1">Format: JPG, PNG, WEBP (Maks 5MB)</p>
                        <div id="image-preview" class="mt-3 hidden">
                            <img id="preview-img" class="max-h-40 mx-auto rounded-lg">
                        </div>
                    </div>
                    <input type="file" name="reference_image" id="reference_image" 
                           class="hidden" accept="image/jpeg,image/png,image/webp">
                    @error('reference_image')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Info Box --}}
                <div class="bg-void-dark border border-void-border rounded-xl p-4 mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-void-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs font-bold text-void-white uppercase tracking-wider">Informasi</p>
                    </div>
                    <ul class="text-xs text-void-gray space-y-1 ml-6 list-disc">
                        <li>Tim VOID Supply akan merespon dalam 1x24 jam</li>
                        <li>Quote harga akan diberikan setelah tim melihat detail desain</li>
                        <li>Commission hanya akan diproses setelah pembayaran lunas</li>
                        <li>Estimasi pengerjaan 14-21 hari setelah payment dikonfirmasi</li>
                    </ul>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" 
                            class="flex-1 bg-white text-black font-bold px-6 py-3 rounded-xl
                                   hover:bg-void-light transition-all duration-200 tracking-wide
                                   active:scale-[0.98] text-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Kirim Request
                    </button>
                    <a href="{{ route('commission.index') }}" 
                       class="flex-1 border border-void-border text-void-light font-medium px-6 py-3 rounded-xl
                              hover:border-void-muted hover:bg-void-card transition-all duration-200
                              active:scale-[0.98] text-sm text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Back Link --}}
        <div class="mt-6 text-center">
            <a href="{{ route('commission.index') }}" 
               class="text-sm text-void-gray hover:text-void-accent transition-colors inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar Commission
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('reference_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('preview-img');
                preview.src = event.target.result;
                document.getElementById('image-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
@endsection