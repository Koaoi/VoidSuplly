<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    private $baseUrl = 'https://rajaongkir.komerce.id/api/v1';

    public function getProvinces()
    {
        try {
            $apiKey = env('RAJAONGKIR_API_KEY');
            
            if (!$apiKey) {
                return response()->json([
                    'error' => 'API Key not configured',
                    'data' => []
                ], 500);
            }

            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders(['key' => $apiKey])
                ->get($this->baseUrl . '/destination/provinces');

            $data = $response->json();

            return response()->json($data);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}