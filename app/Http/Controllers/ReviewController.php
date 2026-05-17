<?php
// app/Http/Controllers/ReviewController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─── Simpan review baru ───────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'  => ['required', 'exists:products,id'],
            'order_id'    => ['required', 'exists:orders,id'],
            'rating'      => ['required', 'integer', 'min:1', 'max:5'],
            'comment'     => ['nullable', 'string', 'max:1000'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'rating.required' => 'Rating bintang wajib dipilih.',
            'rating.min'      => 'Rating minimal 1 bintang.',
            'rating.max'      => 'Rating maksimal 5 bintang.',
        ]);

        // Pastikan order milik user + sudah completed
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->firstOrFail();

        // Pastikan produk ada di order ini
        $itemExists = $order->items()
            ->where('product_id', $validated['product_id'])
            ->exists();

        if (!$itemExists) {
            return back()->with('error', 'Produk ini tidak ada dalam pesananmu.');
        }

        // Cek duplikat review
        $alreadyReviewed = Review::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->where('order_id', $order->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'Kamu sudah mengulas produk ini.');
        }

        // Upload foto review jika ada
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('reviews', 'public');
        }

        Review::create([
            'user_id'     => auth()->id(),
            'product_id'  => $validated['product_id'],
            'order_id'    => $order->id,
            'rating'      => $validated['rating'],
            'comment'     => $validated['comment'] ?? null,
            'image'       => $imagePath,
            'is_approved' => true,
        ]);

        return back()->with('success', 'Ulasan berhasil dikirim. Terima kasih!');
    }

    // ─── Hapus review milik user ──────────────────────────────────────────────

    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        if ($review->image) {
            Storage::disk('public')->delete($review->image);
        }

        $review->delete();

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }
}