@extends('layouts.app')
@section('title','403 — Akses Ditolak')
@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <p class="text-8xl font-black text-void-border mb-4 select-none">403</p>
        <h1 class="text-2xl font-bold text-void-white mb-3">Akses Ditolak</h1>
        <p class="text-void-gray text-sm mb-8 leading-relaxed">
            Kamu tidak memiliki izin untuk mengakses halaman ini.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary px-6 py-2.5 text-sm">Kembali ke Toko</a>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary px-6 py-2.5 text-sm">Logout</button>
                </form>
            @endauth
        </div>
    </div>
</div>
@endsection
BLADE

cat > /mnt/user-data/outputs/void-supply/resources/views/errors/404.blade.php << 'BLADE'
@extends('layouts.app')
@section('title','404 — Halaman Tidak Ditemukan')
@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <p class="text-8xl font-black text-void-border mb-4 select-none">404</p>
        <h1 class="text-2xl font-bold text-void-white mb-3">Halaman Tidak Ditemukan</h1>
        <p class="text-void-gray text-sm mb-8 leading-relaxed">
            Halaman yang kamu cari tidak ada atau sudah dipindahkan.
        </p>
        <a href="{{ route('home') }}" class="btn-primary px-6 py-2.5 text-sm">Kembali ke Home</a>
    </div>
</div>
@endsection
BLADE

cat > /mnt/user-data/outputs/void-supply/resources/views/errors/500.blade.php << 'BLADE'
@extends('layouts.app')
@section('title','500 — Server Error')
@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <p class="text-8xl font-black text-void-border mb-4 select-none">500</p>
        <h1 class="text-2xl font-bold text-void-white mb-3">Server Error</h1>
        <p class="text-void-gray text-sm mb-8 leading-relaxed">
            Terjadi kesalahan pada server. Silakan coba lagi beberapa saat.
        </p>
        <a href="{{ route('home') }}" class="btn-primary px-6 py-2.5 text-sm">Kembali ke Home</a>
    </div>
</div>
@endsection