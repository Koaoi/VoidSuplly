<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    /**
     * Product listing dengan search, filter, sort, dan pagination.
     */
    public function index(Request $request)
    {
        // Optimasi query dengan withCount dan withAvg
        $query = Product::with(['images', 'category'])
            ->withCount(['reviews' => function ($q) {
                $q->where('is_approved', true);
            }])
            ->withAvg('reviews', 'rating')
            ->whereNull('deleted_at');

        // ── Search ────────────────────────────────────────────────────────────
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // ── Filter: Kategori ──────────────────────────────────────────────────
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // ── Filter: Status ────────────────────────────────────────────────────
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // ── Filter: Harga ──────────────────────────────────────────────────────
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->max_price);
        }

        // ── Filter: Ukuran ────────────────────────────────────────────────────
        if ($request->filled('size')) {
            $query->whereJsonContains('sizes', $request->size);
        }

        // ── Filter: Limited only ───────────────────────────────────────────────
        if ($request->boolean('limited')) {
            $query->where('is_limited', true);
        }

        // ── Sort ──────────────────────────────────────────────────────────────
        $sort = $request->get('sort', 'latest');

        switch ($sort) {
            case 'popular':
                $query->withCount('orderItems')->orderBy('order_items_count', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'rating':
                $query->orderBy('reviews_avg_rating', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12);
        $products->appends($request->query()); // Pastikan parameter query terbawa
        
        // Kategori untuk filter sidebar
        $categories = Category::active()
            ->withCount(['products' => function ($q) {
                $q->whereNull('deleted_at');
            }])
            ->orderBy('name')
            ->get();

        // Range harga untuk slider filter
        $priceRange = [
            'min' => (int) (Product::whereNull('deleted_at')->min('price') ?? 0),
            'max' => (int) (Product::whereNull('deleted_at')->max('price') ?? 2000000),
        ];

        return view('products.index', compact(
            'products',
            'categories',
            'priceRange',
        ));
    }

    /**
     * Product detail dengan gallery, reviews, dan related products.
     */
    public function show(string $slug)
    {
        $product = Product::with([
            'images' => function ($q) {
                $q->orderBy('sort_order');
            },
            'category',
            'reviews' => function ($q) {
                $q->where('is_approved', true)
                  ->with('user')
                  ->latest();
            },
        ])
        ->where('slug', $slug)
        ->whereNull('deleted_at')
        ->firstOrFail();

        // Related products — kategori sama, exclude produk ini
        $relatedProducts = Product::with(['images', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'coming_soon')
            ->withCount(['reviews' => function ($q) {
                $q->where('is_approved', true);
            }])
            ->latest()
            ->take(4)
            ->get();

        // Statistik rating
        $reviewsCount = $product->reviews->count();
        $avgRating = $reviewsCount > 0
            ? round($product->reviews->avg('rating'), 1)
            : 0;

        // Rating breakdown (5 → 1)
        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $product->reviews->where('rating', $i)->count();
            $ratingBreakdown[$i] = [
                'count' => $count,
                'percent' => $reviewsCount > 0
                    ? round(($count / $reviewsCount) * 100)
                    : 0,
            ];
        }

        // Cek status wishlist user yang sedang login
        $inWishlist = false;
        if (auth()->check()) {
            $inWishlist = auth()->user()
                ->wishlists()
                ->where('product_id', $product->id)
                ->exists();
        }

        // Cek apakah user pernah beli & bisa review
        $canReview = false;
        $reviewedOrderId = null;
        if (auth()->check()) {
            $purchasedOrder = \App\Models\Order::where('user_id', auth()->id())
                ->where('status', 'completed')
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->whereDoesntHave('reviews', function ($q) use ($product) {
                    $q->where('product_id', $product->id)
                      ->where('user_id', auth()->id());
                })
                ->latest()
                ->first();

            if ($purchasedOrder) {
                $canReview = true;
                $reviewedOrderId = $purchasedOrder->id;
            }
        }

        return view('products.show', compact(
            'product',
            'relatedProducts',
            'avgRating',
            'reviewsCount',
            'ratingBreakdown',
            'inWishlist',
            'canReview',
            'reviewedOrderId',
        ));
    }

    /**
     * Notifikasi produk (Coming Soon / Sold Out)
     */
    public function notifyMe(Product $product)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $user = auth()->user();
        
        // Cek apakah produk valid untuk notifikasi
        if (!in_array($product->status, ['coming_soon', 'sold_out'])) {
            return response()->json([
                'success' => false,
                'message' => 'Produk ini sudah tersedia.'
            ], 422);
        }
        
        // Cek apakah sudah terdaftar
        $exists = ProductNotification::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah terdaftar untuk notifikasi produk ini.'
            ]);
        }
        
        // Simpan notifikasi
        ProductNotification::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Anda akan diberi tahu ketika {$product->name} tersedia."
        ]);
    }
}