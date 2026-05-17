@extends('layouts.admin')
@section('title','Tambah Kategori')
@section('page-title','Tambah Kategori')
@section('content')
<div class="max-w-2xl">
    <div class="bg-void-card border border-void-border rounded-2xl p-6">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Nama Kategori *</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input-void @error('name') border-red-500/50 @enderror" placeholder="Hoodie, T-Shirt, dll">
                @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Deskripsi</label>
                <textarea name="description" rows="3" class="input-void resize-none" placeholder="Deskripsi singkat kategori...">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Gambar Kategori</label>
                <input type="file" name="image" accept="image/*" class="input-void file:mr-3 file:text-xs file:bg-void-dark file:border file:border-void-border file:text-void-gray file:px-3 file:py-1.5 file:rounded-lg file:cursor-pointer">
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active','1') ? 'checked' : '' }} class="w-4 h-4 rounded bg-void-dark border-void-border text-void-accent focus:ring-0">
                <label for="is_active" class="text-sm text-void-light cursor-pointer">Kategori aktif</label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan Kategori</button>
                <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
