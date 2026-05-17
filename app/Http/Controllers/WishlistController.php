<?php
// app/Http/Controllers/WishlistController.php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─── Halaman wishlist ─────────────────────────────────────────────────────

    public function index()
    {
        $wishlists = auth()->user()
            ->wishlists()
            ->with(['product.images', 'product.category'])
            ->latest()
            ->paginate(12);

        return view('wishlist.index', compact('wishlists'));
    }

    // ─── Toggle wishlist (AJAX) ───────────────────────────────────────────────

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $user      = auth()->user();
        $productId = (int) $validated['product_id'];

        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
            $message    = 'Produk dihapus dari wishlist.';
        } else {
            Wishlist::create([
                'user_id'    => $user->id,
                'product_id' => $productId,
            ]);
            $inWishlist = true;
            $message    = 'Produk ditambahkan ke wishlist.';
        }

        $wishlistCount = $user->wishlists()->count();

        return response()->json([
            'success'        => true,
            'in_wishlist'    => $inWishlist,
            'message'        => $message,
            'wishlist_count' => $wishlistCount,
        ]);
    }

    // ─── Cek status wishlist (AJAX) ───────────────────────────────────────────

    public function check(int $productId)
    {
        $inWishlist = auth()->user()
            ->wishlists()
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'in_wishlist' => $inWishlist
        ]);
    }

    // ─── Hapus dari wishlist (redirect) ───────────────────────────────────────

    public function destroy(int $id)
    {
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $wishlist->delete();

        return redirect()->route('wishlist.index')
            ->with('success', 'Produk dihapus dari wishlist.');
    }
}