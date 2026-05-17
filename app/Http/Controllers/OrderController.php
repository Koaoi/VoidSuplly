<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Daftar semua order milik user, dengan filter status.
     */
    public function index(Request $request)
    {
        $query = Order::where('user_id', auth()->id())
            ->with([
                'items.product.images',
                'payment',
                'shippingAddress',
            ])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(8)->withQueryString();

        $statusCounts = Order::where('user_id', auth()->id())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Detail satu order milik user.
     */
    public function show(string $code)
    {
        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->with([
                'items.product.images',
                'shippingAddress',
                'payment',
                'user',
                'reviews',
            ])
            ->firstOrFail();

        $reviewedProductIds = $order->reviews
            ->where('user_id', auth()->id())
            ->pluck('product_id')
            ->toArray();

        return view('orders.show', compact('order', 'reviewedProductIds'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Order Shipping & Payment Methods (untuk route yang diperlukan)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Pilih metode pengiriman (halaman)
     */
    public function selectShipping()
    {
        $cart = auth()->user()->cart()->with('items.product')->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }
        
        return view('orders.select-shipping', compact('cart'));
    }

    /**
     * Update ongkir (AJAX)
     */
    public function updateOngkir(Request $request)
    {
        $request->validate([
            'shipping_cost' => 'required|integer|min:0',
            'courier' => 'required|string',
            'service' => 'required|string',
        ]);

        $cart = auth()->user()->cart()->first();
        
        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Cart not found'], 404);
        }

        // Simpan sementara di session atau cart
        session([
            'shipping_cost' => $request->shipping_cost,
            'courier' => $request->courier,
            'shipping_service' => $request->service,
        ]);

        return response()->json([
            'success' => true,
            'shipping_cost' => $request->shipping_cost,
        ]);
    }

    /**
     * Pilih metode pembayaran (halaman)
     */
    public function selectPayment()
    {
        $cart = auth()->user()->cart()->with('items.product')->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        $shippingCost = session('shipping_cost', 0);
        $subtotal = $cart->total;
        $total = $subtotal + $shippingCost;

        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = 'ORDER-' . time() . '-' . auth()->id();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $total,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone ?? '',
            ],
            'enabled_payments' => [
                'bca_va', 'bni_va', 'bri_va', 'permata_va',
                'gopay', 'qris', 'shopeepay',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            session(['snap_token' => $snapToken, 'midtrans_order_id' => $orderId]);
            
            return view('orders.select-payment', compact('cart', 'shippingCost', 'total', 'snapToken'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Order complete (setelah payment sukses)
     */
    public function complete()
    {
        return redirect()->route('orders.index')->with('success', 'Pembayaran berhasil! Pesanan Anda sedang diproses.');
    }

    /**
     * Riwayat order
     */
    public function orderHistory()
    {
        return redirect()->route('orders.index');
    }

    /**
     * Invoice frontend
     */
    public function invoiceFrontend($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['items.product', 'shippingAddress', 'payment'])
            ->firstOrFail();

        return view('orders.invoice', compact('order'));
    }
}