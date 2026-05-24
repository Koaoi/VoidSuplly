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

        // Ambil daftar provinsi dari API atau static
        $provinces = $this->getProvinces();

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
     * Ambil daftar provinsi dari API atau fallback ke static
     */
    private function getProvinces(): array
    {
        try {
            $apiKey = config('rajaongkir.api_key', env('RAJAONGKIR_API_KEY', ''));
            
            $response = Http::timeout(10)
                ->withHeaders(['key' => $apiKey])
                ->get('https://rajaongkir.komerce.id/api/v1/destination/province');
            
            $data = $response->json();
            
            if ($response->successful()) {
                if (isset($data['data']) && is_array($data['data'])) {
                    return $data['data'];
                }
                if (isset($data['rajaongkir']['results'])) {
                    return $data['rajaongkir']['results'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch provinces: ' . $e->getMessage());
        }
        
        // Fallback data provinsi statis
        return $this->getStaticProvinces();
    }

    /**
     * Data provinsi statis (fallback)
     */
    private function getStaticProvinces(): array
    {
        return [
            ['id' => 1, 'name' => 'NUSA TENGGARA BARAT (NTB)'],
            ['id' => 2, 'name' => 'MALUKU'],
            ['id' => 3, 'name' => 'KALIMANTAN SELATAN'],
            ['id' => 4, 'name' => 'KALIMANTAN TENGAH'],
            ['id' => 5, 'name' => 'JAWA BARAT'],
            ['id' => 6, 'name' => 'BENGKULU'],
            ['id' => 7, 'name' => 'KALIMANTAN TIMUR'],
            ['id' => 8, 'name' => 'KEPULAUAN RIAU'],
            ['id' => 9, 'name' => 'NANGGROE ACEH DARUSSALAM (NAD)'],
            ['id' => 10, 'name' => 'DKI JAKARTA'],
            ['id' => 11, 'name' => 'BANTEN'],
            ['id' => 12, 'name' => 'JAWA TENGAH'],
            ['id' => 13, 'name' => 'JAMBI'],
            ['id' => 14, 'name' => 'PAPUA'],
            ['id' => 15, 'name' => 'BALI'],
            ['id' => 16, 'name' => 'SUMATERA UTARA'],
            ['id' => 17, 'name' => 'GORONTALO'],
            ['id' => 18, 'name' => 'JAWA TIMUR'],
            ['id' => 19, 'name' => 'DI YOGYAKARTA'],
            ['id' => 20, 'name' => 'SULAWESI TENGGARA'],
            ['id' => 21, 'name' => 'NUSA TENGGARA TIMUR (NTT)'],
            ['id' => 22, 'name' => 'SULAWESI UTARA'],
            ['id' => 23, 'name' => 'SUMATERA BARAT'],
            ['id' => 24, 'name' => 'BANGKA BELITUNG'],
            ['id' => 25, 'name' => 'RIAU'],
            ['id' => 26, 'name' => 'SUMATERA SELATAN'],
            ['id' => 27, 'name' => 'SULAWESI TENGAH'],
            ['id' => 28, 'name' => 'KALIMANTAN BARAT'],
            ['id' => 29, 'name' => 'PAPUA BARAT'],
            ['id' => 30, 'name' => 'LAMPUNG'],
            ['id' => 31, 'name' => 'KALIMANTAN UTARA'],
            ['id' => 32, 'name' => 'MALUKU UTARA'],
            ['id' => 33, 'name' => 'SULAWESI SELATAN'],
            ['id' => 34, 'name' => 'SULAWESI BARAT'],
        ];
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
            'city'                => $data['city_name'],
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
            'status'   => 'unpaid',
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
        'city_name'           => ['nullable', 'string', 'max:100'],  // ← changed to nullable
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