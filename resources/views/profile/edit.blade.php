@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">— Account</p>
            <h1 class="text-3xl font-black text-void-accent">Edit Profile</h1>
            <p class="text-void-gray text-sm mt-2">Perbarui informasi akun Anda</p>
        </div>

        <div class="bg-void-card border border-void-border rounded-2xl p-6">
            
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                {{-- Avatar --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Avatar</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full object-cover border border-void-border">
                        <div class="flex-1">
                            <input type="file" name="avatar" accept="image/*" 
                                   class="input-void file:mr-3 file:text-xs file:bg-void-dark file:border file:border-void-border file:text-void-gray file:px-3 file:py-1.5 file:rounded-lg file:cursor-pointer">
                            <p class="text-[10px] text-void-muted mt-1">Format: JPG, PNG, WEBP (Max 2MB)</p>
                        </div>
                    </div>
                    @error('avatar')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Name --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                           class="input-void w-full @error('name') border-red-500/50 @enderror">
                    @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" 
                           class="input-void w-full @error('email') border-red-500/50 @enderror">
                    @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Phone --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Nomor Telepon</label>
                    <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" 
                           class="input-void w-full" placeholder="08123456789 atau 628123456789">
                    <p class="text-[10px] text-void-muted mt-1">
                        Masukkan nomor aktif dengan kode area (contoh: 08123456789 atau 628123456789)
                    </p>
                    @error('phone')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('profile.index') }}" class="btn-secondary">Batal</a>
                </div>
            </form>

            {{-- Change Password Section --}}
            <div class="mt-8 pt-6 border-t border-void-border">
                <h3 class="text-sm font-bold text-void-white mb-4">Ganti Password</h3>
                <form method="POST" action="{{ route('profile.change-password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Password Saat Ini</label>
                        <input type="password" name="current_password" class="input-void w-full">
                        @error('current_password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Password Baru</label>
                        <input type="password" name="password" class="input-void w-full">
                        <p class="text-[10px] text-void-muted mt-1">Minimal 8 karakter</p>
                        @error('password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="input-void w-full">
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="btn-secondary text-sm">Update Password</button>
                        <a href="{{ route('profile.index') }}" class="text-xs text-void-gray hover:text-void-accent transition-colors py-2">
                            Batal
                        </a>
                    </div>
                </form>
            </div>

            {{-- Delete Account Section --}}
            <div class="mt-8 pt-6 border-t border-red-500/20">
                <h3 class="text-sm font-bold text-red-400 mb-3">Hapus Akun</h3>
                <p class="text-xs text-void-gray mb-3">Setelah akun dihapus, semua data akan hilang permanen dan tidak dapat dipulihkan.</p>
                
                <button type="button" onclick="openDeleteModal()" class="text-sm text-red-400 hover:text-red-300 transition-colors">
                    Hapus Akun Saya
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Account Modal --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="inline-block align-bottom bg-void-card rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="px-6 py-5">
                    <h3 class="text-lg font-bold text-void-white mb-3">Hapus Akun</h3>
                    <p class="text-sm text-void-gray mb-4">Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">Konfirmasi Password</label>
                        <input type="password" name="password" required class="input-void w-full">
                    </div>
                </div>
                <div class="px-6 py-4 bg-void-dark/30 flex justify-end gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="btn-secondary text-sm px-4 py-2">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold bg-red-500 text-white hover:bg-red-600 transition-colors">
                        Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endsection