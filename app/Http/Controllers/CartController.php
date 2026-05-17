<?php
// app/Http/Controllers/CartController.php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─── Tampilkan cart ───────────────────────────────────────────────────────

    public function index()
    {
        $cart = auth()->user()->cart()->with([
            'items.product.images',
            'items.product.category',
        ])->first();
        
        // Jika request AJAX, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'total' => 0,
                    'empty' => true,
                ]);
            }
            
            $items = $cart->items->map(function($item) {
                // Pastikan price tidak 0
                $price = $item->price > 0 ? $item->price : ($item->product->price ?? 0);
                
                return [
                    'id' => $item->id,
                    'quantity' => (int) $item->quantity,
                    'price' => (int) $price,
                    'size' => $item->size,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'price' => (int) ($item->product->price ?? 0),
                        'primary_image_url' => $item->product->primary_image_url,
                    ],
                ];
            });
            
            $total = $cart->items->sum(function($item) {
                $price = $item->price > 0 ? $item->price : ($item->product->price ?? 0);
                return $price * $item->quantity;
            });
            
            return response()->json([
                'success' => true,
                'items' => $items,
                'total' => $total,
                'empty' => false,
            ]);
        }
        
        return view('cart.index', compact('cart'));
    }

    // ─── Tambah item ke cart (AJAX) ───────────────────────────────────────────

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'size'       => ['nullable', 'string', 'max:10'],
            'quantity'   => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Validasi status produk
        if (!in_array($product->status, ['available', 'preorder'])) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak dapat ditambahkan ke keranjang.',
            ], 422);
        }

        // Validasi stok
        if ($product->status === 'available' && $product->stock < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "Stok tidak mencukupi. Tersisa {$product->stock} pcs.",
            ], 422);
        }

        // Ambil atau buat cart user
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        // Cek apakah item + size sudah ada di cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('size', $validated['size'] ?? null)
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQty = $cartItem->quantity + $validated['quantity'];

            if ($product->status === 'available' && $newQty > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Maksimal {$product->stock} pcs untuk produk ini.",
                ], 422);
            }

            $cartItem->update([
                'quantity' => $newQty,
                'price' => $product->price
            ]);
        } else {
            // Buat item baru
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'size'       => $validated['size'] ?? null,
                'quantity'   => $validated['quantity'],
                'price'      => $product->price,
            ]);
        }

        // Hitung total item di cart untuk badge navbar
        $totalItems = $cart->items()->sum('quantity');

        Log::info('Add to cart success:', [
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'size' => $validated['size'] ?? null,
            'quantity' => $validated['quantity'],
            'total_items' => $totalItems
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$product->name} ditambahkan ke keranjang.",
            'count'   => $totalItems,
        ]);
    }

    // ─── Update quantity item (AJAX) ──────────────────────────────────────────

    public function update(Request $request, CartItem $cartItem)
    {
        // Pastikan cart item milik user ini
        if ($cartItem->cart->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $product = $cartItem->product;

        if ($product->status === 'available' && $validated['quantity'] > $product->stock) {
            return response()->json([
                'success' => false,
                'message' => "Stok tidak mencukupi. Tersisa {$product->stock} pcs.",
            ], 422);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        $cart = $cartItem->cart->load('items.product');
        $subtotal = $cartItem->price * $cartItem->quantity;
        $cartTotal = $cart->items->sum(function($item) {
            return $item->price * $item->quantity;
        });
        $totalItems = $cart->items->sum('quantity');

        return response()->json([
            'success'    => true,
            'subtotal'   => number_format($subtotal, 0, ',', '.'),
            'cart_total' => number_format($cartTotal, 0, ',', '.'),
            'count'      => $totalItems,
        ]);
    }

    // ─── Hapus item dari cart (AJAX) ──────────────────────────────────────────

    public function remove(CartItem $cartItem)
    {
        // Pastikan cart item milik user ini
        if ($cartItem->cart->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $cart = $cartItem->cart;
        $cartItem->delete();

        $cart->load('items.product');
        $totalItems = $cart->items->sum('quantity');
        $cartTotal = $cart->items->sum(function($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success'    => true,
            'cart_total' => number_format($cartTotal, 0, ',', '.'),
            'count'      => $totalItems,
            'empty'      => $cart->items->isEmpty(),
        ]);
    }
    
    // ─── Get cart count for navbar (AJAX) ─────────────────────────────────────
    
    public function getCount()
    {
        $cart = auth()->user()->cart;
        $count = $cart ? $cart->items()->sum('quantity') : 0;
        
        return response()->json([
            'count' => $count,
        ]);
    }
    
    // ─── Clear all cart items ─────────────────────────────────────────────────
    
    public function clear()
    {
        $cart = auth()->user()->cart;
        
        if ($cart) {
            foreach ($cart->items as $item) {
                $item->delete();
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan.',
        ]);
    }
    
    // ─── Get cart details ─────────────────────────────────────────────────────
    
    public function details()
    {
        $cart = auth()->user()->cart()->with('items.product')->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => true,
                'items' => [],
                'total' => 0,
                'count' => 0,
            ]);
        }
        
        $items = $cart->items->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->product->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'size' => $item->size,
                'subtotal' => $item->price * $item->quantity,
                'image' => $item->product->primary_image_url,
                'url' => route('products.show', $item->product->slug),
            ];
        });
        
        return response()->json([
            'success' => true,
            'items' => $items,
            'total' => $cart->items->sum(function($item) {
                return $item->price * $item->quantity;
            }),
            'count' => $cart->items->sum('quantity'),
        ]);
    }
}