<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>@yield('title', 'Dashboard') — VOID Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-void-black text-void-white font-sans antialiased">
<div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-void-dark border-r border-void-border
               flex flex-col shrink-0 transition-transform duration-300"
    >
        {{-- Logo --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-void-border shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-baseline gap-2">
                <span class="text-lg font-black tracking-widest text-void-accent">VOID</span>
                <span class="text-[9px] font-bold tracking-[0.25em] text-void-gray uppercase">Admin</span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-void-gray hover:text-void-accent">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            @php
                $currentRoute = request()->route()->getName();
                
                $navItems = [
                    'dashboard' => [
                        'route' => 'admin.dashboard',
                        'label' => 'Dashboard',
                        'icon' => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z',
                    ],
                    'categories' => [
                        'route' => 'admin.categories.index',
                        'label' => 'Kategori',
                        'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                    ],
                    'products' => [
                        'route' => 'admin.products.index',
                        'label' => 'Produk',
                        'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    ],
                    'orders' => [
                        'route' => 'admin.orders.index',
                        'label' => 'Pesanan',
                        'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    ],
                    'users' => [
                        'route' => 'admin.users.index',
                        'label' => 'Users',
                        'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    ],
                    'commissions' => [
                        'route' => 'admin.commissions.index',
                        'label' => 'Commission',
                        'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                    ],
                    'reviews' => [
                        'route' => 'admin.reviews.index',
                        'label' => 'Reviews',
                        'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                    ],
                ];
            @endphp

            @foreach($navItems as $key => $item)
                @php
                    $isActive = request()->routeIs($item['route']) || 
                                (str_contains($currentRoute, $key) && $key !== 'dashboard');
                    $pendingCount = 0;
                    
                    if ($key === 'orders') {
                        $pendingCount = \App\Models\Order::where('status', 'pending')->count();
                    } elseif ($key === 'commissions') {
                        $pendingCount = \App\Models\Commission::where('status', 'pending')->count();
                    }
                @endphp
                
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150
                          {{ $isActive 
                              ? 'bg-void-card text-void-accent border border-void-border font-semibold' 
                              : 'text-void-gray hover:text-void-light hover:bg-void-card/60' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                    
                    @if($pendingCount > 0)
                        <span class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded-full
                                     {{ $key === 'orders' 
                                        ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30' 
                                        : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Divider --}}
        <div class="mx-4 my-2 h-px bg-void-border"></div>

        {{-- Additional Menu (Settings, etc) --}}
        <nav class="px-3 pb-4">
            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-void-gray
                      hover:text-void-light hover:bg-void-card/60 transition-all duration-150">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Lihat Toko
            </a>
        </nav>

        {{-- User info --}}
        <div class="p-4 border-t border-void-border shrink-0">
            <div class="flex items-center gap-3 mb-3">
                <img src="{{ auth()->user()->avatar_url }}" alt=""
                     class="w-8 h-8 rounded-full object-cover border border-void-border shrink-0">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-void-light truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[9px] text-void-gray truncate">Administrator</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('home') }}" target="_blank"
                   class="flex-1 text-center text-[10px] text-void-gray hover:text-void-accent
                          transition-colors py-1.5 border border-void-border rounded-lg hover:border-void-muted">
                    Toko
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full text-[10px] text-red-400 hover:text-red-300 transition-colors
                                   py-1.5 border border-red-500/20 rounded-lg hover:bg-red-500/10">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Backdrop mobile --}}
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    {{-- ── Main Content ─────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 bg-void-dark/95 backdrop-blur-md border-b border-void-border
                        px-6 py-4 flex items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true"
                        class="lg:hidden p-2 text-void-gray hover:text-void-accent
                               rounded-lg hover:bg-void-card transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-base font-bold text-void-white">@yield('page-title', 'Dashboard')</h1>
                    @hasSection('page-subtitle')
                        <p class="text-xs text-void-gray mt-0.5">@yield('page-subtitle')</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('error') || session('warning'))
            <div class="px-6 pt-4"
                 x-data="{show: true}" x-show="show"
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                @if(session('success'))
                    <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30
                                text-green-400 px-4 py-3 rounded-xl text-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ session('success') }}
                        <button @click="show = false" class="ml-auto text-green-400/60 hover:text-green-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30
                                text-red-400 px-4 py-3 rounded-xl text-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ session('error') }}
                        <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="flex items-center gap-3 bg-yellow-500/10 border border-yellow-500/30
                                text-yellow-400 px-4 py-3 rounded-xl text-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        {{ session('warning') }}
                        <button @click="show = false" class="ml-auto text-yellow-400/60 hover:text-yellow-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>