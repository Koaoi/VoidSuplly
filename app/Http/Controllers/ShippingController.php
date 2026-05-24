<?php
// app/Http/Controllers/ShippingController.php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShippingController extends Controller
{
    public function __construct(protected RajaOngkirService $rajaOngkir) {}

    public function provinces(): JsonResponse
    {
        $provinces = $this->rajaOngkir->getProvinces();
        
        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
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

            // PERBAIKAN: Hapus tanda $ pada named parameter
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
            \Log::error('Shipping calculate error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}