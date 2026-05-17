<?php
// app/Http/Controllers/Admin/OrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user','items','payment','shippingAddress'])->latest();

        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qb) use ($q) {
                $qb->where('order_code','like',"%$q%")
                   ->orWhereHas('user', fn($u) => $u->where('name','like',"%$q%")
                                                     ->orWhere('email','like',"%$q%"));
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $statusCounts = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total','status')->toArray();

        return view('admin.orders.index', compact('orders','statusCounts'));
    }

    public function show(Order $order)
    {
        $order->load(['user','items.product.images','shippingAddress','payment']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required','in:pending,paid,processing,shipped,completed,cancelled'],
        ]);

        $old = $order->status;
        $order->update(['status' => $request->status]);

        // Jika dikonfirmasi paid secara manual, update payment juga
        if ($request->status === 'paid' && $order->payment) {
            $order->payment->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);
        }

        return back()->with('success',
            "Status order {$order->order_code} diubah dari {$old} → {$request->status}.");
    }

    public function confirmPayment(Order $order)
    {
        if ($order->status !== 'paid' && $order->status !== 'pending') {
            return back()->with('error','Hanya order pending/paid yang bisa dikonfirmasi.');
        }

        $order->update(['status' => 'processing']);
        if ($order->payment) {
            $order->payment->update(['status' => 'paid', 'paid_at' => now()]);
        }

        return back()->with('success',"Pembayaran order {$order->order_code} dikonfirmasi. Status → Processing.");
    }
}