<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['midtransCallback']);

        Config::$serverKey    = config('midtrans.server_key');
        Config::$clientKey    = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        Log::info('Midtrans Config initialized', [
            'server_key_set'    => !empty(Config::$serverKey),
            'client_key_set'    => !empty(Config::$clientKey),
            'is_production'     => Config::$isProduction,
            'server_key_prefix' => substr(Config::$serverKey ?? '', 0, 15) . '...',
        ]);
    }

    /**
     * Halaman pembayaran — digunakan bersama untuk checkout biasa & commission.
     * Perbedaan: commission order tidak punya items, tapi $isCommission = true
     * sehingga view tetap menampilkan form pembayaran.
     */
    public function show(string $code)
    {
        Log::info('Payment show called', ['code' => $code]);

        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->with(['items.product', 'shippingAddress', 'payment'])
            ->firstOrFail();

        Log::info('Order found', ['order_id' => $order->id, 'status' => $order->status]);

        // Redirect jika sudah dibayar/diproses
        if (in_array($order->status, ['paid', 'processing', 'shipped', 'completed'])) {
            return redirect()->route('orders.show', $order->order_code)
                ->with('info', 'Pesanan ini sudah dibayar.');
        }

        // Deteksi apakah ini order dari commission
        $isCommission = $this->isCommissionOrder($order);

        $snapToken = null;
        $clientKey = config('midtrans.client_key');

        if ($order->status === 'pending') {
            try {
                $params = [
                    'transaction_details' => [
                        'order_id'     => $order->order_code . '-' . time(),
                        'gross_amount' => (int) $order->total_price,
                    ],
                    'customer_details' => [
                        'first_name' => auth()->user()->name,
                        'email'      => auth()->user()->email,
                        'phone'      => auth()->user()->phone ?? '',
                    ],
                    'enabled_payments' => [
                        'bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'cimb_va',
                        'alfamart', 'indomaret',
                    ],
                    'item_details' => $this->getItemDetails($order),
                ];

                $snapToken = Snap::getSnapToken($params);
                Log::info('Snap token generated successfully');
            } catch (\Exception $e) {
                Log::error('Midtrans Error: ' . $e->getMessage());
            }
        }

        return view('payment.show', compact('order', 'snapToken', 'clientKey', 'isCommission'));
    }

    /**
     * Get Snap Token sesuai metode pembayaran yang dipilih user.
     */
    public function getSnapToken(Request $request, string $code)
    {
        Log::info('getSnapToken called', ['code' => $code]);

        try {
            $order = Order::where('order_code', $code)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $paymentMethod   = $request->input('payment_method', 'bca_va');
            $paymentType     = $request->input('payment_type', 'bank_transfer');
            $enabledPayments = $this->getEnabledPayments($paymentMethod, $paymentType);

            $customerDetails = [
                'first_name' => auth()->user()->name,
                'email'      => auth()->user()->email,
            ];

            if (auth()->user()->phone) {
                $customerDetails['phone'] = auth()->user()->phone;
            }

            // Shipping address hanya untuk order produk biasa
            $shippingAddress = $order->shippingAddress;
            if ($shippingAddress) {
                $customerDetails['billing_address'] = [
                    'first_name'  => $shippingAddress->recipient_name,
                    'phone'       => $shippingAddress->phone,
                    'address'     => $shippingAddress->address_detail,
                    'city'        => $shippingAddress->city,
                    'postal_code' => $shippingAddress->postal_code,
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id'     => $order->order_code . '-' . time(),
                    'gross_amount' => (int) $order->total_price,
                ],
                'customer_details' => $customerDetails,
                'item_details'     => $this->getItemDetails($order),
                'enabled_payments' => $enabledPayments,
            ];

            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Error in getSnapToken: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload bukti pembayaran manual.
     */
    public function uploadProof(Request $request, string $code)
    {
        $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $path    = $request->file('proof_image')->store('payment_proofs', 'public');
        $payment = Payment::where('order_id', $order->id)->first();

        if ($payment) {
            $payment->update([
                'proof_image' => $path,
                'status'      => 'pending',
                'method'      => 'manual_transfer',
            ]);
        } else {
            Payment::create([
                'order_id'    => $order->id,
                'proof_image' => $path,
                'status'      => 'pending',
                'amount'      => $order->total_price,
                'method'      => 'manual_transfer',
            ]);
        }

        $order->update(['status' => 'pending']);

        return redirect()->route('payment.show', $order->order_code)
            ->with('success', 'Bukti pembayaran berhasil diupload. Mohon tunggu konfirmasi admin.');
    }

    /**
     * Midtrans Webhook Callback.
     *
     * FIX BUG: order_id dari Midtrans formatnya "VOID-YYYYMMDD-XXXX-{timestamp}"
     * karena kita append -time() saat generate snap token.
     * Solusi: ambil 4 bagian pertama (VOID-YYYYMMDD-XXXX) sebagai order_code.
     */
    public function midtransCallback(Request $request)
    {
        Log::info('=== MIDTRANS CALLBACK ===');
        Log::info('Raw input: ' . $request->getContent());

        // Verifikasi signature
        $serverKey = config('midtrans.server_key');
        $hashed    = hash('sha512',
            $request->order_id . $request->status_code . $request->gross_amount . $serverKey
        );

        if ($hashed !== $request->signature_key) {
            Log::warning('Midtrans callback: Invalid signature');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // FIX: order_id Midtrans = "VOID-20241201-AB12-1733000000"
        // Order code kita = "VOID-20241201-AB12" (3 bagian pertama setelah split by '-')
        // Tapi karena format VOID-YYYYMMDD-XXXX, ada 3 segmen:
        // parts[0]=VOID, parts[1]=20241201, parts[2]=AB12, parts[3]=timestamp
        // Jadi ambil index 0,1,2 dan gabung kembali dengan '-'
        $parts     = explode('-', $request->order_id);
        $orderCode = implode('-', array_slice($parts, 0, 3)); // "VOID-YYYYMMDD-XXXX"

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            Log::warning('Midtrans callback: Order not found', [
                'midtrans_order_id' => $request->order_id,
                'parsed_order_code' => $orderCode,
            ]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $request->transaction_status;
        $fraudStatus       = $request->fraud_status;
        $paymentType       = $request->payment_type;
        $transactionId     = $request->transaction_id;

        // Ambil atau buat payment record
        $payment = Payment::where('order_id', $order->id)->first();
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount'   => $order->total_price,
                'status'   => 'pending',
                'method'   => $paymentType ?? 'midtrans',
            ]);
        }

        // Update detail transaksi Midtrans
        $payment->update([
            'midtrans_payment_type'   => $paymentType ?? $payment->midtrans_payment_type,
            'midtrans_transaction_id' => $transactionId ?? $payment->midtrans_transaction_id,
            'method'                  => $paymentType ?? $payment->method,
        ]);

        // Update status berdasarkan notifikasi Midtrans
        if (($transactionStatus === 'capture' && $fraudStatus === 'accept')
            || $transactionStatus === 'settlement') {

            $order->update(['status' => 'paid']);
            $payment->update(['status' => 'paid', 'paid_at' => now()]);

            // Update commission jika order ini berasal dari commission
            $this->updateCommissionIfExists($order->id, 'paid');

            Log::info('✅ PAYMENT SUCCESS: ' . $order->order_code);

        } elseif ($transactionStatus === 'pending') {

            $order->update(['status' => 'pending']);
            $payment->update(['status' => 'pending']);
            Log::info('⏳ PAYMENT PENDING: ' . $order->order_code);

        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {

            $order->update(['status' => 'cancelled']);
            $payment->update(['status' => 'failed']);
            Log::info('❌ PAYMENT FAILED: ' . $order->order_code);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Manual Callback — dipanggil dari JS frontend setelah popup Midtrans.
     * Berfungsi sebagai fallback jika webhook Midtrans terlambat.
     */
    public function manualCallback(Request $request, string $code)
    {
        Log::info('Manual callback called: ' . $code);

        $order = Order::where('order_code', $code)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $order->update(['status' => 'paid']);

        $payment        = Payment::where('order_id', $order->id)->first();
        $paymentMethod  = $request->input('payment_method') ?? $request->input('payment_type') ?? 'midtrans';
        $midtransTxId   = $request->input('transaction_id') ?? null;

        if ($request->has('result')) {
            $result        = $request->input('result');
            $paymentMethod = $result['payment_type'] ?? $result['payment_method'] ?? $paymentMethod;
            $midtransTxId  = $result['transaction_id'] ?? $midtransTxId;
        }

        $paymentData = [
            'status'                  => 'paid',
            'paid_at'                 => now(),
            'method'                  => $paymentMethod,
            'midtrans_payment_type'   => $paymentMethod,
            'midtrans_transaction_id' => $midtransTxId,
        ];

        if ($payment) {
            $payment->update($paymentData);
        } else {
            Payment::create(array_merge($paymentData, [
                'order_id' => $order->id,
                'amount'   => $order->total_price,
            ]));
        }

        // Update commission jika ada
        $this->updateCommissionIfExists($order->id, 'paid');

        return response()->json([
            'success'        => true,
            'order_code'     => $order->order_code,
            'status'         => $order->status,
            'payment_method' => $paymentMethod,
        ]);
    }

    public function confirmPayment(Request $request, int $orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => 'paid']);

        $payment = Payment::where('order_id', $orderId)->first();
        if ($payment) {
            $payment->update([
                'status'  => 'paid',
                'paid_at' => now(),
                'method'  => $payment->method ?? 'manual_transfer',
            ]);
        }

        $this->updateCommissionIfExists($orderId, 'paid');

        return redirect()->route('admin.orders.index')->with('success', 'Pembayaran dikonfirmasi.');
    }

    public function rejectPayment(Request $request, int $orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => 'cancelled']);

        $payment = Payment::where('order_id', $orderId)->first();
        if ($payment) {
            $payment->update(['status' => 'failed']);
        }

        return redirect()->route('admin.orders.index')->with('success', 'Pembayaran ditolak.');
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    /**
     * Update commission status jika order ini berasal dari commission.
     * Dipanggil dari semua titik konfirmasi pembayaran (webhook, manual, admin).
     */
    private function updateCommissionIfExists(int $orderId, string $status): void
    {
        $commission = Commission::where('order_id', $orderId)->first();
        if ($commission) {
            $commission->update(['status' => $status]);
            Log::info("Commission #{$commission->id} updated to '{$status}'");
        }
    }

    /**
     * Cek apakah order ini berasal dari commission.
     */
    private function isCommissionOrder(Order $order): bool
    {
        // Cara 1: cek notes
        if ($order->notes && str_contains($order->notes, 'Commission:')) {
            return true;
        }
        // Cara 2: cek relasi (lebih akurat)
        return Commission::where('order_id', $order->id)->exists();
    }

    private function getEnabledPayments(string $method, string $type): array
    {
        if ($type === 'bank_transfer') return [$method];
        if ($type === 'qris')         return ['qris'];
        if ($type === 'convenience_store') {
            return match($method) {
                'alfamart'  => ['alfamart'],
                'indomaret' => ['indomaret'],
                default     => ['alfamart', 'indomaret'],
            };
        }
        return ['bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'cimb_va', 'alfamart', 'indomaret'];
    }

    /**
     * Build item_details untuk Midtrans.
     * Handle dua jenis order: produk biasa dan commission (tanpa items).
     */
    private function getItemDetails(Order $order): array
    {
        $items = [];

        // Order produk biasa: punya items
        foreach ($order->items as $item) {
            $items[] = [
                'id'       => (string) $item->product_id,
                'price'    => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name'     => substr($item->product_name . ' (' . ($item->size ?? 'FREE') . ')', 0, 50),
            ];
        }

        // Order commission: tidak punya items, pakai notes sebagai nama item
        if (empty($items) && $order->notes && str_contains($order->notes, 'Commission:')) {
            $items[] = [
                'id'       => 'COMM-' . $order->id,
                'price'    => (int) $order->total_price,
                'quantity' => 1,
                'name'     => substr($order->notes, 0, 50),
            ];
        }

        // Shipping cost (hanya untuk order produk biasa)
        if ($order->shipping_cost > 0) {
            $items[] = [
                'id'       => 'SHIPPING',
                'price'    => (int) $order->shipping_cost,
                'quantity' => 1,
                'name'     => 'Ongkos Kirim',
            ];
        }

        return $items;
    }
}
