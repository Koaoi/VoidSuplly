<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('RAJAONGKIR_API_KEY', '');
        $this->baseUrl = 'https://rajaongkir.komerce.id/api/v1/';
    }

    /**
     * Get all provinces from API
     */
    public function getProvinces(): array
    {
        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . 'province');

            if ($response->successful()) {
                $data = $response->json();
                return $data['rajaongkir']['results'] ?? [];
            }

            Log::error('RajaOngkir API Error (Provinces): ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('RajaOngkir Exception (Provinces): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get cities by province ID from API
     */
    public function getCitiesByProvince(string $provinceId): array
    {
        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . 'city', [
                    'province' => $provinceId
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $cities = $data['rajaongkir']['results'] ?? [];
                
                // Format hasil
                return array_map(function($city) {
                    return [
                        'city_id' => $city['city_id'],
                        'city_name' => $city['type'] . ' ' . $city['city_name'],
                        'type' => $city['type'],
                        'postal_code' => $city['postal_code'],
                    ];
                }, $cities);
            }

            Log::error('RajaOngkir API Error (Cities): ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('RajaOngkir Exception (Cities): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get shipping cost from API
     */
    public function getCost(string $origin, string $destination, int $weight, string $courier): array
    {
        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->asForm()
                ->post($this->baseUrl . 'cost', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['rajaongkir']['results'][0] ?? null;
                
                return $results['costs'] ?? [];
            }

            Log::error('RajaOngkir API Error (Cost): ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('RajaOngkir Exception (Cost): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list of available couriers
     */
    public static function getCouriers(): array
    {
        return [
            'jne' => 'JNE',
            'tiki' => 'TIKI',
            'pos' => 'POS Indonesia',
        ];
    }
}