<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    public function __construct(private readonly RajaOngkirService $service)
    {
        $this->middleware('auth');
    }

    // ─── Step-by-Step endpoints ───────────────────────────────────────────────

    public function provinces(): JsonResponse
    {
        $data = $this->service->getProvinces();

        return $this->successOrError($data, 'Gagal memuat daftar provinsi.');
    }

    public function cities(Request $request): JsonResponse
    {
        $request->validate(['province_id' => ['required']]);

        $data = $this->service->getCities($request->province_id);

        return $this->successOrError($data, 'Gagal memuat daftar kota.');
    }

    public function districts(Request $request): JsonResponse
    {
        $request->validate(['city_id' => ['required']]);

        $data = $this->service->getDistricts($request->city_id);

        return $this->successOrError($data, 'Gagal memuat daftar kecamatan.');
    }

    public function subdistricts(Request $request): JsonResponse
    {
        $request->validate(['district_id' => ['required']]);

        $data = $this->service->getSubDistricts($request->district_id);

        return $this->successOrError($data, 'Gagal memuat daftar kelurahan.');
    }

    // ─── Direct Search endpoint ───────────────────────────────────────────────

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'      => ['required', 'string', 'min:2', 'max:100'],
            'limit'  => ['sometimes', 'integer', 'min:1', 'max:50'],
            'offset' => ['sometimes', 'integer', 'min:0'],
        ]);

        $data = $this->service->searchDestination(
            $request->q,
            (int) $request->get('limit', 10),
            (int) $request->get('offset', 0),
        );

        return $this->successOrError($data, 'Gagal mencari destinasi.');
    }

    // ─── Calculate Cost endpoint ──────────────────────────────────────────────

    public function cost(Request $request): JsonResponse
    {
        $request->validate([
            'destination_id' => ['required'],
            'courier'        => ['required', 'string', 'in:' . implode(',', array_keys(RajaOngkirService::availableCouriers()))],
            'weight'         => ['sometimes', 'integer', 'min:1'],
        ]);

        // Jika weight tidak dikirim, hitung dari cart user
        $weight = $request->filled('weight')
            ? (int) $request->weight
            : $this->getCartWeight();

        $data = $this->service->calculateCost(
            $request->destination_id,
            $weight,
            $request->courier,
        );

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada layanan tersedia untuk kurir ini. Coba kurir lain.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'costs'   => $data,
            'weight'  => $weight,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getCartWeight(): int
    {
        $cart = auth()->user()->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return 1000;
        }

        $total = $cart->items->sum(
            fn($item) => ($item->product->weight ?? 300) * $item->quantity
        );

        return max(1000, $total);
    }

    private function successOrError(?array $data, string $errorMessage): JsonResponse
    {
        if ($data === null || empty($data)) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage . ' API mungkin sedang gangguan.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}