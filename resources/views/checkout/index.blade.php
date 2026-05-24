@extends('layouts.app')

@section('title', 'Checkout — VOID Supply')

@section('content')
<div class="pt-24 pb-16">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="mb-8">
        <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">— Checkout</p>
        <h1 class="text-3xl font-black text-void-accent">Selesaikan Pesanan</h1>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl p-4">
            <p class="text-sm font-bold text-red-400 mb-2">Terdapat kesalahan:</p>
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li class="text-xs text-red-400">• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="checkout-form"
        method="POST"
        action="{{ route('checkout.process') }}"
        x-data="checkoutApp()"
        x-init="init()"
        @submit.prevent="handleSubmit()"
    >
        @csrf

        {{-- Toast Notification --}}
        <div x-show="toast.show" x-cloak x-transition
             class="fixed top-20 right-4 z-50 max-w-sm w-full bg-void-card border
                    rounded-xl shadow-xl px-4 py-3 flex items-start gap-3"
             :class="toast.type === 'error'
                 ? 'border-red-500/30 text-red-400'
                 : 'border-green-500/30 text-green-400'">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm flex-1" x-text="toast.message"></p>
            <button @click="toast.show = false" class="opacity-60 hover:opacity-100">✕</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- KIRI — Alamat & Pengiriman --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Alamat Pengiriman --}}
                <div class="bg-void-card border border-void-border rounded-2xl p-6">
                    <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-5">
                        Alamat Pengiriman
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Nama Penerima --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                                Nama Penerima *
                            </label>
                            <input type="text" name="recipient_name"
                                   value="{{ old('recipient_name', auth()->user()->name) }}"
                                   class="input-void @error('recipient_name') border-red-500/50 @enderror"
                                   placeholder="Nama lengkap penerima" required>
                            @error('recipient_name')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nomor Telepon --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                                Nomor Telepon *
                            </label>
                            <input type="text" name="phone"
                                   value="{{ old('phone') }}"
                                   class="input-void @error('phone') border-red-500/50 @enderror"
                                   placeholder="08xxxxxxxxxx" required>
                            @error('phone')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Provinsi --}}
                        <div>
                            <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                                Provinsi *
                            </label>
                            <div class="relative">
                                <select id="select-province"
                                        name="province_id"
                                        @change="onProvinceChange($event)"
                                        :disabled="state.loadingProvinces"
                                        class="input-void cursor-pointer disabled:opacity-60"
                                        required>
                                    <option value="">-- Pilih Provinsi --</option>
                                </select>
                                <div x-show="state.loadingProvinces" class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg class="w-4 h-4 animate-spin text-void-gray" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </div>
                            </div>
                            <input type="hidden" name="province_name" :value="form.province_name">
                            @error('province_id')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kota / Kabupaten --}}
                        <div>
                            <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                                Kota / Kabupaten *
                            </label>
                            <input type="text"
                                   name="city_name"
                                   x-model="form.city_name"
                                   class="input-void @error('city_name') border-red-500/50 @enderror"
                                   placeholder="Contoh: Bekasi"
                                   required>
                            <p class="text-[10px] text-void-muted mt-1">Akan terisi otomatis setelah pilih kecamatan</p>
                            @error('city_name')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kecamatan dengan Autocomplete --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                                Kecamatan / Kelurahan *
                                <span class="text-void-muted font-normal normal-case ml-1">
                                    (ketik untuk mencari)
                                </span>
                            </label>

                            <div class="relative">
                                <input type="text"
                                       x-model="subdistrict.query"
                                       @input.debounce.500ms="searchSubdistrict()"
                                       @focus="subdistrict.showDropdown = subdistrict.results.length > 0"
                                       @click.outside="subdistrict.showDropdown = false"
                                       @keydown.escape="subdistrict.showDropdown = false"
                                       @keydown.arrow-down.prevent="moveSelection(1)"
                                       @keydown.arrow-up.prevent="moveSelection(-1)"
                                       @keydown.enter.prevent="selectHighlighted()"
                                       class="input-void pr-10"
                                       placeholder="Ketik nama kelurahan/kecamatan..."
                                       required>

                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <svg x-show="subdistrict.isLoading" class="w-4 h-4 animate-spin text-void-gray" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <svg x-show="!subdistrict.isLoading && subdistrict.selectedId" class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>

                                {{-- Dropdown hasil --}}
                                <div x-show="subdistrict.showDropdown && subdistrict.results.length > 0"
                                     x-cloak
                                     class="absolute top-full left-0 right-0 z-50 mt-1
                                            bg-void-card border border-void-border rounded-xl
                                            shadow-xl overflow-hidden max-h-64 overflow-y-auto">
                                    <template x-for="(item, idx) in subdistrict.results" :key="item.id">
                                        <button type="button"
                                                @click="selectSubdistrict(item)"
                                                @mouseenter="subdistrict.highlightIndex = idx"
                                                :class="subdistrict.highlightIndex === idx
                                                    ? 'bg-void-muted/40 text-void-accent'
                                                    : 'text-void-light hover:bg-void-muted/20'"
                                                class="w-full text-left px-4 py-3 text-xs
                                                       border-b border-void-border/50 last:border-0
                                                       transition-colors">
                                            <p class="font-semibold" x-text="item.label"></p>
                                            <p class="text-void-gray mt-0.5"
                                               x-text="'Kode Pos: ' + (item.postal_code || '-')"></p>
                                        </button>
                                    </template>
                                </div>

                                {{-- No results --}}
                                <div x-show="subdistrict.showDropdown && subdistrict.results.length === 0 && !subdistrict.isLoading && subdistrict.query.length >= 3"
                                     x-cloak
                                     class="absolute top-full left-0 right-0 z-50 mt-1
                                            bg-void-card border border-void-border rounded-xl
                                            shadow-xl px-4 py-3 text-xs text-void-gray">
                                    Tidak ada hasil untuk "<span x-text="subdistrict.query"></span>"
                                </div>
                            </div>

                            {{-- Selected info --}}
                            <div x-show="subdistrict.selectedId" x-cloak class="mt-2 flex items-center gap-2 text-xs text-green-400">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Terpilih: <span x-text="subdistrict.query" class="font-semibold text-void-light"></span>
                                <button type="button" @click="clearSubdistrict()" class="ml-1 text-void-gray hover:text-red-400">
                                    (Ganti)
                                </button>
                            </div>

                            <input type="hidden" name="subdistrict_id" :value="subdistrict.selectedId">
                            <input type="hidden" name="subdistrict_name" :value="subdistrict.selectedName">
                            @error('subdistrict_id')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kode Pos --}}
                        <div>
                            <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                                Kode Pos *
                            </label>
                            <input type="text"
                                   name="postal_code"
                                   id="input-postal"
                                   x-model="subdistrict.selectedPostal"
                                   class="input-void @error('postal_code') border-red-500/50 @enderror"
                                   placeholder="12345" required>
                            @error('postal_code')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Alamat Lengkap --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                                Alamat Lengkap *
                            </label>
                            <textarea name="address_detail" rows="3"
                                      class="input-void resize-none @error('address_detail') border-red-500/50 @enderror"
                                      placeholder="Nama jalan, nomor rumah, RT/RW, patokan..."
                                      required>{{ old('address_detail') }}</textarea>
                            @error('address_detail')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Catatan --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-2">
                                Catatan Pengiriman
                                <span class="text-void-muted font-normal normal-case ml-1">(opsional)</span>
                            </label>
                            <textarea name="notes" rows="2" class="input-void resize-none"
                                      placeholder="Instruksi khusus untuk kurir...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Kurir & Layanan --}}
                <div class="bg-void-card border border-void-border rounded-2xl p-6">
                    <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-5">
                        Kurir & Layanan
                    </h2>

                    {{-- Radio kurir --}}
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-3">
                            Pilih Kurir *
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach($couriers as $code => $name)
                                <label class="cursor-pointer">
                                    <input type="radio"
                                           name="courier"
                                           value="{{ $code }}"
                                           class="sr-only peer"
                                           @change="onCourierChange('{{ $code }}')">
                                    <div class="flex items-center justify-center px-3 py-2.5 rounded-xl
                                                border-2 border-void-border text-xs font-bold text-void-gray
                                                peer-checked:border-void-accent peer-checked:text-void-accent
                                                peer-checked:bg-void-muted/20 hover:border-void-muted
                                                hover:text-void-light transition-all text-center select-none">
                                        {{ $name }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Prompt --}}
                    <div x-show="!canCalculate() && !state.costError" x-cloak
                         class="flex items-center gap-2 py-3 text-xs text-void-muted">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="promptText()"></span>
                    </div>

                    {{-- Loading --}}
                    <div x-show="state.loadingCost" x-cloak class="flex items-center gap-3 py-4">
                        <svg class="w-5 h-5 text-void-gray animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-sm text-void-gray">Menghitung ongkos kirim...</span>
                    </div>

                    {{-- Error --}}
                    <div x-show="state.costError" x-cloak class="flex items-start gap-2 py-3 text-sm text-red-400">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="state.costError"></span>
                    </div>

                    {{-- Daftar layanan --}}
                    <div x-show="shippingOptions.length > 0 && !state.loadingCost" x-cloak class="space-y-2">
                        <label class="block text-xs font-bold text-void-light uppercase tracking-wider mb-3">
                            Pilih Layanan *
                        </label>
                        <template x-for="opt in shippingOptions" :key="opt.service">
                            <label class="block cursor-pointer group">
                                <input type="radio"
                                       name="service"
                                       :value="opt.service"
                                       class="sr-only peer"
                                       @change="selectService(opt)">
                                <div class="flex items-center justify-between px-4 py-3.5 rounded-xl border-2
                                            border-void-border peer-checked:border-void-accent
                                            peer-checked:bg-void-muted/20 group-hover:border-void-muted
                                            transition-all">
                                    <div>
                                        <p class="text-sm font-bold text-void-white"
                                           x-text="form.courier.toUpperCase() + ' ' + opt.service"></p>
                                        <p class="text-xs text-void-gray mt-0.5" x-text="opt.description"></p>
                                        <p x-show="opt.etd" x-cloak class="text-[10px] text-void-muted mt-0.5"
                                           x-text="'Estimasi: ' + opt.etd"></p>
                                    </div>
                                    <p class="text-base font-black text-void-accent shrink-0 ml-4"
                                       x-text="rupiah(opt.cost)"></p>
                                </div>
                            </label>
                        </template>
                    </div>

                    <input type="hidden" name="service_description" :value="form.serviceDesc">
                    <input type="hidden" name="shipping_cost" :value="form.shippingCost">
                    <input type="hidden" name="estimated_days" :value="form.etd">
                </div>
            </div>

            {{-- KANAN — Order Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-void-card border border-void-border rounded-2xl p-6 sticky top-24">
                    <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-5">
                        Ringkasan Order
                    </h2>

                    <div class="space-y-3 mb-5 max-h-52 overflow-y-auto pr-1">
                        @foreach($cart->items as $item)
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-void-dark shrink-0 border border-void-border">
                                    <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-void-light line-clamp-1">{{ $item->product->name }}</p>
                                    <p class="text-[10px] text-void-gray">{{ $item->size }} × {{ $item->quantity }}</p>
                                </div>
                                <p class="text-xs font-bold text-void-white shrink-0">{{ $item->formatted_subtotal }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2.5 pt-4 border-t border-void-border text-sm">
                        <div class="flex justify-between">
                            <span class="text-void-gray">Subtotal Produk</span>
                            <span class="text-void-light font-semibold">{{ $cart->formatted_total }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-void-gray">Ongkos Kirim</span>
                            <span :class="form.shippingCost > 0 ? 'text-void-light font-semibold' : 'text-void-muted italic text-xs'"
                                  x-text="form.shippingCost > 0 ? rupiah(form.shippingCost) : 'Belum dipilih'"></span>
                        </div>
                    </div>

                    <div class="flex justify-between items-baseline mt-4 pt-4 border-t border-void-border">
                        <span class="text-sm font-bold text-void-white">Total Bayar</span>
                        <span class="text-xl font-black text-void-accent"
                              x-text="rupiah({{ (int)$cart->total }} + form.shippingCost)"></span>
                    </div>

                    <button type="submit"
                            :disabled="state.submitting"
                            class="w-full mt-6 py-3.5 rounded-xl text-sm font-bold
                                   bg-white text-black hover:bg-void-light
                                   disabled:opacity-50 disabled:cursor-not-allowed
                                   transition-all duration-200 flex items-center justify-center gap-2">
                        <span x-show="!state.submitting">Buat Pesanan →</span>
                        <span x-show="state.submitting" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Memproses...
                        </span>
                    </button>

                    <div class="mt-5 pt-4 border-t border-void-border flex justify-center gap-2 text-xs text-void-gray">
                        <svg class="w-3.5 h-3.5 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Transaksi aman & terenkripsi
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
<script>
function checkoutApp() {
    return {
        // Form Data
        form: {
            province_name: '',
            city_name: '',
            courier: '',
            service: '',
            serviceDesc: '',
            shippingCost: 0,
            etd: '',
        },

        // Subdistrict Search Data
        subdistrict: {
            query: '',
            results: [],
            isLoading: false,
            showDropdown: false,
            selectedId: '',
            selectedName: '',
            selectedPostal: '',
            highlightIndex: -1,
        },

        // Shipping Options
        shippingOptions: [],

        // UI State
        state: {
            loadingProvinces: false,
            loadingCost: false,
            costError: '',
            submitting: false,
        },

        // Toast
        toast: {
            show: false,
            type: 'error',
            message: '',
        },

        // Cart Data
        cartWeight: {{ $cartWeight ?? 1000 }},

        // ─────────────────────────────────────────────────────────────────────
        // INIT
        // ─────────────────────────────────────────────────────────────────────
        async init() {
            await this.loadProvinces();
            
            // Set old values if exists
            const oldCityName = '{{ old("city_name") }}';
            if (oldCityName) {
                this.form.city_name = oldCityName;
            }
        },

        // ─────────────────────────────────────────────────────────────────────
        // LOAD PROVINCES
        // ─────────────────────────────────────────────────────────────────────
        async loadProvinces() {
            this.state.loadingProvinces = true;
            try {
                const res = await this.api('GET', '{{ route('ongkir.provinces') }}');
                const provinces = res.data || [];

                const select = document.getElementById('select-province');
                if (!select) return;

                select.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                provinces.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.text = p.name;
                    opt.setAttribute('data-name', p.name);
                    select.appendChild(opt);
                });
                
                // Set old value jika ada
                const oldProvinceId = '{{ old("province_id") }}';
                if (oldProvinceId) {
                    select.value = oldProvinceId;
                    const selectedOpt = select.options[select.selectedIndex];
                    if (selectedOpt) {
                        this.form.province_name = selectedOpt.getAttribute('data-name') || selectedOpt.text;
                    }
                }
            } catch (e) {
                this.showToast('Gagal memuat provinsi: ' + e.message, 'error');
            } finally {
                this.state.loadingProvinces = false;
            }
        },

        // ─────────────────────────────────────────────────────────────────────
        // EVENT HANDLERS
        // ─────────────────────────────────────────────────────────────────────
        onProvinceChange(event) {
            const select = event.target;
            const selectedOpt = select.options[select.selectedIndex];
            
            this.form.province_name = selectedOpt?.getAttribute('data-name') || selectedOpt?.text || '';
            
            // Reset shipping when province changes
            this.resetShipping();
        },

        async onCourierChange(code) {
            this.form.courier = code;
            this.resetService();
            if (this.canCalculate()) {
                await this.calculateOngkir();
            }
        },

        selectService(opt) {
            this.form.service = opt.service;
            this.form.serviceDesc = opt.description;
            this.form.shippingCost = opt.cost;
            this.form.etd = opt.etd || '';
        },

        // ─────────────────────────────────────────────────────────────────────
        // SUBDISTRICT SEARCH
        // ─────────────────────────────────────────────────────────────────────
        async searchSubdistrict() {
            if (this.subdistrict.query.length < 3) {
                this.subdistrict.results = [];
                this.subdistrict.showDropdown = false;
                return;
            }

            this.subdistrict.isLoading = true;
            try {
                const url = '{{ route('ongkir.subdistricts') }}?search=' + encodeURIComponent(this.subdistrict.query) + '&limit=8';
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                const data = await res.json();

                this.subdistrict.results = data.data || [];
                this.subdistrict.showDropdown = this.subdistrict.results.length > 0;
                this.subdistrict.highlightIndex = -1;
            } catch (e) {
                console.error('subdistrict search:', e);
                this.subdistrict.results = [];
            } finally {
                this.subdistrict.isLoading = false;
            }
        },

        selectSubdistrict(item) {
            this.subdistrict.selectedId = item.id;
            this.subdistrict.selectedName = item.label;
            this.subdistrict.selectedPostal = item.postal_code || '';
            this.subdistrict.query = item.label;
            this.subdistrict.showDropdown = false;
            this.subdistrict.highlightIndex = -1;

            // Extract city from label (format: "KRANJI, BEKASI BARAT, BEKASI, JAWA BARAT, 17135")
            const parts = item.label.split(', ');
            if (parts.length >= 3 && !this.form.city_name) {
                // parts[0] = KRANJI (kecamatan)
                // parts[1] = BEKASI BARAT (kota bagian)
                // parts[2] = BEKASI (kota utama)
                this.form.city_name = parts[2];
            }

            // Auto-fill kode pos
            if (item.postal_code) {
                this.subdistrict.selectedPostal = item.postal_code;
                const postalInput = document.getElementById('input-postal');
                if (postalInput && !postalInput.value) {
                    postalInput.value = item.postal_code;
                }
            }

            // Trigger ongkir calculation
            if (this.form.courier) {
                this.calculateOngkir();
            }
        },

        clearSubdistrict() {
            this.subdistrict.selectedId = '';
            this.subdistrict.selectedName = '';
            this.subdistrict.selectedPostal = '';
            this.subdistrict.query = '';
            this.subdistrict.results = [];
            this.subdistrict.showDropdown = false;
            this.resetShipping();
        },

        moveSelection(dir) {
            if (!this.subdistrict.showDropdown || this.subdistrict.results.length === 0) return;
            this.subdistrict.highlightIndex = Math.max(0, Math.min(this.subdistrict.results.length - 1, this.subdistrict.highlightIndex + dir));
        },

        selectHighlighted() {
            if (this.subdistrict.highlightIndex >= 0 && this.subdistrict.results[this.subdistrict.highlightIndex]) {
                this.selectSubdistrict(this.subdistrict.results[this.subdistrict.highlightIndex]);
            }
        },

        // ─────────────────────────────────────────────────────────────────────
        // CALCULATE ONGKIR
        // ─────────────────────────────────────────────────────────────────────
        async calculateOngkir() {
            if (!this.canCalculate()) return;

            this.state.loadingCost = true;
            this.state.costError = '';
            this.shippingOptions = [];
            this.resetService();

            try {
                const res = await this.api('POST', '{{ route('ongkir.calculate') }}', {
                    destination: this.subdistrict.selectedId,
                    weight: this.cartWeight,
                    courier: this.form.courier,
                });

                if (res.success && res.data && res.data.length > 0) {
                    this.shippingOptions = res.data;
                } else {
                    this.state.costError = res.message || 'Tidak ada layanan tersedia. Coba kurir lain.';
                }
            } catch (e) {
                this.state.costError = 'Gagal menghitung ongkir. Coba lagi.';
                console.error('calculateOngkir:', e);
            } finally {
                this.state.loadingCost = false;
            }
        },

        // ─────────────────────────────────────────────────────────────────────
        // VALIDATION
        // ─────────────────────────────────────────────────────────────────────
        canCalculate() {
            return !!this.subdistrict.selectedId && !!this.form.courier;
        },

        isReady() {
            const provinceSelect = document.getElementById('select-province');
            const provinceId = provinceSelect?.value;
            
            return !!provinceId
                && !!this.form.city_name
                && !!this.subdistrict.selectedId
                && !!this.form.courier
                && !!this.form.service;
        },

        promptText() {
            if (!this.subdistrict.selectedId) return 'Pilih kecamatan/kelurahan tujuan terlebih dahulu.';
            if (!this.form.courier) return 'Pilih kurir untuk melihat opsi layanan.';
            return '';
        },

        // ─────────────────────────────────────────────────────────────────────
        // SUBMIT
        // ─────────────────────────────────────────────────────────────────────
        handleSubmit() {
            const provinceSelect = document.getElementById('select-province');
            const provinceId = provinceSelect?.value;
            
            // Debug
            console.log('=== HANDLE SUBMIT ===');
            console.log('province_id:', provinceId);
            console.log('province_name:', this.form.province_name);
            console.log('city_name:', this.form.city_name);
            console.log('subdistrict_id:', this.subdistrict.selectedId);
            console.log('subdistrict_name:', this.subdistrict.selectedName);
            console.log('courier:', this.form.courier);
            console.log('service:', this.form.service);
            console.log('shipping_cost:', this.form.shippingCost);
            
            if (!provinceId) {
                this.showToast('Provinsi belum dipilih', 'error');
                return;
            }
            if (!this.form.city_name) {
                this.showToast('Kota belum diisi', 'error');
                return;
            }
            if (!this.subdistrict.selectedId) {
                this.showToast('Kecamatan belum dipilih', 'error');
                return;
            }
            if (!this.form.courier) {
                this.showToast('Kurir belum dipilih', 'error');
                return;
            }
            if (!this.form.service) {
                this.showToast('Layanan pengiriman belum dipilih', 'error');
                return;
            }
            
            this.state.submitting = true;
            document.getElementById('checkout-form').submit();
        },

        // ─────────────────────────────────────────────────────────────────────
        // HELPERS
        // ─────────────────────────────────────────────────────────────────────
        resetShipping() {
            this.shippingOptions = [];
            this.state.costError = '';
            this.resetService();
        },

        resetService() {
            this.form.service = '';
            this.form.serviceDesc = '';
            this.form.shippingCost = 0;
            this.form.etd = '';
        },

        rupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        showToast(message, type = 'error') {
            this.toast = { show: true, type, message };
            setTimeout(() => { this.toast.show = false; }, 5000);
        },

        async api(method, url, body = null) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const opts = {
                method,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            };

            if (method === 'GET' && body) {
                url += '?' + new URLSearchParams(body).toString();
            } else if (method === 'POST' && body) {
                opts.headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(body);
            }

            const res = await fetch(url, opts);
            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                throw new Error(data.message || `HTTP ${res.status}`);
            }
            return data;
        },
    };
}
</script>
@endpush