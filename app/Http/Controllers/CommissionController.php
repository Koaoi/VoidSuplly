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

    /**
     * Proses pembayaran commission.
     *
     * Skenario yang ditangani:
     * A) Commission belum punya order → buat Order + Payment baru
     * B) Commission sudah punya order tapi pembayaran dibatalkan/expired → buat Order baru lagi
     * C) Commission sudah punya order dan masih pending → langsung redirect ke payment
     * D) Commission sudah paid → tolak, tidak perlu bayar lagi
     */
    public function processPayment(Request $request, Commission $commission)
    {
        // Pastikan hanya pemilik yang bisa akses
        if ($commission->user_id !== auth()->id()) {
            abort(403);
        }

        // Validasi status: hanya 'accepted' yang boleh bayar
        if ($commission->status !== 'accepted') {
            $message = match($commission->status) {
                'pending'     => 'Commission masih menunggu review admin.',
                'reviewing'   => 'Commission sedang direview admin.',
                'paid'        => 'Commission ini sudah dibayar.',
                'completed'   => 'Commission ini sudah selesai.',
                'rejected'    => 'Commission ini telah ditolak.',
                'in_progress' => 'Commission sedang dalam pengerjaan.',
                default       => 'Commission belum bisa dibayar.',
            };
            return redirect()->route('commission.show', $commission)->with('error', $message);
        }

        // Validasi quoted_price
        if (empty($commission->quoted_price) || $commission->quoted_price <= 0) {
            return redirect()->route('commission.show', $commission)
                ->with('error', 'Admin belum menetapkan harga untuk commission ini.');
        }

        // === SKENARIO B & C: Commission sudah punya order ===
        if ($commission->order_id && $commission->order) {
            $existingOrder = $commission->order;

            // Skenario C: Order masih aktif (pending) → lanjut bayar
            if ($existingOrder->status === 'pending') {
                return redirect()->route('payment.show', $existingOrder->order_code)
                    ->with('info', 'Silakan selesaikan pembayaran Anda.');
            }

            // Skenario B: Order sudah cancelled/expired → reset dan buat order baru
            if (in_array($existingOrder->status, ['cancelled'])) {
                // Lepas relasi order lama dari commission dulu
                $commission->update(['order_id' => null]);
                Log::info('Commission order reset (was cancelled)', [
                    'commission_id' => $commission->id,
                    'old_order_code' => $existingOrder->order_code,
                ]);
            }

            // Jika order sudah paid (tidak seharusnya masuk sini, tapi jaga-jaga)
            if ($existingOrder->status === 'paid') {
                return redirect()->route('commission.show', $commission)
                    ->with('error', 'Commission ini sudah dibayar.');
            }
        }

        // === SKENARIO A & B lanjutan: Buat Order + Payment baru ===
        $order = Order::create([
            'user_id'       => auth()->id(),
            'order_code'    => Order::generateCode(),
            'subtotal'      => $commission->quoted_price,
            'shipping_cost' => 0,
            'total_price'   => $commission->quoted_price,
            'status'        => 'pending',
            'notes'         => 'Commission: ' . $commission->title,
        ]);

        // Payment dimulai dari 'unpaid' (sesuai enum payments migration)
            Payment::create([
                'order_id' => $order->id,
                'amount'   => $order->total_price,
                'status'   => 'pending',  // ✅ GANTI JADI PENDING
                'method'   => null,
            ]);
        // Ikat commission ke order baru (status commission tetap 'accepted')
        $commission->update(['order_id' => $order->id]);

        Log::info('Commission order created', [
            'commission_id' => $commission->id,
            'order_code'    => $order->order_code,
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
