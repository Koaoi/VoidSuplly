@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">— Checkout</p>
            <h1 class="text-3xl font-black text-void-accent">Selesaikan Pesanan</h1>
        </div>

        {{-- Step indicator --}}
        <div class="flex items-center gap-3 mb-10">
            @foreach(['Keranjang' => true, 'Checkout' => true, 'Pembayaran' => false] as $step => $active)
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full {{ $active ? 'bg-white text-black' : 'bg-void-muted text-void-gray' }}
                                    flex items-center justify-center text-[10px] font-black">
                            {{ $loop->iteration }}
                        </div>
                        <span class="text-xs font-medium {{ $active ? 'text-void-light' : 'text-void-muted' }}">
                            {{ $step }}
                        </span>
                    </div>
                    @if(!$loop->last)
                        <div class="w-8 h-px bg-void-border"></div>
                    @endif
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('checkout.process') }}"
              x-data="checkoutComponent()"
              x-init="init()">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- LEFT: Form Alamat + Kurir --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Alert Errors --}}
                    @if($errors->any())
                        <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-4">
                            <ul class="space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="text-xs text-red-400">• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Alamat Pengiriman --}}
                    <div class="bg-void-card border border-void-border rounded-2xl p-6">
                        <h2 class="text-sm font-bold text-void-white uppercase tracking-wider mb-5">
                            Alamat Pengiriman
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- Nama Penerima --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                                    Nama Penerima *
                                </label>
                                <input type="text" name="recipient_name"
                                       value="{{ old('recipient_name', auth()->user()->name) }}"
                                       class="input-void @error('recipient_name') border-red-500/50 @enderror"
                                       placeholder="Nama lengkap penerima">
                                @error('recipient_name')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- No Telepon --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                                    Nomor Telepon *
                                </label>
                                <input type="text" name="phone"
                                       value="{{ old('phone') }}"
                                       class="input-void @error('phone') border-red-500/50 @enderror"
                                       placeholder="08xxxxxxxxxx">
                                @error('phone')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
{{-- Provinsi --}}
<div>
    <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
        Provinsi *
    </label>
    <div class="relative">
        <select name="province_id" x-model="provinceId"
                @change="onProvinceChange()"
                :disabled="loadingProvinces"
                class="input-void cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed @error('province_id') border-red-500/50 @enderror">
            <option value="">
                <span x-show="loadingProvinces">Memuat provinsi...</span>
                <span x-show="!loadingProvinces">Pilih Provinsi</span>
            </option>
            @foreach($provinces as $prov)
                <option value="{{ $prov['province_id'] }}"
                        {{ old('province_id') == $prov['province_id'] ? 'selected' : '' }}>
                    {{ $prov['province'] }}
                </option>
            @endforeach
        </select>
        <div x-show="loadingProvinces" 
             class="absolute right-3 top-1/2 -translate-y-1/2">
            <svg class="w-4 h-4 text-void-gray animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </div>
    </div>
    @error('province_id')
        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
    @enderror
