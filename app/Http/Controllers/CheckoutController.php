<?php
// app/Http/Controllers/CheckoutController.php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingAddress;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    protected RajaOngkirService $rajaOngkir;
    protected $originCityId;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->middleware('auth');
        $this->rajaOngkir = $rajaOngkir;
        $this->originCityId = config('services.rajaongkir.origin', 23);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Halaman Utama
    // ──────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $cart = $this->getUserCart();
        
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Keranjangmu kosong.');
        }

        $provinces = $this->fetchProvinces();
        $couriers = RajaOngkirService::getCouriers();

        return view('checkout.index', compact('cart', 'provinces', 'couriers'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // AJAX Endpoints
    // ──────────────────────────────────────────────────────────────────────────

    public function getCities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return $this->jsonError($validator->errors()->first(), 422);
        }

        try {
            $cities = $this->rajaOngkir->getCitiesByProvince($request->province_id);
            
            return response()->json([
                'success' => true,
                'cities' => $cities,
                'count' => count($cities)
            ]);
        } catch (\Exception $e) {
            Log::error('Get cities error: ' . $e->getMessage());
            return $this->jsonError('Gagal mengambil data kota: ' . $e->getMessage(), 500);
        }
    }

    public function getOngkir(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_id' => ['required', 'string'],
            'courier' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return $this->jsonError($validator->errors()->first(), 422);
        }

        $cart = $this->getUserCart();
        
        if (!$cart || $cart->items->isEmpty()) {
            return $this->jsonError('Keranjang kosong', 422);
        }

        try {
            $totalWeight = $this->calculateTotalWeight($cart);
            $costs = $this->rajaOngkir->getCost(
                $this->originCityId,
                $request->city_id,
                $totalWeight,
                $request->courier
            );

            return response()->json([
                'success' => true,
                'costs' => $costs,
                'weight' => $totalWeight,
                'weight_kg' => round($totalWeight / 1000, 2)
            ]);
        } catch (\Exception $e) {
            Log::error('Get ongkir error: ' . $e->getMessage());
            return $this->jsonError('Gagal menghitung ongkos kirim: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Proses Checkout
    // ──────────────────────────────────────────────────────────────────────────

    public function process(Request $request)
    {
        try {
            $validated = $this->validateCheckout($request);
            $cart = $this->getUserCart();

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('cart.index')->with('warning', 'Keranjangmu kosong.');
            }

            if ($stockError = $this->validateStock($cart)) {
                return back()->withInput()->with('error', $stockError);
            }

            DB::beginTransaction();

            $order = $this->createOrder($cart, $validated);
            $this->createOrderItems($order, $cart);
            $this->createShippingAddress($order, $validated);
            $this->createPayment($order, $order->total_price);
            $this->clearCart($cart);

            DB::commit();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'user_id' => auth()->id(),
                'total' => $order->total_price
            ]);

            return redirect()->route('payment.show', $order->order_code)
                ->with('success', 'Order berhasil dibuat! Silakan selesaikan pembayaran.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses order. Silakan coba lagi.');
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private Helper Methods
    // ──────────────────────────────────────────────────────────────────────────

    private function getUserCart(): ?Cart
    {
        return auth()->user()->cart()->with('items.product')->first();
    }

    private function fetchProvinces(): array
    {
        try {
            return $this->rajaOngkir->getProvinces();
        } catch (\Exception $e) {
            Log::error('Failed to fetch provinces: ' . $e->getMessage());
            return [];
        }
    }

    private function calculateTotalWeight($cart): int
    {
        $weight = $cart->items->sum(function ($item) {
            return ($item->product->weight ?? 0) * $item->quantity;
        });
        return max(100, $weight);
    }

    private function jsonError(string $message, int $code): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'cities' => [],
            'costs' => []
        ], $code);
    }

    private function validateCheckout(Request $request): array
    {
        return $request->validate([
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'province' => ['required', 'string'],
            'province_id' => ['required', 'string'],
            'city' => ['required', 'string'],
            'city_id' => ['required', 'string'],
            'postal_code' => ['required', 'string', 'max:10', 'min:5'],
            'address_detail' => ['required', 'string', 'max:500'],
            'courier' => ['required', 'string', 'in:jne,pos,tiki,jnt,sicepat,anteraja'],
            'service' => ['required', 'string'],
            'service_name' => ['nullable', 'string'],
            'shipping_cost' => ['required', 'integer', 'min:0'],
            'estimated_days' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:300'],
        ], $this->validationMessages());
    }

    private function validationMessages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'province.required' => 'Provinsi wajib dipilih.',
            'city.required' => 'Kota wajib dipilih.',
            'postal_code.required' => 'Kode pos wajib diisi.',
            'postal_code.min' => 'Kode pos minimal 5 digit.',
            'address_detail.required' => 'Alamat detail wajib diisi.',
            'courier.required' => 'Kurir wajib dipilih.',
            'courier.in' => 'Kurir tidak valid.',
            'service.required' => 'Layanan pengiriman wajib dipilih.',
            'shipping_cost.required' => 'Pilih layanan pengiriman terlebih dahulu.',
            'shipping_cost.min' => 'Shipping cost tidak valid.',
        ];
    }

    private function validateStock($cart): ?string
    {
        foreach ($cart->items as $item) {
            if ($item->product->status === 'available' && $item->product->stock < $item->quantity) {
                return "Stok {$item->product->name} tidak mencukupi. Sisa {$item->product->stock} pcs.";
            }
        }
        return null;
    }

    private function createOrder($cart, array $validated): Order
    {
        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
        $shippingCost = (int) $validated['shipping_cost'];
        $totalPrice = $subtotal + $shippingCost;

        return Order::create([
            'user_id' => auth()->id(),
            'order_code' => $this->generateUniqueOrderCode(),
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);
    }

    private function createOrderItems(Order $order, $cart): void
    {
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'size' => $item->size,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->price * $item->quantity,
            ]);

            $this->updateProductStock($item);
        }
    }

    private function updateProductStock($item): void
    {
        if ($item->product->status === 'available') {
            $item->product->decrement('stock', $item->quantity);
            
            if ($item->product->fresh()->stock <= 0) {
                $item->product->update(['status' => 'sold_out']);
            }
        }
    }

    private function createShippingAddress(Order $order, array $validated): void
    {
        ShippingAddress::create([
            'order_id' => $order->id,
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'province' => $validated['province'],
            'province_id' => $validated['province_id'],
            'city' => $validated['city'],
            'city_id' => $validated['city_id'],
            'postal_code' => $validated['postal_code'],
            'address_detail' => $validated['address_detail'],
            'courier' => strtoupper($validated['courier']),
            'service' => $validated['service'],
            'service_description' => $validated['service_name'] ?? null,
            'estimated_days' => $validated['estimated_days'] ?? null,
        ]);
    }

    private function createPayment(Order $order, int $amount): void
    {
        Payment::create([
            'order_id' => $order->id,
            'status' => 'pending',
            'amount' => $amount,
            'method' => null,
        ]);
    }

    private function clearCart($cart): void
    {
        $cart->items()->delete();
    }

    private function generateUniqueOrderCode(): string
    {
        $prefix = 'VOID';
        $date = now()->format('ymd');
        
        do {
            $random = strtoupper(substr(uniqid(), -6));
            $code = $prefix . $date . $random;
        } while (Order::where('order_code', $code)->exists());
        
        return $code;
    }
}