<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
        
        Log::info('Midtrans Config initialized', [
            'server_key_set' => !empty(Config::$serverKey),
            'client_key_set' => !empty(Config::$clientKey),
            'is_production' => Config::$isProduction,
            'server_key_prefix' => substr(Config::$serverKey ?? '', 0, 15) . '...'
        ]);
    }

    /**
     * Halaman pembayaran
     */
    public function show(string $code)
    {
        Log::info('Payment show called', ['code' => $code]);
        
        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->with(['items.product', 'shippingAddress', 'payment'])
            ->firstOrFail();

        Log::info('Order found', ['order_id' => $order->id, 'status' => $order->status]);

        if (in_array($order->status, ['paid', 'processing', 'shipped', 'completed'])) {
            return redirect()->route('orders.show', $order->order_code)
                ->with('info', 'Pesanan ini sudah dibayar.');
        }

        $snapToken = null;
        $clientKey = config('midtrans.client_key');

        if ($order->status === 'pending') {
            try {
                $transactionDetails = [
                    'transaction_details' => [
                        'order_id' => $order->order_code . '-' . time(),
                        'gross_amount' => (int) $order->total_price,
                    ],
                    'customer_details' => [
                        'first_name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                        'phone' => auth()->user()->phone ?? '',
                    ],
                    'enabled_payments' => [
                        'bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'cimb_va',
                        'gopay', 'qris', 'shopeepay', 'alfamart', 'indomaret'
                    ],
                    'item_details' => $this->getItemDetails($order),
                ];

                $snapToken = Snap::getSnapToken($transactionDetails);
                Log::info('Snap token generated successfully');
                
            } catch (\Exception $e) {
                Log::error('Midtrans Error: ' . $e->getMessage());
            }
        }

        return view('payment.show', compact('order', 'snapToken', 'clientKey'));
    }

    /**
     * Get Snap Token untuk metode pembayaran tertentu
     */
    public function getSnapToken(Request $request, string $code)
    {
        Log::info('getSnapToken called', ['code' => $code]);
        
        try {
            $order = Order::where('order_code', $code)
                ->where('user_id', auth()->id())
                ->firstOrFail();
            
            $paymentMethod = $request->input('payment_method', 'bca_va');
            $paymentType = $request->input('payment_type', 'bank_transfer');
            $enabledPayments = $this->getEnabledPayments($paymentMethod, $paymentType);
            
            $customerDetails = [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ];
            
            if (auth()->user()->phone) {
                $customerDetails['phone'] = auth()->user()->phone;
            }
            
            $shippingAddress = $order->shippingAddress;
            if ($shippingAddress) {
                $customerDetails['billing_address'] = [
                    'first_name' => $shippingAddress->recipient_name,
                    'phone' => $shippingAddress->phone,
                    'address' => $shippingAddress->address_detail,
                    'city' => $shippingAddress->city,
                    'postal_code' => $shippingAddress->postal_code,
                ];
            }
            
            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_code . '-' . time(),
                    'gross_amount' => (int) $order->total_price,
                ],
                'customer_details' => $customerDetails,
                'item_details' => $this->getItemDetails($order),
                'enabled_payments' => $enabledPayments,
            ];
            
            $snapToken = Snap::getSnapToken($params);
            
            return response()->json([
                'success' => true,
                'snap_token' => $snapToken
            ]);
            
        } catch (\Exception $e) {
            Log::error('Midtrans Error in getSnapToken: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload bukti pembayaran (manual)
     */
    public function uploadProof(Request $request, string $code)
    {
        $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $path = $request->file('proof_image')->store('payment_proofs', 'public');

        $payment = Payment::where('order_id', $order->id)->first();
        
        if ($payment) {
            $payment->update([
                'proof_image' => $path,
                'status' => 'pending',
                'method' => 'manual_transfer',
            ]);
        } else {
            Payment::create([
                'order_id' => $order->id,
                'proof_image' => $path,
                'status' => 'pending',
                'amount' => $order->total_price,
                'method' => 'manual_transfer',
            ]);
        }

        $order->update(['status' => 'pending']);

        return redirect()->route('payment.show', $order->order_code)
            ->with('success', 'Bukti pembayaran berhasil diupload. Mohon tunggu konfirmasi admin.');
    }

    /**
     * Midtrans Callback (Webhook)
     */
    public function midtransCallback(Request $request)
    {
        Log::info('=== MIDTRANS CALLBACK RAW ===');
        Log::info('Raw input: ' . $request->getContent());
        Log::info('All input: ', $request->all());
        
        $serverKey = config('midtrans.server_key');
        $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            Log::warning('Midtrans callback: Invalid signature');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Extract order code (format: ORDERCODE-timestamp)
        $orderCode = explode('-', $request->order_id)[0];
        Log::info('Extracted order_code: ' . $orderCode);
        
        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            Log::warning('Midtrans callback: Order not found', ['order_code' => $orderCode]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status;
        $paymentType = $request->payment_type;

        Log::info('Processing Midtrans Callback', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'transaction_status' => $transactionStatus,
            'payment_type' => $paymentType
        ]);

        // Get or create payment record
        $payment = Payment::where('order_id', $order->id)->first();
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'status' => 'pending',
                'method' => $paymentType ?? 'unknown',
            ]);
        }

        // Save payment details
        $paymentDetails = [
            'transaction_id' => $request->transaction_id,
            'order_id' => $request->order_id,
            'gross_amount' => $request->gross_amount,
            'payment_type' => $paymentType,
            'transaction_time' => $request->transaction_time,
            'transaction_status' => $transactionStatus,
            'fraud_status' => $fraudStatus,
            'status_code' => $request->status_code,
        ];

        if ($request->va_numbers) {
            $paymentDetails['va_numbers'] = $request->va_numbers;
        }
        
        if ($request->qr_code_url) {
            $paymentDetails['qr_code_url'] = $request->qr_code_url;
        }

        $payment->update([
            'payment_details' => json_encode($paymentDetails),
            'method' => $paymentType ?? $payment->method,
        ]);

        // Update order status based on transaction status
        if ($transactionStatus == 'capture' && $fraudStatus == 'accept') {
            $order->update(['status' => 'paid']);
            $payment->update(['status' => 'paid', 'paid_at' => now()]);
            Log::info('✅ PAYMENT SUCCESS for order: ' . $order->order_code);
        } 
        elseif ($transactionStatus == 'settlement') {
            $order->update(['status' => 'paid']);
            $payment->update(['status' => 'paid', 'paid_at' => now()]);
            Log::info('✅ PAYMENT SUCCESS (settlement) for order: ' . $order->order_code);
        } 
        elseif ($transactionStatus == 'pending') {
            $order->update(['status' => 'pending']);
            $payment->update(['status' => 'pending']);
            Log::info('⏳ PAYMENT PENDING for order: ' . $order->order_code);
        } 
        elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $order->update(['status' => 'cancelled']);
            $payment->update(['status' => 'failed']);
            Log::info('❌ PAYMENT FAILED for order: ' . $order->order_code);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Manual Callback (dipanggil dari frontend setelah popup sukses)
     */
    public function manualCallback(Request $request, string $code)
    {
        Log::info('Manual callback called: ' . $code);
        Log::info('Request data: ', $request->all());
        
        $order = Order::where('order_code', $code)->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }
        
        // Update order status
        $order->update(['status' => 'paid']);
        
        // Update or create payment record
        $payment = Payment::where('order_id', $order->id)->first();
        
        // Ambil metode pembayaran dari request
        $paymentMethod = $request->input('payment_method') ?? 
                         $request->input('payment_type') ?? 
                         $request->input('method') ?? 
                         'midtrans';
        
        // Map payment method ke format yang lebih readable
        $methodMapping = [
            'bca_va' => 'bca_va',
            'mandiri_va' => 'mandiri_va',
            'bni_va' => 'bni_va',
            'bri_va' => 'bri_va',
            'cimb_va' => 'cimb_va',
            'qris' => 'qris',
            'gopay' => 'gopay',
            'shopeepay' => 'shopeepay',
            'credit_card' => 'credit_card',
            'bank_transfer' => 'bank_transfer',
            'alfamart' => 'alfamart',
            'indomaret' => 'indomaret',
        ];
        
        $finalMethod = $methodMapping[$paymentMethod] ?? $paymentMethod;
        
        // Juga cek dari result Midtrans
        if ($request->has('result')) {
            $result = $request->input('result');
            if (isset($result['payment_type'])) {
                $finalMethod = $methodMapping[$result['payment_type']] ?? $result['payment_type'];
            }
            if (isset($result['payment_method'])) {
                $finalMethod = $methodMapping[$result['payment_method']] ?? $result['payment_method'];
            }
        }
        
        Log::info('Payment method detected: ' . $finalMethod);
        
        if ($payment) {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'method' => $finalMethod,
                'payment_details' => json_encode($request->all())
            ]);
            Log::info('Payment updated with method: ' . $finalMethod);
        } else {
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'status' => 'paid',
                'paid_at' => now(),
                'method' => $finalMethod,
                'payment_details' => json_encode($request->all())
            ]);
            Log::info('Payment created with method: ' . $finalMethod);
        }
        
        return response()->json([
            'success' => true,
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_status' => $payment->status,
            'payment_method' => $payment->method
        ]);
    }

    /**
     * Update payment status
     */
    private function updatePaymentStatus(int $orderId, string $status): void
    {
        $payment = Payment::where('order_id', $orderId)->first();
        if ($payment) {
            $payment->update(['status' => $status]);
            
            if ($status === 'paid') {
                $payment->update(['paid_at' => now()]);
            }
        }
    }

    /**
     * Konfirmasi pembayaran manual (Admin)
     */
    public function confirmPayment(Request $request, int $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $order->update(['status' => 'paid']);
        
        $payment = Payment::where('order_id', $orderId)->first();
        if ($payment) {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
        }
        
        return redirect()->route('admin.orders.index')
            ->with('success', 'Pembayaran dikonfirmasi.');
    }

    /**
     * Tolak pembayaran manual (Admin)
     */
    public function rejectPayment(Request $request, int $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $order->update(['status' => 'cancelled']);
        
        $payment = Payment::where('order_id', $orderId)->first();
        if ($payment) {
            $payment->update(['status' => 'failed']);
        }
        
        return redirect()->route('admin.orders.index')
            ->with('success', 'Pembayaran ditolak.');
    }

    private function getEnabledPayments(string $method, string $type): array
    {
        if ($type === 'bank_transfer') return [$method];
        if ($type === 'qris') return ['qris'];
        if ($type === 'convenience_store') {
            if ($method === 'alfamart') return ['alfamart'];
            if ($method === 'indomaret') return ['indomaret'];
            return ['alfamart', 'indomaret'];
        }
        
        return ['bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'cimb_va', 'gopay', 'qris', 'shopeepay', 'alfamart', 'indomaret'];
    }

    private function getItemDetails(Order $order): array
    {
        $items = [];
        
        foreach ($order->items as $item) {
            $items[] = [
                'id' => (string) $item->product_id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product_name . ' (' . ($item->size ?? 'FREE') . ')', 0, 50),
            ];
        }
        
        if ($order->shipping_cost > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }
        
        return $items;
    }
}