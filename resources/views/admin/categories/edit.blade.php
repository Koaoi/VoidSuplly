@extends('layouts.admin')
@section('title','Edit Kategori')
@section('page-title','Edit Kategori — ' . $category->name)
@section('content')
<div class="max-w-2xl">
    <div class="bg-void-card border border-void-border rounded-2xl p-6">
        <form method="POST" action="{{ route('admin.categories.update',$category) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Nama Kategori *</label>
                <input type="text" name="name" value="{{ old('name',$category->name) }}" class="input-void @error('name') border-red-500/50 @enderror">
                @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Deskripsi</label>
                <textarea name="description" rows="3" class="input-void resize-none">{{ old('description',$category->description) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Gambar</label>
                @if($category->image)
                    <img src="{{ $category->image_url }}" class="w-20 h-20 rounded-xl object-cover mb-3 border border-void-border">
                @endif
                <input type="file" name="image" accept="image/*" class="input-void file:mr-3 file:text-xs file:bg-void-dark file:border file:border-void-border file:text-void-gray file:px-3 file:py-1.5 file:rounded-lg file:cursor-pointer">
                <p class="text-[10px] text-void-muted mt-1">Kosongkan jika tidak ingin mengubah gambar.</p>
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active',$category->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded bg-void-dark border-void-border text-void-accent focus:ring-0">
                <label for="is_active" class="text-sm text-void-light cursor-pointer">Kategori aktif</label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Update Kategori</button>
                <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection