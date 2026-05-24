@extends('layouts.app')

@section('title', 'Checkout — VOID Supply')

@section('content')
<div class="pt-24 pb-16">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="mb-8">
        <p class="text-[10px] font-bold tracking-[0.3em] text-void-gray uppercase mb-2">— Checkout</p>
        <h1 class="text-3xl font-black text-void-accent">Selesaikan Pesanan</h1>
    </div>

    {{-- Validasi error dari server --}}
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

    {{--
        x-data: state Alpine.js seluruh form checkout
        Semua AJAX request memakai fetchJson() helper yang sudah inject CSRF.
    --}}
    <form
        id="checkout-form"
        method="POST"
        action="{{ route('checkout.process') }}"
        x-data="checkoutApp()"
        @submit.prevent="handleSubmit()"
    >
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ════════════════════════════════════════════════
                 KIRI — Alamat & Pengiriman
            ═════════════════════════════════════════════════ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Alamat Pengiriman --}}
                <div class="bg-void-card border border-void-border rounded-2xl p-6">
                    <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-5">
                        Alamat Pengiriman
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Nama Penerima --}}
                        <div class="sm:col-span-2">
                            <label class="form-label">Nama Penerima *</label>
                            <input type="text" name="recipient_name"
                                   value="{{ old('recipient_name', auth()->user()->name) }}"
                                   class="input-void @error('recipient_name') border-red-500/50 @enderror"
                                   placeholder="Nama lengkap penerima" required>
                            @error('recipient_name')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nomor Telepon --}}
                        <div class="sm:col-span-2">
                            <label class="form-label">Nomor Telepon *</label>
                            <input type="text" name="phone"
                                   value="{{ old('phone') }}"
                                   class="input-void @error('phone') border-red-500/50 @enderror"
                                   placeholder="08xxxxxxxxxx" required>
                            @error('phone')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PROVINSI — dropdown statis dari server --}}
                        <div>
                            <label class="form-label">Provinsi *</label>
                            <div class="relative">
                                <select
                                    id="select-province"
                                    @change="onProvinceChange($event)"
                                    :disabled="loading.provinces"
                                    class="input-void cursor-pointer @error('province_id') border-red-500/50 @enderror"
                                    required
                                >
                                    <option value="">
                                        <template x-if="loading.provinces">Memuat provinsi...</template>
                                        <template x-if="!loading.provinces">-- Pilih Provinsi --</template>
                                    </option>
                                </select>
                                <div x-show="loading.provinces" class="select-spinner"></div>
                            </div>
                            {{-- Hidden inputs untuk submit ke server --}}
                            <input type="hidden" name="province_id"   x-bind:value="address.province_id">
                            <input type="hidden" name="province_name" x-bind:value="address.province_name">
                            @error('province_id')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KOTA — diload via AJAX setelah provinsi dipilih --}}
                        <div>
                            <label class="form-label">Kota / Kabupaten *</label>
                            <div class="relative">
                                <select
                                    id="select-city"
                                    @change="onCityChange($event)"
                                    :disabled="!address.province_id || loading.cities"
                                    class="input-void cursor-pointer disabled:opacity-50
                                           disabled:cursor-not-allowed @error('city_id') border-red-500/50 @enderror"
                                    required
                                >
                                    <option value="">
                                        <template x-if="!address.province_id">-- Pilih Provinsi Dulu --</template>
                                        <template x-if="address.province_id && loading.cities">Memuat kota...</template>
                                        <template x-if="address.province_id && !loading.cities">-- Pilih Kota --</template>
                                    </option>
                                </select>
                                <div x-show="loading.cities" class="select-spinner"></div>
                            </div>
                            <input type="hidden" name="city_id"   x-bind:value="address.city_id">
                            <input type="hidden" name="city_name" x-bind:value="address.city_name">
                            @error('city_id')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KECAMATAN — diload via AJAX setelah kota dipilih --}}
                        <div>
                            <label class="form-label">
                                Kecamatan
                                <span class="text-void-muted font-normal normal-case ml-1">(untuk akurasi ongkir)</span>
                            </label>
                            <div class="relative">
                                <select
                                    id="select-district"
                                    @change="onDistrictChange($event)"
                                    :disabled="!address.city_id || loading.districts"
                                    class="input-void cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <option value="">
                                        <template x-if="!address.city_id">-- Pilih Kota Dulu --</template>
                                        <template x-if="address.city_id && loading.districts">Memuat kecamatan...</template>
                                        <template x-if="address.city_id && !loading.districts">-- Pilih Kecamatan --</template>
                                    </option>
                                </select>
                                <div x-show="loading.districts" class="select-spinner"></div>
                            </div>
                            <input type="hidden" name="district_name" x-bind:value="address.district_name">
                        </div>

                        {{-- KODE POS --}}
                        <div>
                            <label class="form-label">Kode Pos *</label>
                            <input type="text"
                                   name="postal_code"
                                   x-ref="postalCode"
                                   value="{{ old('postal_code') }}"
                                   class="input-void @error('postal_code') border-red-500/50 @enderror"
                                   placeholder="12345" maxlength="10" required>
                            @error('postal_code')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ALAMAT DETAIL --}}
                        <div class="sm:col-span-2">
                            <label class="form-label">Alamat Lengkap *</label>
                            <textarea name="address_detail" rows="3"
                                      class="input-void resize-none @error('address_detail') border-red-500/50 @enderror"
                                      placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                                      required>{{ old('address_detail') }}</textarea>
                            @error('address_detail')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- CATATAN --}}
                        <div class="sm:col-span-2">
                            <label class="form-label">
                                Catatan
                                <span class="text-void-muted font-normal normal-case ml-1">(opsional)</span>
                            </label>
                            <textarea name="notes" rows="2" class="input-void resize-none"
                                      placeholder="Instruksi khusus...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Pilih Kurir & Layanan --}}
                <div class="bg-void-card border border-void-border rounded-2xl p-6">
                    <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-5">
                        Kurir & Layanan
                    </h2>

                    {{-- Pilih kurir --}}
                    <div class="mb-5">
                        <label class="form-label mb-3">Pilih Kurir *</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach($couriers as $code => $name)
                                <label class="cursor-pointer">
                                    <input type="radio"
                                           name="_courier_ui"
                                           value="{{ $code }}"
                                           class="sr-only peer"
                                           @change="onCourierChange('{{ $code }}')">
                                    <div class="flex items-center justify-center px-3 py-2.5 rounded-xl
                                                border-2 border-void-border text-xs font-bold text-void-gray
                                                peer-checked:border-void-accent peer-checked:text-void-accent
                                                peer-checked:bg-void-muted/20 hover:border-void-muted
                                                hover:text-void-light transition-all text-center">
                                        {{ $name }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <input type="hidden" name="courier" x-bind:value="shipping.courier">
                    </div>

                    {{-- State: belum pilih lokasi atau kurir --}}
                    <div x-show="!canFetchCost()" class="info-box">
                        <svg class="w-4 h-4 shrink-0 text-void-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="infoText()"></span>
                    </div>

                    {{-- Loading ongkir --}}
                    <div x-show="loading.costs && canFetchCost()"
                         class="flex items-center gap-3 py-4">
                        <svg class="w-5 h-5 text-void-gray animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-sm text-void-gray">Menghitung ongkos kirim...</span>
                    </div>

                    {{-- Error ongkir --}}
                    <div x-show="errors.cost"
                         class="flex items-center gap-2 py-3 text-sm text-red-400">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="errors.cost"></span>
                    </div>

                    {{-- Daftar layanan pengiriman --}}
                    <div x-show="shipping.options.length > 0 && !loading.costs" class="space-y-2">
                        <label class="form-label mb-3">Pilih Layanan *</label>
                        <template x-for="opt in shipping.options" :key="opt.service">
                            <label class="block cursor-pointer">
                                <input type="radio"
                                       name="_service_ui"
                                       :value="opt.service"
                                       class="sr-only peer"
                                       @change="selectService(opt)">
                                <div class="flex items-center justify-between px-4 py-3 rounded-xl border-2
                                            border-void-border peer-checked:border-void-accent
                                            peer-checked:bg-void-muted/20 hover:border-void-muted transition-all">
                                    <div>
                                        <p class="text-sm font-bold text-void-white"
                                           x-text="shipping.courier.toUpperCase() + ' ' + opt.service"></p>
                                        <p class="text-xs text-void-gray" x-text="opt.description"></p>
                                        <p class="text-[10px] text-void-muted mt-0.5"
                                           x-text="'Estimasi: ' + (opt.etd || '-') + ' hari kerja'"></p>
                                    </div>
                                    <p class="text-base font-black text-void-accent shrink-0 ml-4"
                                       x-text="formatRupiah(opt.cost)"></p>
                                </div>
                            </label>
                        </template>
                    </div>

                    {{-- Hidden fields untuk submit --}}
                    <input type="hidden" name="service"             x-bind:value="shipping.service">
                    <input type="hidden" name="service_description" x-bind:value="shipping.serviceDesc">
                    <input type="hidden" name="shipping_cost"       x-bind:value="shipping.cost">
                    <input type="hidden" name="estimated_days"      x-bind:value="shipping.etd">
                </div>
            </div>

            {{-- ════════════════════════════════════════════════
                 KANAN — Order Summary
            ═════════════════════════════════════════════════ --}}
            <div class="lg:col-span-1">
                <div class="bg-void-card border border-void-border rounded-2xl p-6 sticky top-24">

                    <h2 class="text-xs font-bold tracking-widest text-void-white uppercase mb-5">
                        Ringkasan Order
                    </h2>

                    {{-- Daftar item --}}
                    <div class="space-y-3 mb-5 max-h-52 overflow-y-auto pr-1">
                        @foreach($cart->items as $item)
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-void-dark shrink-0 border border-void-border">
                                    <img src="{{ $item->product->primary_image_url }}"
                                         alt="{{ $item->product->name }}"
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-void-light line-clamp-1">
                                        {{ $item->product->name }}
                                    </p>
                                    <p class="text-[10px] text-void-gray">{{ $item->size }} × {{ $item->quantity }}</p>
                                </div>
                                <p class="text-xs font-bold text-void-white shrink-0">
                                    {{ $item->formatted_subtotal }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totals --}}
                    <div class="space-y-2.5 pt-4 border-t border-void-border text-sm">
                        <div class="flex justify-between">
                            <span class="text-void-gray">Subtotal</span>
                            <span class="text-void-light font-semibold">{{ $cart->formatted_total }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-void-gray">Ongkos Kirim</span>
                            <span x-text="shipping.cost > 0
                                    ? formatRupiah(shipping.cost)
                                    : 'Belum dipilih'"
                                  :class="shipping.cost > 0 ? 'text-void-light font-semibold' : 'text-void-muted italic text-xs'">
                            </span>
                        </div>
                    </div>

                    {{-- Grand Total --}}
                    <div class="flex justify-between items-baseline mt-4 pt-4 border-t border-void-border">
                        <span class="text-sm font-bold text-void-white">Total Bayar</span>
                        <span class="text-xl font-black text-void-accent"
                              x-text="formatRupiah({{ $cart->total }} + shipping.cost)">
                        </span>
                    </div>

                    {{-- Submit button --}}
                    <button type="submit"
                            :disabled="!isFormReady() || isSubmitting"
                            class="w-full mt-6 py-3.5 rounded-xl text-sm font-bold
                                   transition-all duration-200 flex items-center justify-center gap-2
                                   bg-white text-black hover:bg-void-light
                                   disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!isSubmitting">
                            <span>Buat Pesanan</span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Memproses...
                            </span>
                        </template>
                    </button>

                    <p x-show="!isFormReady() && !isSubmitting"
                       class="text-[10px] text-void-muted text-center mt-2">
                        Lengkapi semua data dan pilih layanan pengiriman
                    </p>

                    {{-- Security note --}}
                    <div class="mt-5 pt-4 border-t border-void-border flex items-center justify-center gap-2 text-xs text-void-gray">
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
/**
 * VOID Supply — Checkout Alpine.js Component
 *
 * Flow:
 * 1. Init → loadProvinces() → render select provinsi
 * 2. Pilih provinsi → loadCities(province_id)
 * 3. Pilih kota → loadDistricts(city_id)
 * 4. Pilih kecamatan → auto isi kode pos + siap hitung ongkir
 * 5. Pilih kurir → loadCosts(destination_id, courier)
 * 6. Pilih layanan → update shipping.cost
 * 7. Submit → validateForm() → submit native form
 */
function checkoutApp() {
    return {

        // ── State Address ─────────────────────────────────────────────────────
        address: {
            province_id:   '',
            province_name: '',
            city_id:       '',
            city_name:     '',
            district_id:   '',
            district_name: '',
        },

        // ── State Shipping ────────────────────────────────────────────────────
        shipping: {
            courier:     '',
            options:     [],
            service:     '',
            serviceDesc: '',
            cost:        0,
            etd:         '',
        },

        // ── Loading states ────────────────────────────────────────────────────
        loading: {
            provinces: false,
            cities:    false,
            districts: false,
            costs:     false,
        },

        // ── Error messages ────────────────────────────────────────────────────
        errors: {
            cost: '',
        },

        // ── Form state ────────────────────────────────────────────────────────
        isSubmitting: false,

        // ─────────────────────────────────────────────────────────────────────
        // INIT — load provinces saat komponen dimount
        // ─────────────────────────────────────────────────────────────────────

        async init() {
            await this.loadProvinces();
        },

        // ─────────────────────────────────────────────────────────────────────
        // LOAD FUNCTIONS
        // ─────────────────────────────────────────────────────────────────────

        async loadProvinces() {
            this.loading.provinces = true;
            try {
                const res  = await this.fetchJson('GET', '{{ route('shipping.provinces') }}');
                const data = res.data || [];
                this.populateSelect('select-province', data, 'id', 'name', '-- Pilih Provinsi --');
            } catch (e) {
                console.error('loadProvinces error:', e);
            } finally {
                this.loading.provinces = false;
            }
        },

        async loadCities(provinceId) {
            this.loading.cities = true;
            this.resetFrom('city');
            try {
                const res  = await this.fetchJson('GET', '{{ route('shipping.cities') }}', { province_id: provinceId });
                const data = res.data || [];
                this.populateSelect('select-city', data, 'id', 'name', '-- Pilih Kota --');
            } catch (e) {
                console.error('loadCities error:', e);
            } finally {
                this.loading.cities = false;
            }
        },

        async loadDistricts(cityId) {
            this.loading.districts = true;
            this.resetFrom('district');
            try {
                const res  = await this.fetchJson('GET', '{{ route('shipping.districts') }}', { city_id: cityId });
                const data = res.data || [];
                this.populateSelect('select-district', data, 'id', 'name', '-- Pilih Kecamatan --');
            } catch (e) {
                console.error('loadDistricts error:', e);
            } finally {
                this.loading.districts = false;
            }
        },

        async loadCosts() {
            if (!this.canFetchCost()) return;

            this.loading.costs   = true;
            this.errors.cost     = '';
            this.shipping.options = [];
            this.resetService();

            // Pakai district_id jika tersedia (lebih akurat), fallback ke city_id
            const destinationId = this.address.district_id || this.address.city_id;

            try {
                const res = await this.fetchJson('POST', '{{ route('shipping.cost') }}', {
                    destination_id: destinationId,
                    courier:        this.shipping.courier,
                });

                if (res.success) {
                    this.shipping.options = res.costs || [];

                    if (this.shipping.options.length === 0) {
                        this.errors.cost = 'Tidak ada layanan tersedia. Coba kurir lain.';
                    }
                } else {
                    this.errors.cost = res.message || 'Gagal menghitung ongkos kirim.';
                }
            } catch (e) {
                this.errors.cost = 'Gagal terhubung ke server. Coba lagi.';
                console.error('loadCosts error:', e);
            } finally {
                this.loading.costs = false;
            }
        },

        // ─────────────────────────────────────────────────────────────────────
        // EVENT HANDLERS
        // ─────────────────────────────────────────────────────────────────────

        async onProvinceChange(event) {
            const select = event.target;
            const opt    = select.options[select.selectedIndex];

            this.address.province_id   = select.value;
            this.address.province_name = opt?.getAttribute('data-name') || opt?.text || '';

            this.resetFrom('city');

            if (this.address.province_id) {
                await this.loadCities(this.address.province_id);
            }
        },

        async onCityChange(event) {
            const select = event.target;
            const opt    = select.options[select.selectedIndex];

            this.address.city_id   = select.value;
            this.address.city_name = opt?.getAttribute('data-name') || opt?.text || '';

            // Auto isi kode pos dari data attribute jika ada
            const postalCode = opt?.getAttribute('data-postal');
            if (postalCode && this.$refs.postalCode && !this.$refs.postalCode.value) {
                this.$refs.postalCode.value = postalCode;
            }

            this.resetFrom('district');

            if (this.address.city_id) {
                await this.loadDistricts(this.address.city_id);
                // Jika sudah pilih kurir, recalculate dengan city_id dulu
                if (this.shipping.courier) await this.loadCosts();
            }
        },

        onDistrictChange(event) {
            const select = event.target;
            const opt    = select.options[select.selectedIndex];

            this.address.district_id   = select.value;
            this.address.district_name = opt?.text || '';

            // Recalculate ongkir dengan district_id (lebih akurat)
            if (this.shipping.courier) this.loadCosts();
        },

        async onCourierChange(courierCode) {
            this.shipping.courier = courierCode;
            this.resetService();
            await this.loadCosts();
        },

        selectService(opt) {
            this.shipping.service     = opt.service;
            this.shipping.serviceDesc = opt.description;
            this.shipping.cost        = opt.cost;
            this.shipping.etd         = opt.etd || '';
        },

        // ─────────────────────────────────────────────────────────────────────
        // FORM VALIDATION & SUBMIT
        // ─────────────────────────────────────────────────────────────────────

        canFetchCost() {
            return this.address.city_id !== '' && this.shipping.courier !== '';
        },

        isFormReady() {
            return this.address.province_id !== ''
                && this.address.city_id     !== ''
                && this.shipping.courier    !== ''
                && this.shipping.service    !== '';
        },

        handleSubmit() {
            if (!this.isFormReady()) {
                alert('Pastikan semua data sudah diisi dan layanan pengiriman sudah dipilih.');
                return;
            }
            this.isSubmitting = true;
            document.getElementById('checkout-form').submit();
        },

        // ─────────────────────────────────────────────────────────────────────
        // HELPERS
        // ─────────────────────────────────────────────────────────────────────

        /**
         * Reset state mulai dari level tertentu ke bawah.
         * 'city' → reset city, district, shipping
         * 'district' → reset district, shipping
         * 'shipping' → reset shipping options saja
         */
        resetFrom(level) {
            if (level === 'city') {
                this.address.city_id   = '';
                this.address.city_name = '';
                this.clearSelect('select-city');
            }
            if (level === 'city' || level === 'district') {
                this.address.district_id   = '';
                this.address.district_name = '';
                this.clearSelect('select-district');
            }
            this.resetService();
            this.shipping.options = [];
            this.errors.cost      = '';
        },

        resetService() {
            this.shipping.service     = '';
            this.shipping.serviceDesc = '';
            this.shipping.cost        = 0;
            this.shipping.etd         = '';
        },

        /**
         * Isi <select> dengan array data dari API.
         * @param {string} id       - ID elemen select
         * @param {array}  items    - Array dari API: [{id, name}, ...]
         * @param {string} valKey   - Key untuk value option (mis: 'id')
         * @param {string} textKey  - Key untuk text option (mis: 'name')
         * @param {string} placeholder
         */
        populateSelect(id, items, valKey, textKey, placeholder) {
            const select = document.getElementById(id);
            if (!select) return;

            select.innerHTML = `<option value="">${placeholder}</option>`;

            items.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item[valKey];
                opt.text  = item[textKey];
                // Simpan nama di data-name untuk dibaca Alpine
                opt.setAttribute('data-name', item[textKey]);
                // Kode pos (jika ada, untuk Komerce API city data)
                if (item.postal_code) opt.setAttribute('data-postal', item.postal_code);
                select.appendChild(opt);
            });
        },

        clearSelect(id) {
            const select = document.getElementById(id);
            if (select) select.innerHTML = '<option value="">--</option>';
        },

        /**
         * Helper AJAX yang otomatis inject CSRF token.
         * Mendukung GET (query string) dan POST (JSON body).
         */
        async fetchJson(method, url, payload = {}) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            let fetchUrl = url;
            const options = {
                method,
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            };

            if (method === 'GET' && Object.keys(payload).length > 0) {
                fetchUrl += '?' + new URLSearchParams(payload).toString();
            } else if (method === 'POST') {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(payload);
            }

            const response = await fetch(fetchUrl, options);

            if (!response.ok) {
                const err = await response.json().catch(() => ({}));
                throw new Error(err.message || `HTTP ${response.status}`);
            }

            return response.json();
        },

        formatRupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID');
        },

        infoText() {
            if (!this.address.city_id && !this.address.province_id) return 'Pilih provinsi dan kota terlebih dahulu.';
            if (!this.address.city_id) return 'Pilih kota terlebih dahulu.';
            if (!this.shipping.courier) return 'Pilih kurir untuk melihat opsi layanan.';
            return '';
        },
    };
}
</script>
@endpush