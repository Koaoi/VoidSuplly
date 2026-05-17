<nav
    x-data="{ 
        mobileOpen: false, 
        scrolled: false, 
        cartCount: {{ auth()->check() ? (auth()->user()->cart?->items?->sum('quantity') ?? 0) : 0 }},
        searchOpen: false,
        searchQuery: '',
        performSearch() {
            if (this.searchQuery.trim()) {
                window.location.href = '{{ route('products.index') }}?q=' + encodeURIComponent(this.searchQuery);
            }
        }
    }"
    x-on:scroll.window="scrolled = window.scrollY > 20"
    x-on:cart-updated.window="cartCount = $event.detail.count"
    :class="scrolled ? 'bg-void-black/95 backdrop-blur-md border-void-border' : 'bg-transparent border-transparent'"
    class="fixed top-0 left-0 right-0 z-40 border-b transition-all duration-300"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <span class="text-xl font-black tracking-widest text-void-accent group-hover:text-white transition-colors">
                    VOID
                </span>
                <span class="text-xs font-medium tracking-[0.3em] text-void-gray uppercase mt-0.5">
                    Supply
                </span>
            </a>

            {{-- Nav Links — Desktop --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('home') }}"
                   class="text-sm font-medium text-void-gray hover:text-void-accent transition-colors tracking-wide
                          {{ request()->routeIs('home') ? 'text-void-accent' : '' }}">
                    Home
                </a>
                <a href="{{ route('products.index') }}"
                   class="text-sm font-medium text-void-gray hover:text-void-accent transition-colors tracking-wide
                          {{ request()->routeIs('products.index') ? 'text-void-accent' : '' }}">
                    Products
                </a>
                <a href="{{ route('products.index', ['limited' => 1]) }}"
                   class="text-sm font-medium text-void-gray hover:text-void-accent transition-colors tracking-wide
                          {{ request()->get('limited') ? 'text-void-accent' : '' }}">
                    Drops
                </a>
                <a href="{{ route('commission.index') }}"
                   class="text-sm font-medium text-void-gray hover:text-void-accent transition-colors tracking-wide
                          {{ request()->routeIs('commission.*') ? 'text-void-accent' : '' }}">
                    Commission
                </a>
            </div>

            {{-- Right Section --}}
            <div class="flex items-center gap-3">

                {{-- Search Icon & Modal --}}
                <div class="relative">
                    <button 
                        @click="searchOpen = !searchOpen"
                        class="p-2 text-void-gray hover:text-void-accent transition-colors rounded-lg hover:bg-void-card"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    {{-- Search Modal --}}
                    <div 
                        x-show="searchOpen"
                        @click.away="searchOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute right-0 mt-2 w-80 bg-void-card border border-void-border rounded-xl shadow-xl overflow-hidden"
                    >
                        <div class="p-3">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-void-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input 
                                    type="text"
                                    x-model="searchQuery"
                                    @keyup.enter="performSearch()"
                                    placeholder="Cari produk..."
                                    class="w-full input-void pl-10 pr-10 text-sm"
                                >
                                <button 
                                    @click="performSearch()"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 text-xs font-medium bg-white text-black rounded hover:bg-void-light transition-colors"
                                >
                                    Cari
                                </button>
                            </div>
                            <div class="mt-2 text-xs text-void-gray text-center">
                                Tekan Enter untuk mencari
                            </div>
                        </div>
                    </div>
                </div>

                @auth
                    {{-- Cart Icon --}}
                    <a href="{{ route('cart.index') }}" class="relative p-2 text-void-gray hover:text-void-accent transition-colors rounded-lg hover:bg-void-card">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span x-show="cartCount > 0" 
                              x-text="cartCount > 9 ? '9+' : cartCount"
                              class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 bg-white text-black text-[9px] font-bold rounded-full flex items-center justify-center px-1">
                        </span>
                    </a>

                    {{-- Wishlist Icon --}}
                    <a href="{{ route('wishlist.index') }}" class="p-2 text-void-gray hover:text-void-accent transition-colors rounded-lg hover:bg-void-card">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </a>

                    {{-- User Dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button
                            @click="open = !open"
                            @click.outside="open = false"
                            class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-void-card transition-colors group"
                        >
                            <img
                                src="{{ auth()->user()->avatar_url }}"
                                alt="{{ auth()->user()->name }}"
                                class="w-7 h-7 rounded-full object-cover border border-void-border group-hover:border-void-muted transition-colors"
                            >
                            <svg class="w-3.5 h-3.5 text-void-gray transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-full mt-2 w-52 bg-void-card border border-void-border rounded-xl shadow-xl overflow-hidden"
                        >
                            {{-- User Info --}}
                            <div class="px-4 py-3 border-b border-void-border">
                                <p class="text-sm font-semibold text-void-white truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-void-gray truncate mt-0.5">{{ auth()->user()->email }}</p>
                                @if(auth()->user()->isAdmin())
                                    <span class="inline-block mt-1.5 text-[10px] font-bold tracking-widest bg-white text-black px-2 py-0.5 rounded-full uppercase">
                                        Admin
                                    </span>
                                @endif
                            </div>

                            {{-- Menu Items --}}
                            <div class="py-1">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-void-light hover:bg-void-muted hover:text-void-accent transition-colors">
                                        <svg class="w-4 h-4 text-void-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                        Dashboard Admin
                                    </a>
                                @endif
                                
                                {{-- Profile Link (DIUBAH ke route profile.index) --}}
                                <a href="{{ route('profile.index') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-void-light hover:bg-void-muted hover:text-void-accent transition-colors">
                                    <svg class="w-4 h-4 text-void-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profile Saya
                                </a>
                                
                                <a href="{{ route('orders.index') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-void-light hover:bg-void-muted hover:text-void-accent transition-colors">
                                    <svg class="w-4 h-4 text-void-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Riwayat Order
                                </a>
                                
                                <a href="{{ route('commission.index') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-void-light hover:bg-void-muted hover:text-void-accent transition-colors">
                                    <svg class="w-4 h-4 text-void-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Commission Saya
                                </a>
                            </div>

                            {{-- Logout --}}
                            <div class="border-t border-void-border py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4 4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- Guest: Login & Register buttons --}}
                    <a href="{{ route('login') }}"
                       class="text-sm font-medium text-void-gray hover:text-void-accent transition-colors px-3 py-1.5">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                       class="text-sm font-semibold bg-white text-black px-4 py-1.5 rounded-lg hover:bg-void-light transition-colors tracking-wide">
                        Register
                    </a>
                @endauth

                {{-- Mobile Menu Toggle --}}
                <button
                    @click="mobileOpen = !mobileOpen"
                    class="md:hidden p-2 text-void-gray hover:text-void-accent transition-colors rounded-lg hover:bg-void-card"
                >
                    <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="md:hidden bg-void-dark border-t border-void-border"
    >
        <div class="px-4 py-4 space-y-1">
            <a href="{{ route('home') }}" class="block px-3 py-2.5 text-sm font-medium text-void-light hover:text-void-accent hover:bg-void-card rounded-lg transition-colors">Home</a>
            <a href="{{ route('products.index') }}" class="block px-3 py-2.5 text-sm font-medium text-void-light hover:text-void-accent hover:bg-void-card rounded-lg transition-colors">Products</a>
            <a href="{{ route('products.index', ['limited' => 1]) }}" class="block px-3 py-2.5 text-sm font-medium text-void-light hover:text-void-accent hover:bg-void-card rounded-lg transition-colors">Drops</a>
            <a href="{{ route('commission.index') }}" class="block px-3 py-2.5 text-sm font-medium text-void-light hover:text-void-accent hover:bg-void-card rounded-lg transition-colors">Commission</a>
        </div>

        @guest
            <div class="pt-3 border-t border-void-border flex gap-3">
                <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2.5 text-sm font-medium border border-void-border text-void-light rounded-lg hover:bg-void-card transition-colors">Login</a>
                <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2.5 text-sm font-semibold bg-white text-black rounded-lg hover:bg-void-light transition-colors">Register</a>
            </div>
        @endguest
    </div>
</nav>