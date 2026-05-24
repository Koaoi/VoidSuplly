<?php
// app/Http/Controllers/RajaOngkirControllerV2.php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RajaOngkirControllerV2 extends Controller
{
    private string $apiKey;
    private string $baseUrl;
    private string $originId;
    private int    $timeout;
    private int    $cacheTtl;

    public function __construct()
    {
        $this->apiKey   = config('rajaongkir.api_key', '');
        $this->baseUrl  = rtrim(config('rajaongkir.base_url', ''), '/');
        $this->originId = config('rajaongkir.origin_id', '17473');
        $this->timeout  = config('rajaongkir.timeout', 15);
        $this->cacheTtl = config('rajaongkir.cache_ttl', 86400);
    }

    // =========================================================================
    // PUBLIC METHODS
    // =========================================================================

    /**
     * GET /ongkir/provinces
     * Ambil semua provinsi — di-cache 24 jam.
     *
     * Response Komerce: { meta: {...}, data: [{id, name}, ...] }
     */
    public function getProvinces(): JsonResponse
    {
        $data = Cache::remember('rajaongkir_v2_provinces', $this->cacheTtl, function () {
            return $this->callApi('GET', '/destination/province');
        });

        if ($data === null) {
            return $this->errorResponse('Gagal memuat daftar provinsi. API sedang gangguan.');
        }

        return $this->successResponse($data);
    }

    /**
     * GET /ongkir/cities?search=keyword
     *
     * Komerce tidak punya endpoint /city?province_id=X seperti API lama.
     * Solusi: pakai search endpoint, lalu extract city_name unik.
     * Gunakan ini untuk autocomplete field kota.
     *
     * @query string search  Nama kota yang dicari (min 3 karakter)
     */
    public function getCities(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter search wajib diisi (min. 2 karakter).',
                'data'    => [],
            ], 422);
        }

        $keyword = trim($request->search);

        // Search destination, ambil city_name unik sebagai hasil
        $destinations = $this->callApi('GET', '/destination/domestic-destination', [
            'search' => $keyword,
            'limit'  => 50,
            'offset' => 0,
        ]);

        if ($destinations === null) {
            return $this->errorResponse('Gagal mencari kota. Coba lagi.');
        }

        // Extract kota unik dari hasil search
        $cities = collect($destinations)
            ->unique('city_name')
            ->sortBy('city_name')
            ->values()
            ->map(fn($item) => [
                'city_name'     => $item['city_name']     ?? '',
                'province_name' => $item['province_name'] ?? '',
            ])
            ->filter(fn($c) => !empty($c['city_name']))
            ->values()
            ->all();

        return $this->successResponse($cities);
    }

    /**
     * GET /ongkir/subdistricts?search=keyword
     *
     * Cari kecamatan/kelurahan dengan keyword.
     * Response berisi ID yang digunakan untuk calculateOngkir.
     *
     * @query string search  Nama kelurahan/kecamatan (min 3 karakter)
     * @query int    limit   Jumlah hasil (default 10, max 20)
     */
    public function getSubdistricts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => ['required', 'string', 'min:3', 'max:100'],
            'limit'  => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter search wajib diisi (min. 3 karakter).',
                'data'    => [],
            ], 422);
        }

        $keyword = trim($request->search);
        $limit   = (int) $request->get('limit', 10);

        $data = $this->callApi('GET', '/destination/domestic-destination', [
            'search' => $keyword,
            'limit'  => $limit,
            'offset' => 0,
        ]);

        if ($data === null) {
            return $this->errorResponse('Gagal mencari kecamatan. Coba lagi.');
        }

        // Format untuk autocomplete: tampilkan label lengkap + simpan ID
        $formatted = collect($data)->map(fn($item) => [
            'id'               => $item['id'],
            'label'            => $this->buildLabel($item),
            'subdistrict_name' => $item['subdistrict_name'] ?? $item['name'] ?? '',
            'district_name'    => $item['district_name']    ?? '',
            'city_name'        => $item['city_name']        ?? '',
            'province_name'    => $item['province_name']    ?? '',
            'postal_code'      => $item['postal_code']      ?? $item['zip_code'] ?? '',
        ])->values()->all();

        return $this->successResponse($formatted);
    }

    /**
     * POST /ongkir/calculate
     *
     * Hitung ongkos kirim.
     *
     * @body int    destination  Subdistrict ID tujuan (dari getSubdistricts)
     * @body int    weight       Berat total dalam gram (min 1000)
     * @body string courier      Kode kurir: jne, jnt, sicepat, dll
     *
     * Response: [{ service, description, cost, etd }, ...]
     */
    public function calculateOngkir(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'destination' => ['required'],
            'weight'      => ['required', 'integer', 'min:1'],
            'courier'     => ['required', 'string', 'in:' . implode(',', array_keys(config('rajaongkir.couriers', [])))],
        ], [
            'destination.required' => 'Tujuan pengiriman wajib dipilih.',
            'weight.required'      => 'Berat paket wajib diisi.',
            'courier.required'     => 'Kurir wajib dipilih.',
            'courier.in'           => 'Kurir tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ], 422);
        }

        $weight = max(1000, (int) $request->weight); // minimum 1 kg

        $data = $this->callApi('POST', '/calculate/domestic-cost', [
            'origin'      => $this->originId,
            'destination' => (string) $request->destination,
            'weight'      => $weight,
            'courier'     => strtolower($request->courier),
            'price'       => 'lowest',
        ]);

        if ($data === null) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ongkos kirim. API sedang gangguan.',
                'data'    => [],
            ], 503);
        }

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada layanan tersedia untuk kurir ini ke tujuan tersebut.',
                'data'    => [],
            ]);
        }

        return $this->successResponse($data);
    }

    /**
     * GET /ongkir/couriers
     * Kembalikan daftar kurir yang tersedia (dari config).
     */
    public function getCouriers(): JsonResponse
    {
        $couriers = collect(config('rajaongkir.couriers', []))
            ->map(fn($name, $code) => ['code' => $code, 'name' => $name])
            ->values()
            ->all();

        return $this->successResponse($couriers);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Kirim HTTP request ke Komerce API.
     * Mengembalikan data[] jika sukses, null jika gagal.
     */
    private function callApi(string $method, string $path, array $params = []): ?array
    {
        $url     = $this->baseUrl . $path;
        $headers = [
            'key'    => $this->apiKey,
            'Accept' => 'application/json',
        ];

        try {
            $http = Http::timeout($this->timeout)->withHeaders($headers);

            $response = match (strtoupper($method)) {
                'GET'  => $http->get($url, $params),
                'POST' => $http->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
                               ->asForm()
                               ->post($url, $params),
                default => throw new \InvalidArgumentException("Method {$method} tidak didukung."),
            };

            if ($response->successful()) {
                // Komerce response: { meta: { code: 200 }, data: [...] }
                $body = $response->json();

                // Validasi meta code
                $code = $body['meta']['code'] ?? $response->status();
                if ($code !== 200) {
                    Log::warning("RajaOngkir {$method} {$path}: meta code {$code}", [
                        'params' => $params,
                        'body'   => substr($response->body(), 0, 500),
                    ]);
                    return null;
                }

                $data = $body['data'] ?? null;
                return is_array($data) ? $data : [];
            }

            Log::warning("RajaOngkir {$method} {$path}: HTTP {$response->status()}", [
                'params' => array_merge($params, ['api_key' => '***']),
                'body'   => substr($response->body(), 0, 500),
            ]);

            return null;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("RajaOngkir connection error {$path}: " . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
            Log::error("RajaOngkir unexpected error {$path}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Build label lengkap untuk autocomplete dropdown.
     * Format: "KELURAHAN, KECAMATAN, KOTA, PROVINSI, 12345"
     */
    private function buildLabel(array $item): string
    {
        $parts = array_filter([
            strtoupper($item['subdistrict_name'] ?? $item['name'] ?? ''),
            strtoupper($item['district_name']    ?? ''),
            strtoupper($item['city_name']        ?? ''),
            strtoupper($item['province_name']    ?? ''),
            $item['postal_code'] ?? $item['zip_code'] ?? '',
        ]);

        return implode(', ', $parts);
    }

    private function successResponse(array $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    private function errorResponse(string $message, int $status = 503): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => [],
        ], $status);
    }
}