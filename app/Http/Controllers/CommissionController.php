<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $commissions = Commission::where('user_id', auth()->id())
            ->with('order')
            ->latest()
            ->paginate(10);

        return view('commission.index', compact('commissions'));
    }

    public function create()
    {
        $productTypes = [
            'hoodie'  => 'Hoodie',
            'tshirt'  => 'T-Shirt',
            'jersey'  => 'Jersey',
            'jacket'  => 'Jacket',
            'pants'   => 'Pants',
            'totebag' => 'Tote Bag',
            'other'   => 'Lainnya',
        ];

        return view('commission.create', compact('productTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:150'],
            'product_type'    => ['required', 'string', 'in:hoodie,tshirt,jersey,jacket,pants,totebag,other'],
            'description'     => ['required', 'string', 'min:30', 'max:2000'],
            'quantity'        => ['required', 'integer', 'min:1', 'max:100'],
            'budget'          => ['nullable', 'numeric', 'min:0'],
            'reference_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $imagePath = null;
        if ($request->hasFile('reference_image')) {
            $imagePath = $request->file('reference_image')
                ->store('commissions/references', 'public');
        }

        Commission::create([
            'user_id'         => auth()->id(),
            'title'           => $validated['title'],
            'product_type'    => $validated['product_type'],
            'description'     => $validated['description'],
            'quantity'        => $validated['quantity'],
            'budget'          => $validated['budget'] ?? null,
            'reference_image' => $imagePath,
            'status'          => 'pending',
        ]);

        return redirect()->route('commission.index')
            ->with('success', 'Commission request berhasil dikirim! Tim VOID Supply akan segera menghubungimu.');
    }

    public function show(Commission $commission)
    {
        if ($commission->user_id !== auth()->id()) {
            abort(403);
        }

        return view('commission.show', compact('commission'));
    }

    public function processPayment(Request $request, Commission $commission)
    {
        if ($commission->user_id !== auth()->id()) {
            abort(403);
        }

        if ($commission->status !== 'accepted' || !$commission->quoted_price) {
            return redirect()->route('commission.show', $commission)
                ->with('error', 'Commission belum bisa dibayar.');
        }

        if ($commission->order_id) {
            return redirect()->route('payment.show', $commission->order->order_code)
                ->with('info', 'Silakan lanjutkan pembayaran.');
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'order_code' => Order::generateCode(),
            'subtotal' => $commission->quoted_price,
            'shipping_cost' => 0,
            'total_price' => $commission->quoted_price,
            'status' => 'pending',
            'notes' => 'Commission: ' . $commission->title,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_price,
            'status' => 'pending',
            'method' => null,
        ]);

        $commission->update([
            'order_id' => $order->id,
        ]);

        Log::info('Commission order created', [
            'commission_id' => $commission->id,
            'order_code' => $order->order_code
        ]);

        return redirect()->route('payment.show', $order->order_code)
            ->with('success', 'Silakan selesaikan pembayaran commission Anda.');
    }

    public function destroy(Commission $commission)
    {
        if ($commission->user_id !== auth()->id()) {
            abort(403);
        }

        if ($commission->status !== 'pending') {
            return back()->with('error', 'Commission yang sudah diproses tidak dapat dibatalkan.');
        }

        if ($commission->reference_image) {
            Storage::disk('public')->delete($commission->reference_image);
        }

        $commission->delete();

        return redirect()->route('commission.index')
            ->with('success', 'Commission berhasil dibatalkan.');
    }
}