<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    private const CACHE_TTL = 86400; // 24 jam

    private string $apiKey;
    private string $baseUrl;
    private string $originId;

    public function __construct()
    {
        $this->apiKey  = config('services.rajaongkir.api_key', '');
        $this->baseUrl = rtrim(config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
        $this->originId = config('services.rajaongkir.origin_subdistrict', '');
    }

    // ─── Public: Daftar Kurir ─────────────────────────────────────────────────

    public static function availableCouriers(): array
    {
        return [
            'jne'      => 'JNE',
            'jnt'      => 'J&T Express',
            'sicepat'  => 'SiCepat',
            'anteraja' => 'AnterAja',
            'tiki'     => 'TIKI',
            'pos'      => 'POS Indonesia',
            'lion'     => 'Lion Parcel',
            'ninja'    => 'Ninja Xpress',
        ];
    }

    // ─── Public: Step-by-Step Method ─────────────────────────────────────────

    public function getProvinces(): array
    {
        return Cache::remember('komerce:provinces', self::CACHE_TTL, function () {
            return $this->get('/destination/province') ?? [];
        });
    }

    public function getCities(int|string $provinceId): array
    {
        return Cache::remember("komerce:cities:{$provinceId}", self::CACHE_TTL, function () use ($provinceId) {
            return $this->get('/city', ['province_id' => $provinceId]) ?? [];
        });
    }

    public function getDistricts(int|string $cityId): array
    {
        return Cache::remember("komerce:districts:{$cityId}", self::CACHE_TTL, function () use ($cityId) {
            return $this->get('/destination/district', ['city_id' => $cityId]) ?? [];
        });
    }

    public function getSubDistricts(int|string $districtId): array
    {
        return Cache::remember("komerce:subdistricts:{$districtId}", self::CACHE_TTL, function () use ($districtId) {
            return $this->get('/destination/subdistrict', ['district_id' => $districtId]) ?? [];
        });
    }

    // ─── Public: Direct Search Method ────────────────────────────────────────

    public function searchDestination(string $keyword, int $limit = 10, int $offset = 0): array
    {
        if (empty(trim($keyword))) {
            return [];
        }

        // Search tidak di-cache karena kombinasinya tak terbatas
        return $this->get('/destination/domestic-destination', [
            'search' => trim($keyword),
            'limit'  => $limit,
            'offset' => $offset,
        ]) ?? [];
    }

    // ─── Public: Hitung Ongkir ────────────────────────────────────────────────

    /**
     * @param  int|string  $destinationId  Subdistrict ID tujuan
     * @param  int         $weightGram     Berat dalam gram (minimum 1000)
     * @param  string      $courier        Kode kurir: jne, jnt, sicepat, dll
     * @return array       Berisi list layanan dengan cost & etd
     */
    public function calculateCost(int|string $destinationId, int $weightGram, string $courier): array
    {
        if (empty($this->originId)) {
            Log::warning('RajaOngkir: RAJAONGKIR_ORIGIN_SUBDISTRICT belum diset di .env');
        }

        return $this->post('/calculate/domestic-cost', [
            'origin'      => $this->originId,
            'destination' => $destinationId,
            'weight'      => max(1000, $weightGram),
            'courier'     => strtolower($courier),
            'price'       => 'lowest',
        ]) ?? [];
    }

    // ─── Private: HTTP Helpers ────────────────────────────────────────────────

    private function headers(): array
    {
        return [
            'key'    => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    /**
     * GET request — mengembalikan data[] atau null jika gagal.
     */
    private function get(string $path, array $query = []): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->headers())
                ->get($this->baseUrl . $path, $query);

            return $this->parseResponse($response, $path);

        } catch (\Throwable $e) {
            Log::error("RajaOngkir GET {$path} exception: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * POST request — mengembalikan data[] atau null jika gagal.
     */
    private function post(string $path, array $payload = []): ?array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(array_merge($this->headers(), [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]))
                ->asForm()
                ->post($this->baseUrl . $path, $payload);

            return $this->parseResponse($response, $path);

        } catch (\Throwable $e) {
            Log::error("RajaOngkir POST {$path} exception: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Parse response Komerce API.
     * Struktur response: { meta: {...}, data: [...] }
     */
    private function parseResponse(Response $response, string $path): ?array
    {
        if ($response->successful()) {
            $data = $response->json('data');

            if (is_array($data)) {
                return $data;
            }
        }

        Log::warning("RajaOngkir {$path} response tidak valid", [
            'status' => $response->status(),
            'body'   => substr($response->body(), 0, 500),
        ]);

        return null;
    }
}