// resources/js/shipping-form.js
// atau bisa langsung di <script> tag

function shippingForm() {
    return {
        // ── State ────────────────────────────────────────────────
        provinces: [],
        cities: [],
        selectedProvince: '',
        selectedCity: '',

        isLoadingProvinces: false,
        isLoadingCities: false,

        provinceError: null,
        cityError: null,

        // Abort controller untuk cancel request yang sedang berjalan
        _cityAbortController: null,

        // Retry configuration
        _retryConfig: {
            maxAttempts: 3,
            delayMs: 1000,
            backoffMultiplier: 2,
        },

        // ── Lifecycle ────────────────────────────────────────────
        async init() {
            await this.loadProvinces();
        },

        // ── Cleanup saat component di-destroy ───────────────────
        destroy() {
            this._cancelPendingCityRequest();
        },

        // ── Load Provinces ───────────────────────────────────────
        async loadProvinces() {
            this.isLoadingProvinces = true;
            this.provinceError = null;

            try {
                const data = await this._fetchWithRetry(
                    '/api/rajaongkir/provinces',
                    { method: 'GET' }
                );

                // ✅ Guard: pastikan komponen masih mounted sebelum update state
                if (!this.$el) return;

                this.provinces = Array.isArray(data?.provinces) ? data.provinces : [];

            } catch (error) {
                if (!this.$el) return; // Component sudah unmounted, abort

                this.provinceError = this._humanizeError(error);
                this.provinces = [];

                console.error('[ShippingForm] loadProvinces failed:', error);
            } finally {
                // Double-check elemen masih ada sebelum update loading state
                if (this.$el) {
                    this.isLoadingProvinces = false;
                }
            }
        },

        // ── Load Cities ──────────────────────────────────────────
        async loadCities() {
            // Guard: jangan lanjut jika province belum dipilih
            if (!this.selectedProvince) {
                this.cities = [];
                this.selectedCity = '';
                this.cityError = null;
                return;
            }

            // ✅ Cancel request sebelumnya jika masih pending
            // Ini mencegah race condition saat user ganti province cepat-cepat
            this._cancelPendingCityRequest();
            this._cityAbortController = new AbortController();

            // Reset state kota saat ganti province
            this.cities = [];
            this.selectedCity = '';
            this.cityError = null;
            this.isLoadingCities = true;

            try {
                const data = await this._fetchWithRetry(
                    `/api/rajaongkir/cities?province=${this.selectedProvince}`,
                    { signal: this._cityAbortController?.signal }
                );

                // ✅ Guard: cek komponen masih ada & request tidak di-cancel
                if (!this.$el) return;

                this.cities = Array.isArray(data?.cities) ? data.cities : [];

                if (this.cities.length === 0) {
                    this.cityError = 'Tidak ada kota tersedia untuk provinsi ini.';
                }

            } catch (error) {
                // ✅ Jangan update state jika request di-cancel (bukan error sebenarnya)
                if (error.name === 'AbortError') return;
                if (!this.$el) return;

                this.cityError = this._humanizeError(error);
                this.cities = [];

                console.error('[ShippingForm] loadCities failed:', error);

            } finally {
                if (this.$el && error?.name !== 'AbortError') {
                    this.isLoadingCities = false;
                }
                this._cityAbortController = null;
            }
        },

        // ── Private: Fetch dengan Retry + Exponential Backoff ────
        async _fetchWithRetry(url, options = {}, attempt = 1) {
            const { maxAttempts, delayMs, backoffMultiplier } = this._retryConfig;

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    ...options,
                });

                // ✅ Handle 503 dan 5xx: retry
                if (response.status === 503 || (response.status >= 500 && attempt < maxAttempts)) {
                    console.warn(`[ShippingForm] ${response.status} pada attempt ${attempt}/${maxAttempts}, retrying...`);
                    await this._delay(delayMs * Math.pow(backoffMultiplier, attempt - 1));
                    return this._fetchWithRetry(url, options, attempt + 1);
                }

                // ✅ Handle 4xx: tidak perlu retry, langsung throw
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    const error = new Error(errorData?.message || `Request gagal (${response.status})`);
                    error.status = response.status;
                    throw error;
                }

                const data = await response.json();
                return data;

            } catch (error) {
                // Re-throw AbortError tanpa retry
                if (error.name === 'AbortError') throw error;

                // Network error (offline, timeout) → retry jika masih ada kesempatan
                if (!error.status && attempt < maxAttempts) {
                    console.warn(`[ShippingForm] Network error, attempt ${attempt}/${maxAttempts}:`, error.message);
                    await this._delay(delayMs * Math.pow(backoffMultiplier, attempt - 1));
                    return this._fetchWithRetry(url, options, attempt + 1);
                }

                throw error;
            }
        },

        // ── Private: Cancel request yang pending ─────────────────
        _cancelPendingCityRequest() {
            if (this._cityAbortController) {
                this._cityAbortController.abort();
                this._cityAbortController = null;
            }
        },

        // ── Private: Delay helper ─────────────────────────────────
        _delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },

        // ── Private: Humanize error messages ─────────────────────
        _humanizeError(error) {
            if (error.status === 503) {
                return 'Layanan pengiriman sedang tidak tersedia. Coba beberapa saat lagi.';
            }
            if (error.status === 429) {
                return 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.';
            }
            if (error.status >= 500) {
                return 'Terjadi kesalahan server. Silakan coba lagi.';
            }
            if (error.status === 404) {
                return 'Data tidak ditemukan.';
            }
            if (!navigator.onLine) {
                return 'Tidak ada koneksi internet. Periksa koneksi Anda.';
            }
            return error.message || 'Terjadi kesalahan. Silakan coba lagi.';
        },
    };
}

// Register ke Alpine
document.addEventListener('alpine:init', () => {
    Alpine.data('shippingForm', shippingForm);
});