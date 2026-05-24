<?php
// app/Http/Controllers/ShippingController.php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    public function __construct(protected RajaOngkirService $rajaOngkir) {}

    public function provinces(): JsonResponse
    {
        try {
            $provinces = $this->rajaOngkir->getProvinces();
            
            return response()->json([
                'success' => true,
                'data' => $provinces
            ]);
        } catch (\Exception $e) {
            Log::error('Shipping provinces error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data provinsi.',
                'data' => []
            ], 500);
        }
    }

    public function cities(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'province_id' => 'required'
            ]);

            // Memanggil service RajaOngkir dengan parameter dari request
            $cities = $this->rajaOngkir->getCities($request->province_id);
            
            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            Log::error('Shipping cities error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kota/kabupaten.',
                'data' => []
            ], 500);
        }
    }

    public function destinations(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'required|string|min:2'
        ]);

        $results = $this->rajaOngkir->searchDomesticDestination($request->search);
        
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    public function calculate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'origin' => 'required|integer',
                'destination' => 'required|integer',
                'weight' => 'required|integer|min:1',
                'courier' => 'nullable|string',
            ]);

            $costs = $this->rajaOngkir->calculateDomesticCost(
                $request->origin,
                $request->destination,
                $request->weight,
                $request->input('courier', 'jne')
            );

            return response()->json([
                'success' => true,
                'data' => $costs
            ]);
        } catch (\Exception $e) {
            Log::error('Shipping calculate error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}