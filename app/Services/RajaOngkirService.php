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
        // FIX: Gunakan config rajaongkir.php
        $this->apiKey  = config('rajaongkir.api_key', '');
        $this->baseUrl = rtrim(config('rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
        $this->originId = config('rajaongkir.origin_subdistrict', '');
        
        // Debug: log config
        Log::info('RajaOngkir Service initialized', [
            'base_url' => $this->baseUrl,
            'api_key_set' => !empty($this->apiKey),
            'origin_id' => $this->originId
        ]);
    }

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

    public function getProvinces(): array
    {
        return Cache::remember('komerce:provinces', self::CACHE_TTL, function () {
            $data = $this->get('/destination/province');
            return is_array($data) ? $data : [];
        });
    }

    // FIX: Perbaiki path dan parameter untuk cities
    public function getCities(int|string $provinceId): array
    {
        return Cache::remember("komerce:cities:{$provinceId}", self::CACHE_TTL, function () use ($provinceId) {
            $data = $this->get('/destination/city', ['province' => $provinceId]);
            return is_array($data) ? $data : [];
        });
    }

    // FIX: Perbaiki path dan parameter untuk districts
    public function getDistricts(int|string $cityId): array
    {
        return Cache::remember("komerce:districts:{$cityId}", self::CACHE_TTL, function () use ($cityId) {
            $data = $this->get('/destination/district', ['city' => $cityId]);
            return is_array($data) ? $data : [];
        });
    }

    // FIX: Perbaiki path dan parameter untuk subdistricts
    public function getSubDistricts(int|string $districtId): array
    {
        return Cache::remember("komerce:subdistricts:{$districtId}", self::CACHE_TTL, function () use ($districtId) {
            $data = $this->get('/destination/subdistrict', ['district' => $districtId]);
            return is_array($data) ? $data : [];
        });
    }

    public function searchDestination(string $keyword, int $limit = 10, int $offset = 0): array
    {
        if (empty(trim($keyword))) {
            return [];
        }

        $data = $this->get('/destination/domestic-destination', [
            'search' => trim($keyword),
            'limit'  => $limit,
            'offset' => $offset,
        ]);
        
        return is_array($data) ? $data : [];
    }

    public function calculateCost(int|string $destinationId, int $weightGram, string $courier): array
    {
        if (empty($this->originId)) {
            Log::warning('RajaOngkir: RAJAONGKIR_ORIGIN_SUBDISTRICT belum diset di .env');
            return [];
        }

        $data = $this->post('/calculate/domestic-cost', [
            'origin'      => $this->originId,
            'destination' => $destinationId,
            'weight'      => max(1000, $weightGram),
            'courier'     => strtolower($courier),
            'price'       => 'lowest',
        ]);
        
        return is_array($data) ? $data : [];
    }

    // ─── Private: HTTP Helpers ────────────────────────────────────────────────

    private function headers(): array
    {
        return [
            'key'    => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    private function get(string $path, array $query = []): ?array
    {
        try {
            $url = $this->baseUrl . $path;
            Log::info('RajaOngkir GET: ' . $url, ['query' => $query]);
            
            $response = Http::timeout(10)
                ->withHeaders($this->headers())
                ->get($url, $query);

            return $this->parseResponse($response, $path);

        } catch (\Throwable $e) {
            Log::error("RajaOngkir GET {$path} exception: {$e->getMessage()}");
            return null;
        }
    }

    private function post(string $path, array $payload = []): ?array
    {
        try {
            $url = $this->baseUrl . $path;
            Log::info('RajaOngkir POST: ' . $url, ['payload' => $payload]);
            
            $response = Http::timeout(15)
                ->withHeaders($this->headers())
                ->asForm()
                ->post($url, $payload);

            return $this->parseResponse($response, $path);

        } catch (\Throwable $e) {
            Log::error("RajaOngkir POST {$path} exception: {$e->getMessage()}");
            return null;
        }
    }

    private function parseResponse(Response $response, string $path): ?array
    {
        Log::info("RajaOngkir {$path} response status: " . $response->status());
        
        if ($response->successful()) {
            // Coba ambil dari key 'data' (format Komerce)
            $data = $response->json('data');
            
            if (is_array($data) && !empty($data)) {
                return $data;
            }
            
            // Fallback: coba ambil dari 'rajaongkir.results' (format lama)
            $rajaongkir = $response->json('rajaongkir');
            if (isset($rajaongkir['results']) && is_array($rajaongkir['results'])) {
                return $rajaongkir['results'];
            }
            
            // Jika data kosong, log untuk debug
            Log::warning("RajaOngkir {$path} data kosong", [
                'full_response' => $response->json()
            ]);
        }

        Log::warning("RajaOngkir {$path} response tidak valid", [
            'status' => $response->status(),
            'body'   => substr($response->body(), 0, 500),
        ]);

        return null;
    }
}