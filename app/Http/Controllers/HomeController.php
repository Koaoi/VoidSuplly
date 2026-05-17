<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Featured — available, stok ada, terbaru
        $featuredProducts = Product::with(['images', 'category', 'reviews'])
            ->whereNull('deleted_at')
            ->where('status', 'available')
            ->where('stock', '>', 0)
            ->latest()
            ->take(8)
            ->get();

        // Upcoming drops — coming_soon dengan release_date mendatang
        $upcomingDrops = Product::with(['images'])
            ->whereNull('deleted_at')
            ->where('status', 'coming_soon')
            ->whereNotNull('release_date')
            ->where('release_date', '>', now())
            ->orderBy('release_date')
            ->take(3)
            ->get();

        // Preorder aktif
        $preorderProducts = Product::with(['images', 'category'])
            ->whereNull('deleted_at')
            ->where('status', 'preorder')
            ->latest()
            ->take(4)
            ->get();

        // Semua kategori aktif dengan jumlah produk
        $categories = Category::active()
            ->withCount(['products' => function ($q) {
                $q->whereNull('deleted_at');
            }])
            ->get();

        return view('home.index', compact(
            'featuredProducts',
            'upcomingDrops',
            'preorderProducts',
            'categories',
        ));
    }
}