</div>

                            {{-- Kota --}}
                            <div>
                                <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                                    Kota/Kabupaten *
                                </label>
                                <div class="relative">
                                    <select name="city_id" x-model="cityId"
                                            @change="onCityChange()"
                                            :disabled="cities.length === 0"
                                            class="input-void cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed @error('city_id') border-red-500/50 @enderror">
                                        <option value="">
                                            <span x-show="loadingCities">Memuat...</span>
                                            <span x-show="!loadingCities && cities.length === 0">Pilih Provinsi Dulu</span>
                                            <span x-show="!loadingCities && cities.length > 0">Pilih Kota</span>
                                        </option>
                                        <template x-for="city in cities" :key="city.city_id">
                                            <option :value="city.city_id" x-text="city.city_name"></option>
                                        </template>
                                    </select>
                                    <div x-show="loadingCities"
                                         class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="w-4 h-4 text-void-gray animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                    </div>
                                </div>
                                @error('city_id')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Kode Pos --}}
                            <div>
                                <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                                    Kode Pos *
                                </label>
                                <input type="text" name="postal_code"
                                       value="{{ old('postal_code') }}"
                                       class="input-void @error('postal_code') border-red-500/50 @enderror"
                                       placeholder="12345" maxlength="10">
                                @error('postal_code')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Alamat Detail --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                                    Alamat Lengkap *
                                </label>
                                <textarea name="address_detail" rows="3"
                                          class="input-void resize-none @error('address_detail') border-red-500/50 @enderror"
                                          placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan...">{{ old('address_detail') }}</textarea>
                                @error('address_detail')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Catatan --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-2">
                                    Catatan (opsional)
                                </label>
                                <textarea name="notes" rows="2"
                                          class="input-void resize-none"
                                          placeholder="Instruksi khusus untuk kurir atau penjual...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Pilih Kurir --}}
                    <div class="bg-void-card border border-void-border rounded-2xl p-6">
                        <h2 class="text-sm font-bold text-void-white uppercase tracking-wider mb-5">
                            Pengiriman
                        </h2>

                        {{-- Berat Total --}}
                        <div class="mb-4 text-sm text-void-gray">
                            Total Berat: <span class="font-bold text-void-white">{{ $cart->total_weight }} gram</span>
                            ({{ number_format($cart->total_weight / 1000, 2) }} kg)
                        </div>

                        {{-- Pilih Kurir --}}
                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-void-light uppercase tracking-wider mb-3">
                                Pilih Kurir *
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($couriers as $code => $name)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="courier" value="{{ $code }}"
                                               x-model="courier"
                                               @change="onCourierChange()"
                                               class="sr-only peer">
                                        <div class="flex items-center justify-center px-3 py-2.5 rounded-xl border-2
                                                    border-void-border text-xs font-semibold text-void-gray
                                                    peer-checked:border-void-accent peer-checked:text-void-accent
                                                    peer-checked:bg-void-muted/20 hover:border-void-muted
                                                    hover:text-void-light transition-all text-center leading-tight">
                                            {{ $name }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Loading state --}}
                        <div x-show="loadingOngkir" class="flex items-center gap-3 py-4">
                            <svg class="w-5 h-5 text-void-gray animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span class="text-sm text-void-gray">Menghitung ongkos kirim...</span>
                        </div>

                        {{-- Shipping options --}}
                        <div x-show="shippingOptions.length > 0 && !loadingOngkir" class="space-y-2">
                            <p class="text-xs font-semibold text-void-light uppercase tracking-wider mb-3">
                                Pilih Layanan *
                            </p>
                            <template x-for="option in shippingOptions" :key="option.service">
                                <label class="cursor-pointer block">
                                    <input type="radio" name="service" :value="option.service"
                                           @change="selectService(option)"
                                           class="sr-only peer">
                                    <div class="flex items-center justify-between px-4 py-3 rounded-xl border-2
                                                border-void-border peer-checked:border-void-accent
                                                peer-checked:bg-void-muted/20 hover:border-void-muted
                                                transition-all">
                                        <div>
                                            <p class="text-sm font-bold text-void-white"
                                               x-text="courier.toUpperCase() + ' ' + option.service"></p>
                                            <p class="text-xs text-void-gray" x-text="option.description"></p>
                                            <p class="text-xs text-void-muted mt-0.5"
                                               x-text="'Estimasi: ' + (option.etd ? option.etd + ' hari' : '-')"></p>
                                        </div>
                                        <p class="text-base font-black text-void-accent"
                                           x-text="formatRupiah(option.cost)"></p>
                                    </div>
                                </label>
                            </template>
                        </div>

                        {{-- No options yet --}}
                        <div x-show="shippingOptions.length === 0 && !loadingOngkir && courier && cityId"
                             class="text-sm text-red-400 py-2 bg-red-500/10 rounded-lg p-3">
                            <strong>⚠️ Layanan tidak tersedia</strong><br>
                            Untuk kota ini, kurir <span x-text="courier.toUpperCase()"></span> tidak memiliki layanan.
                            Silakan pilih kurir lain.
                        </div>

                        {{-- Prompt --}}
                        <div x-show="!courier || !cityId"
                             class="text-xs text-void-muted py-2 bg-void-muted/10 rounded-lg p-3">
                            📦 Pilih provinsi, kota, dan kurir untuk melihat opsi pengiriman.
                        </div>

                        {{-- Hidden fields yang dikirim ke server --}}
                        <input type="hidden" name="service_name" :value="selectedService">
                        <input type="hidden" name="shipping_cost" :value="shippingCost">
                        <input type="hidden" name="estimated_days" :value="estimatedDays">
                        <input type="hidden" name="total_weight" value="{{ $cart->total_weight }}">
                    </div>
                </div>

                {{-- RIGHT: Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-void-card border border-void-border rounded-2xl p-6 sticky top-24">
                        <h2 class="text-sm font-bold text-void-white uppercase tracking-wider mb-5">
                            Ringkasan Order
                        </h2>

                        {{-- Items --}}
                        <div class="space-y-3 mb-5 max-h-48 overflow-y-auto pr-1">
                            @foreach($cart->items as $item)
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-void-dark shrink-0">
                                        <img src="{{ $item->product->primary_image_url }}"
                                             alt="{{ $item->product->name }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-void-light line-clamp-1">
                                            {{ $item->product->name }}
                                        </p>
                                        <p class="text-[10px] text-void-gray">
                                            Size: {{ $item->size }} × {{ $item->quantity }}
                                        </p>
                                    </div>
                                    <p class="text-xs font-bold text-void-white shrink-0">
                                        {{ $item->formatted_subtotal }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totals --}}
                        <div class="space-y-2.5 pt-4 border-t border-void-border">
                            <div class="flex justify-between text-sm">
                                <span class="text-void-gray">Subtotal</span>
                                <span class="text-void-light">{{ $cart->formatted_total }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-void-gray">Berat Total</span>
                                <span class="text-void-light">{{ $cart->total_weight }} gram</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-void-gray">Ongkos Kirim</span>
                                <span class="text-void-light font-semibold"
                                      x-text="shippingCost > 0 ? formatRupiah(shippingCost) : 'Belum dipilih'"></span>
                            </div>
                        </div>

                        <div class="flex justify-between mt-4 pt-4 border-t border-void-border">
                            <span class="text-sm font-bold text-void-white">Total</span>
                            <span class="text-xl font-black text-void-accent" x-text="formatRupiah(total)"></span>
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                :disabled="!selectedService"
                                class="btn-primary w-full mt-6 py-3.5 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!submitting">Buat Pesanan</span>
                            <span x-show="submitting" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Memproses...
                            </span>
                        </button>

                        <p x-show="!selectedService && courier && cityId"
                           class="text-[10px] text-red-400 text-center mt-2">
                           ⚠️ Pilih layanan pengiriman terlebih dahulu
                        </p>

                        <p x-show="!courier || !cityId"
                           class="text-[10px] text-void-muted text-center mt-2">
                           📍 Lengkapi alamat dan pilih kurir
                        </p>

                        {{-- Security --}}
                        <div class="mt-5 pt-4 border-t border-void-border flex items-center justify-center gap-2 text-xs text-void-gray">
                            <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Transaksi aman & terlindungi
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function checkoutComponent() {
    return {
        // Data
        provinceId: {{ old('province_id', 'null') }},
        cityId: {{ old('city_id', 'null') }},
        courier: '{{ old('courier', '') }}',
        cities: [],
        shippingOptions: [],
        selectedService: '{{ old('service', '') }}',
        shippingCost: {{ old('shipping_cost', 0) }},
        estimatedDays: {{ old('estimated_days', 'null') }},
        loadingCities: false,
        loadingOngkir: false,
        submitting: false,
        
        // Computed
        get subtotal() {
            return {{ $cart->total }};
        },
        
        get total() {
            return this.subtotal + this.shippingCost;
        },
        
        // Methods
        init() {
            if (this.provinceId) {
                this.loadCities();
            }
            if (this.courier && this.cityId) {
                this.fetchOngkir();
            }
        },
        
        formatRupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        },
        
        async loadCities() {
            if (!this.provinceId) return;
            this.loadingCities = true;
            try {
                const response = await fetch('{{ route('checkout.cities') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ province_id: this.provinceId })
                });
                const data = await response.json();
                this.cities = data.cities || [];
            } catch (error) {
                console.error('Error loading cities:', error);
            } finally {
                this.loadingCities = false;
            }
        },
        
        async onProvinceChange() {
            this.cityId = '';
            this.cities = [];
            this.shippingOptions = [];
            this.selectedService = null;
            this.shippingCost = 0;
            await this.loadCities();
        },
        
        async onCityChange() {
            if (this.courier && this.cityId) {
                await this.fetchOngkir();
            }
        },
        
        async onCourierChange() {
            if (this.cityId && this.courier) {
                await this.fetchOngkir();
            }
        },
        
        async fetchOngkir() {
            this.shippingOptions = [];
            this.selectedService = null;
            this.shippingCost = 0;
            this.loadingOngkir = true;
            
            try {
                const response = await fetch('{{ route('checkout.ongkir') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        city_id: this.cityId,
                        courier: this.courier
                    })
                });
                const data = await response.json();
                this.shippingOptions = data.costs || [];
            } catch (error) {
                console.error('Error fetching shipping cost:', error);
                this.shippingOptions = [];
            } finally {
                this.loadingOngkir = false;
            }
        },
        
        selectService(option) {
            this.selectedService = option.service;
            this.shippingCost = option.cost;
            this.estimatedDays = option.etd ? parseInt(option.etd) : null;
        }
    }
}
</script>
@endpush
@endsection