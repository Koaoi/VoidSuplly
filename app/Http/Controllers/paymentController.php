<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
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
    }

    /**
     * Halaman pembayaran
     */
    public function show($code)
    {
        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->with(['items.product', 'shippingAddress', 'payment'])
            ->firstOrFail();

        // Jika order sudah dibayar, redirect ke detail order
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
                        'bca_va',
                        'bni_va',
                        'bri_va',
                        'gopay',
                        'qris',
                        'shopeepay'
                    ],
                ];

                $snapToken = Snap::getSnapToken($transactionDetails);
                
            } catch (\Exception $e) {
                \Log::error('Midtrans Error: ' . $e->getMessage());
            }
        }

        return view('payment.show', compact('order', 'snapToken', 'clientKey'));
    }

    /**
     * Upload bukti pembayaran (manual)
     */
    public function uploadProof(Request $request, $code)
    {
        $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Upload bukti transfer
        $path = $request->file('proof_image')->store('payment_proofs', 'public');

        // Update payment record
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

        // Update status order
        $order->update(['status' => 'pending']);

        return redirect()->route('payment.show', $order->order_code)
            ->with('success', 'Bukti pembayaran berhasil diupload. Mohon tunggu konfirmasi admin.');
    }

    /**
     * Midtrans Callback (Webhook)
     */
    public function midtransCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = explode('-', $request->order_id)[0];
        $order = Order::where('order_code', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $order->update(['status' => 'paid']);
                $this->updatePaymentStatus($order->id, 'paid');
            }
        } else if ($transactionStatus == 'settlement') {
            $order->update(['status' => 'paid']);
            $this->updatePaymentStatus($order->id, 'paid');
        } else if ($transactionStatus == 'pending') {
            $order->update(['status' => 'pending']);
            $this->updatePaymentStatus($order->id, 'pending');
        } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $order->update(['status' => 'cancelled']);
            $this->updatePaymentStatus($order->id, 'failed');
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Update payment status
     */
    private function updatePaymentStatus($orderId, $status)
    {
        $payment = Payment::where('order_id', $orderId)->first();
        if ($payment) {
            $payment->update(['status' => $status]);
        }
    }

    /**
     * Konfirmasi pembayaran manual (Admin)
     */
    public function confirmPayment(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $order->update(['status' => 'paid']);
        
        $payment = Payment::where('order_id', $orderId)->first();
        if ($payment) {
            $payment->update(['status' => 'paid']);
        }
        
        return redirect()->route('admin.orders.index')
            ->with('success', 'Pembayaran dikonfirmasi.');
    }

    /**
     * Tolak pembayaran manual (Admin)
     */
    public function rejectPayment(Request $request, $orderId)
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
}