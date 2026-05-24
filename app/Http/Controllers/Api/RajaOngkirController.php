<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RajaOngkirController extends Controller
{
    public function __construct(private readonly RajaOngkirService $service)
    {
        $this->middleware('auth')->except(['provinces', 'cities', 'districts', 'cost']);
    }

    public function provinces(): JsonResponse
    {
        $data = $this->service->getProvinces();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function cities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => ['required'],
        ]);

        Log::info('Cities request received', ['province_id' => $validated['province_id']]);

        $data = $this->service->getCities($validated['province_id']);

        Log::info('Cities response', ['count' => count($data)]);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => empty($data) ? 'Tidak ada kota ditemukan untuk provinsi ini.' : null,
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city_id' => ['required'],
        ]);

        $data = $this->service->getDistricts($validated['city_id']);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function subdistricts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_id' => ['required'],
        ]);

        $data = $this->service->getSubDistricts($validated['district_id']);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function cost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'destination_id' => ['required'],
            'courier'        => ['required', 'string'],
            'weight'         => ['sometimes', 'integer', 'min:1'],
        ]);

        $weight = $request->filled('weight')
            ? (int) $request->weight
            : $this->getCartWeight();

        $data = $this->service->calculateCost(
            $validated['destination_id'],
            $weight,
            $validated['courier'],
        );

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'costs'   => [],
                'message' => 'Tidak ada layanan tersedia untuk kurir ini. Coba kurir lain.',
            ]);
        }

        return response()->json([
            'success' => true,
            'costs'   => $data,
            'weight'  => $weight,
        ]);
    }

    private function getCartWeight(): int
    {
        $cart = auth()->user()->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return 1000;
        }

        return max(1000, $cart->items->sum(
            fn($item) => ($item->product->weight ?? 300) * $item->quantity
        ));
    }
}