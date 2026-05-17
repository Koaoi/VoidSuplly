@extends('layouts.app')

@section('title', 'Buat Commission Request')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('commission.index') }}"
               class="inline-flex items-center gap-2 text-xs text-void-gray hover:text-void-accent
                      transition-colors mb-5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">— Custom Order</p>
            <h1 class="text-3xl font-black text-void-accent">Commission Request</h1>
            <p class="text-void-gray text-sm mt-2 leading-relaxed">
                Ceritakan ide desainmu secara detail. Tim VOID Supply akan review dan
                memberikan estimasi harga dalam 1–2 hari kerja.
            </p>
        </div>

        {{-- How it works --}}
        <div class="grid grid-cols-3 gap-3 mb-8">
            @foreach([
                ['01', 'Submit Request', 'Isi form dengan detail lengkap'],
                ['02', 'Review & Quote', 'Kami review dan beri estimasi'],
                ['03', 'Produksi',       'Dikerjakan & dikirim ke kamu'],
            ] as [$num, $title, $desc])
                <div class="bg-void-card border border-void-border rounded-xl p-4 text-center">
                    <p class="text-2xl font-black text-void-muted mb-2">{{ $num }}</p>
                    <p class="text-xs font-bold text-void-white">{{ $title }}</p>
                    <p class="text-[10px] text-void-gray mt-1 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('commission.store') }}"
              enctype="multipart/form-data"
              x-data="{
                  charCount: 0,
                  previewUrl: null,
                  fileName: '',
                  dragover: false,

                  handleFile(file) {
                      if (!file || !file.type.startsWith('image/')) return;
                      this.fileName = file.name;
                      const reader = new FileReader();
                      reader.onload = e => this.previewUrl = e.target.result;
                      reader.readAsDataURL(file);
                  }
              }"
              class="space-y-6"
        >
            @csrf

            {{-- Card: Informasi Dasar --}}
            <div class="bg-void-card border border-void-border rounded-2xl p-6 space-y-5">
                <h2 class="text-sm font-bold text-void-white uppercase tracking-wider">
                    Informasi Dasar
                </h2>

                {{-- Judul --}}
                <div>
                    <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                        Judul Commission *
                    </label>
                    <input type="text" name="title"
                           value="{{ old('title') }}"
                           class="input-void @error('title') border-red-500/50 @enderror"
                           placeholder="Contoh: Custom Hoodie Streetwear Logo Minimalis">
                    @error('title')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipe Produk --}}
                <div>
                    <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-3">
                        Tipe Produk *
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach($productTypes as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="product_type" value="{{ $value }}"
                                       {{ old('product_type') === $value ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="flex items-center justify-center px-3 py-2.5 rounded-xl
                                            border-2 border-void-border text-xs font-semibold text-void-gray
                                            peer-checked:border-void-accent peer-checked:text-void-accent
                                            peer-checked:bg-void-muted/20 hover:border-void-muted
                                            hover:text-void-light transition-all text-center">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('product_type')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jumlah + Budget --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                            Jumlah (pcs) *
                        </label>
                        <input type="number" name="quantity" min="1" max="100"
                               value="{{ old('quantity', 1) }}"
                               class="input-void @error('quantity') border-red-500/50 @enderror">
                        @error('quantity')
                            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                            Budget (Rp) <span class="text-void-muted normal-case font-normal">opsional</span>
                        </label>
                        <input type="number" name="budget" min="0"
                               value="{{ old('budget') }}"
                               class="input-void"
                               placeholder="500000">
                        <p class="text-[10px] text-void-muted mt-1">Kosongkan jika belum ada estimasi</p>
                    </div>
                </div>
            </div>

            {{-- Card: Deskripsi --}}
            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <h2 class="text-sm font-bold text-void-white uppercase tracking-wider mb-5">
                    Deskripsi Detail
                </h2>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-void-light uppercase tracking-wider">
                            Deskripsi Desain *
                        </label>
                        <span class="text-[10px] text-void-gray" x-text="charCount + ' / 2000 karakter'"></span>
                    </div>
                    <textarea name="description" rows="8"
                              @input="charCount = $el.value.length"
                              class="input-void resize-none @error('description') border-red-500/50 @enderror"
                              placeholder="Ceritakan detail desainmu:
- Konsep dan tema desain
- Warna yang diinginkan (background, text, elemen)
- Font atau tipografi yang disukai
- Posisi print/bordir (dada, punggung, lengan)
- Referensi brand atau style yang menginspirasi
- Ukuran yang dibutuhkan
- Deadline jika ada">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                    <p class="text-[10px] text-void-muted mt-1">Minimal 30 karakter. Semakin detail semakin baik.</p>
                </div>
            </div>

            {{-- Card: Referensi Gambar --}}
            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <h2 class="text-sm font-bold text-void-white uppercase tracking-wider mb-2">
                    Gambar Referensi
                    <span class="text-void-muted text-xs font-normal normal-case ml-1">(opsional)</span>
                </h2>
                <p class="text-xs text-void-gray mb-5">
                    Upload mockup, sketsa, atau gambar referensi yang membantu menjelaskan desainmu.
                    Format: JPG, PNG, WEBP — maks. 5MB.
                </p>

                {{-- Drop zone --}}
                <label
                    class="relative block cursor-pointer"
                    @dragover.prevent="dragover = true"
                    @dragleave="dragover = false"
                    @drop.prevent="dragover = false; handleFile($event.dataTransfer.files[0])"
                >
                    <input type="file" name="reference_image"
                           accept="image/jpg,image/jpeg,image/png,image/webp"
                           class="sr-only"
                           @change="handleFile($event.target.files[0])">

                    {{-- Preview state --}}
                    <template x-if="previewUrl">
                        <div class="relative rounded-2xl overflow-hidden border-2 border-void-border
                                    group hover:border-void-muted transition-colors">
                            <img :src="previewUrl"
                                 class="w-full max-h-64 object-contain bg-void-dark">
                            <div class="absolute inset-0 bg-void-black/50 opacity-0 group-hover:opacity-100
                                        transition-opacity flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-8 h-8 text-void-white mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-xs text-void-white font-medium">Klik untuk ganti gambar</p>
                                </div>
                            </div>
                            <div class="absolute bottom-3 left-3 bg-void-black/70 rounded-lg px-2 py-1">
                                <p class="text-[10px] text-void-white" x-text="fileName"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="!previewUrl">
                        <div :class="dragover ? 'border-void-accent bg-void-muted/10' : 'border-void-border'"
                             class="border-2 border-dashed rounded-2xl p-12 text-center
                                    hover:border-void-muted hover:bg-void-dark/30 transition-all">
                            <svg class="w-12 h-12 text-void-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm font-medium text-void-light mb-1">
                                Drag & drop atau klik untuk upload
                            </p>
                            <p class="text-xs text-void-gray">
                                JPG, PNG, WEBP — maks. 5MB
                            </p>
                        </div>
                    </template>
                </label>

                @error('reference_image')
                    <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Terms & Submit --}}
            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <div class="flex items-start gap-3 mb-5">
                    <input type="checkbox" id="terms" required
                           class="w-4 h-4 mt-0.5 rounded border-void-border bg-void-dark
                                  text-void-accent focus:ring-0 shrink-0 cursor-pointer">
                    <label for="terms" class="text-sm text-void-gray leading-relaxed cursor-pointer">
                        Saya mengerti bahwa commission request ini akan direview oleh tim VOID Supply.
                        Harga final akan dikomunikasikan via platform ini sebelum produksi dimulai.
                        Pembayaran dilakukan setelah quote disetujui.
                    </label>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="btn-primary flex-1 py-3.5 text-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Commission Request
                    </button>
                    <a href="{{ route('commission.index') }}" class="btn-secondary flex-1 py-3.5 text-sm text-center">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection