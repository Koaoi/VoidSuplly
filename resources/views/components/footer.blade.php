<footer class="bg-void-dark border-t border-void-border mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

            {{-- Brand --}}
            <div class="md:col-span-2">
                <div class="flex items-baseline gap-2 mb-4">
                    <span class="text-2xl font-black tracking-widest text-void-accent">VOID</span>
                    <span class="text-xs font-medium tracking-[0.3em] text-void-gray uppercase">Supply</span>
                </div>
                <p class="text-sm text-void-gray leading-relaxed max-w-xs">
                    Limited fashion drop & commission streetwear store. Eksklusif, premium, dan selalu terbatas.
                </p>
                {{-- Social Links --}}
                <div class="flex gap-4 mt-5">
                    <a href="#" class="text-void-gray hover:text-void-accent transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="#" class="text-void-gray hover:text-void-accent transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="#" class="text-void-gray hover:text-void-accent transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.56V6.81a4.85 4.85 0 01-1.07-.12z"/></svg>
                    </a>
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <h4 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase mb-4">Shop</h4>
                <ul class="space-y-2.5">
                    @foreach(['All Products' => '#', 'New Arrivals' => '#', 'Limited Drops' => '#', 'Preorder' => '#', 'Commission' => '#'] as $label => $href)
                        <li><a href="{{ $href }}" class="text-sm text-void-gray hover:text-void-accent transition-colors">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Info --}}
            <div>
                <h4 class="text-xs font-bold tracking-[0.2em] text-void-white uppercase mb-4">Info</h4>
                <ul class="space-y-2.5">
                    @foreach(['Tentang Kami' => '#', 'Size Guide' => '#', 'Cara Order' => '#', 'Kebijakan Return' => '#', 'Hubungi Kami' => '#'] as $label => $href)
                        <li><a href="{{ $href }}" class="text-sm text-void-gray hover:text-void-accent transition-colors">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="mt-10 pt-6 border-t border-void-border flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-void-gray">
                &copy; {{ date('Y') }} VOID Supply. All rights reserved.
            </p>
            <p class="text-xs text-void-gray">
                Made with Laravel 10 &amp; Tailwind CSS
            </p>
        </div>
    </div>
</footer>