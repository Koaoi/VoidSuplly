<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingAddress;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(private readonly RajaOngkirService $rajaOngkir)
    {
        $this->middleware('auth');
    }

    // ─── Tampilkan halaman checkout ───────────────────────────────────────────

    public function index()
    {
        $cart = $this->getValidatedCart();

        if (is_string($cart)) {
            return redirect()->route('cart.index')->with('warning', $cart);
        }

        // Hitung total berat cart (gram)
        $cartWeight = $cart->items->sum(function($item) {
            return ($item->product->weight ?? 300) * $item->quantity;
        });
        $cartWeight = max(1000, $cartWeight);

        // Ambil daftar provinsi dari API (LIVE, tanpa dummy)
        $provinces = $this->getProvincesFromApi();

        $couriers = RajaOngkirService::availableCouriers();

        return view('checkout.index', compact('cart', 'provinces', 'couriers', 'cartWeight'));
    }

    // ─── Proses checkout → buat Order ─────────────────────────────────────────

    public function process(Request $request)
    {
        $validated = $this->validateCheckoutRequest($request);

        $cart = $this->getValidatedCart();
        if (is_string($cart)) {
            return redirect()->route('cart.index')->with('warning', $cart);
        }

        // Double-check stok sebelum transaksi dimulai
        $stockError = $this->checkStock($cart);
        if ($stockError) {
            return back()->with('error', $stockError);
        }

        DB::beginTransaction();
        try {
            $order = $this->createOrder($cart, $validated);
            $this->createOrderItems($order, $cart);
            $this->createShippingAddress($order, $validated);
            $this->createPaymentRecord($order);
            
            // Hapus cart setelah order berhasil
            $cart->items()->delete();
            $cart->delete();
            
            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checkout process error', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
        }

        return redirect()
            ->route('payment.show', $order->order_code)
            ->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');
    }

    // ─── Private: Helpers ─────────────────────────────────────────────────────

    /**
     * Mengembalikan Cart jika valid, atau string pesan error.
     */
    private function getValidatedCart(): Cart|string
    {
        $cart = Cart::where('user_id', auth()->id())
            ->with(['items.product.images', 'items.product.category'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return 'Keranjangmu kosong. Silakan tambahkan produk terlebih dahulu.';
        }

        $hasUnavailable = $cart->items->contains(
            fn($item) => !in_array($item->product->status, ['available', 'preorder'])
        );

        if ($hasUnavailable) {
            return 'Ada produk tidak tersedia di keranjangmu. Silakan hapus terlebih dahulu.';
        }

        return $cart;
    }

    /**
     * Ambil daftar provinsi langsung dari API RajaOngkir (LIVE, tanpa dummy)
     */
    private function getProvincesFromApi(): array
    {
        try {
            $apiKey = config('rajaongkir.api_key', env('RAJAONGKIR_API_KEY', ''));
            
            if (empty($apiKey)) {
                Log::error('RajaOngkir API Key is missing in .env file');
                return [];
            }
            
            $response = Http::timeout(15)
                ->withHeaders(['key' => $apiKey])
                ->get('https://rajaongkir.komerce.id/api/v1/destination/province');
            
            if (!$response->successful()) {
                Log::error('RajaOngkir API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }
            
            $data = $response->json();
            
            if (isset($data['data']) && is_array($data['data'])) {
                return $data['data'];
            }
            
            if (isset($data['rajaongkir']['results']) && is_array($data['rajaongkir']['results'])) {
                return $data['rajaongkir']['results'];
            }
            
            Log::warning('Unexpected API response format', ['data' => $data]);
            return [];
            
        } catch (\Exception $e) {
            Log::error('Exception when fetching provinces from API: ' . $e->getMessage());
            return [];
        }
    }

    private function checkStock(Cart $cart): ?string
    {
        foreach ($cart->items as $item) {
            if ($item->product->status === 'available' && $item->product->stock < $item->quantity) {
                return "Stok {$item->product->name} tidak mencukupi. Tersisa {$item->product->stock} pcs.";
            }
        }
        return null;
    }

    private function createOrder(Cart $cart, array $data): Order
    {
        return Order::create([
            'user_id'       => auth()->id(),
            'order_code'    => Order::generateCode(),
            'subtotal'      => $cart->total,
            'shipping_cost' => $data['shipping_cost'],
            'total_price'   => $cart->total + $data['shipping_cost'],
            'status'        => 'pending',
            'notes'         => $data['notes'] ?? null,
        ]);
    }

    private function createOrderItems(Order $order, Cart $cart): void
    {
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $item->product_id,
                'product_name' => $item->product->name,
                'size'         => $item->size,
                'quantity'     => $item->quantity,
                'price'        => $item->product->price,
                'subtotal'     => $item->product->price * $item->quantity,
            ]);

            if ($item->product->status === 'available') {
                $item->product->decrement('stock', $item->quantity);
                if ($item->product->fresh()->stock <= 0) {
                    $item->product->update(['status' => 'sold_out']);
                }
            }
        }
    }

    private function createShippingAddress(Order $order, array $data): void
    {
        ShippingAddress::create([
            'order_id'            => $order->id,
            'recipient_name'      => $data['recipient_name'],
            'phone'               => $data['phone'],
            'province'            => $data['province_name'],
            'province_id'         => (string) $data['province_id'],
            'city'                => $data['city_name'] ?? '',
            'city_id'             => (string) ($data['city_id'] ?? ''),
            'district'            => $data['subdistrict_name'] ?? $data['district_name'] ?? null,
            'postal_code'         => $data['postal_code'],
            'address_detail'      => $data['address_detail'],
            'courier'             => strtoupper($data['courier']),
            'service'             => strtoupper($data['service']),
            'service_description' => $data['service_description'] ?? null,
            'estimated_days'      => isset($data['estimated_days']) ? (int) $data['estimated_days'] : null,
        ]);
    }

    private function createPaymentRecord(Order $order): void
    {
        Payment::create([
            'order_id' => $order->id,
            'status'   => 'pending',  // ← DIPERBAIKI: 'unpaid' jadi 'pending'
            'amount'   => $order->total_price,
        ]);
    }

    private function validateCheckoutRequest(Request $request): array
    {
        $rules = [
            'recipient_name'      => ['required', 'string', 'max:100'],
            'phone'               => ['required', 'string', 'max:20'],
            'province_id'         => ['required'],
            'province_name'       => ['required', 'string', 'max:100'],
            'city_name'           => ['nullable', 'string', 'max:100'],
            'postal_code'         => ['required', 'string', 'max:10'],
            'address_detail'      => ['required', 'string', 'max:500'],
            'courier'             => ['required', 'string'],
            'service'             => ['required', 'string'],
            'shipping_cost'       => ['required', 'integer', 'min:0'],
        ];
        
        // Optional fields
        $optionalFields = ['city_id', 'district_name', 'subdistrict_id', 'subdistrict_name', 'service_description', 'estimated_days', 'notes'];
        foreach ($optionalFields as $field) {
            if ($request->has($field)) {
                $rules[$field] = ['nullable', 'string', 'max:200'];
            }
        }

        $messages = [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'phone.required'          => 'Nomor telepon wajib diisi.',
            'province_id.required'    => 'Provinsi wajib dipilih.',
            'postal_code.required'    => 'Kode pos wajib diisi.',
            'address_detail.required' => 'Alamat lengkap wajib diisi.',
            'courier.required'        => 'Kurir wajib dipilih.',
            'service.required'        => 'Layanan pengiriman wajib dipilih.',
            'shipping_cost.required'  => 'Ongkos kirim belum dihitung.',
        ];

        return $request->validate($rules, $messages);
    }
}