<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'VOID Supply') — Streetwear Limited Drop</title>
    <meta name="description" content="@yield('meta_description', 'VOID Supply — Limited fashion drop & commission streetwear store.')">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('image/favicon.png') }}">

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Vite — Tailwind CSS & Alpine.js --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])


        <script>
// Global notification function
window.showNotification = function(message, type = 'success') {
    // Cari atau buat container notifikasi
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container fixed top-20 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }
    
    // Buat elemen notifikasi
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-yellow-500');
    notification.className = `px-4 py-3 rounded-lg shadow-lg text-sm transition-all transform translate-x-full text-white ${bgColor}`;
    notification.textContent = message;
    
    container.appendChild(notification);
    
    // Animasi masuk
    setTimeout(() => notification.classList.remove('translate-x-full'), 10);
    
    // Animasi keluar dan hapus setelah 3 detik
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
};

// Global add to cart function
window.addToCart = async function(productId, size = null, quantity = 1) {
    try {
        const response = await fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                size: size || 'FREE SIZE',
                quantity: quantity
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showNotification(data.message, 'success');
            
            // Update cart badge
            window.dispatchEvent(new CustomEvent('cart-updated', { 
                detail: { count: data.count } 
            }));
            
            return { success: true, count: data.count };
        } else {
            window.showNotification(data.message || 'Gagal menambahkan ke keranjang', 'error');
            return { success: false, message: data.message };
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        window.showNotification('Terjadi kesalahan, silakan coba lagi', 'error');
        return { success: false, message: error.message };
    }
};
</script>

<style>
.notification-container {
    pointer-events: none;
}
.notification-container > div {
    pointer-events: auto;
    transition: transform 0.3s ease;
    min-width: 280px;
    max-width: 350px;
}
.notification-container .translate-x-full {
    transform: translateX(100%);
}
</style>
    {{-- Stack untuk CSS tambahan per halaman --}}
    @stack('styles')
</head>
<body class="bg-void-black text-void-white font-sans antialiased min-h-screen flex flex-col">

    {{-- ─── Navbar ──────────────────────────────────────────────────────── --}}
    @include('components.navbar')

    {{-- ─── Flash Messages ─────────────────────────────────────────────── --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed top-20 right-4 z-50 max-w-sm w-full"
        >
            @if(session('success'))
                <div class="flex items-start gap-3 bg-void-card border border-green-500/30 text-green-400 px-4 py-3 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm">{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto text-green-400/60 hover:text-green-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-start gap-3 bg-void-card border border-red-500/30 text-red-400 px-4 py-3 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm">{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            @if(session('warning'))
                <div class="flex items-start gap-3 bg-void-card border border-yellow-500/30 text-yellow-400 px-4 py-3 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <p class="text-sm">{{ session('warning') }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- ─── Main Content ────────────────────────────────────────────────── --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ─── Footer ──────────────────────────────────────────────────────── --}}
    @include('components.footer')

    {{-- ─── Global Scripts ───────────────────────────────────────────────── --}}
    <script>
        // Global notification function
        window.showNotification = function(message, type = 'success') {
            let container = document.querySelector('.notification-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'notification-container fixed top-20 right-4 z-50 space-y-2';
                document.body.appendChild(container);
            }
            
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-yellow-500');
            notification.className = `px-4 py-3 rounded-lg shadow-lg text-sm transition-all transform translate-x-full text-white ${bgColor}`;
            notification.textContent = message;
            
            container.appendChild(notification);
            
            // Animate in
            setTimeout(() => notification.classList.remove('translate-x-full'), 10);
            
            // Animate out and remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        };

        // Global add to cart function
        window.addToCart = async function(productId, size = null, quantity = 1) {
            try {
                const response = await fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        size: size || 'FREE SIZE',
                        quantity: quantity
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.showNotification(data.message, 'success');
                    
                    // Update cart badge
                    window.dispatchEvent(new CustomEvent('cart-updated', { 
                        detail: { count: data.count } 
                    }));
                    
                    return { success: true, count: data.count };
                } else {
                    window.showNotification(data.message || 'Gagal menambahkan ke keranjang', 'error');
                    return { success: false, message: data.message };
                }
            } catch (error) {
                console.error('Add to cart error:', error);
                window.showNotification('Terjadi kesalahan, silakan coba lagi', 'error');
                return { success: false, message: error.message };
            }
        };
    </script>

    {{-- Style untuk notifikasi --}}
    <style>
        .notification-container {
            pointer-events: none;
        }
        .notification-container > div {
            pointer-events: auto;
            transition: transform 0.3s ease;
            min-width: 280px;
            max-width: 350px;
        }
        .notification-container .translate-x-full {
            transform: translateX(100%);
        }
    </style>

    {{-- Stack untuk script tambahan per halaman --}}
    @stack('scripts')
</body>
</html>