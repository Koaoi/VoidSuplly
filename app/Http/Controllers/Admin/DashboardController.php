<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Statistik utama ──────────────────────────────────────────────────
        $stats = [
            'total_users'       => User::where('role','customer')->count(),
            'total_products'    => Product::whereNull('deleted_at')->count(),
            'total_orders'      => Order::count(),
            'total_revenue'     => Order::whereIn('status',['paid','processing','shipped','completed'])
                                        ->sum('total_price'),
            'pending_orders'    => Order::where('status','pending')->count(),
            'pending_commissions' => Commission::where('status','pending')->count(),
            'low_stock'         => Product::whereNull('deleted_at')
                                          ->where('stock','<=',5)
                                          ->where('status','available')
                                          ->count(),
            'new_reviews'       => Review::where('created_at','>=',now()->subDays(7))->count(),
        ];

        // ── Revenue 7 hari terakhir ──────────────────────────────────────────
        $revenueChart = Order::whereIn('status',['paid','processing','shipped','completed'])
            ->where('created_at','>=', now()->subDays(6))
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartDays   = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = now()->subDays($i)->format('Y-m-d');
            $chartDays[]   = now()->subDays($i)->format('d M');
            $chartValues[] = (int) ($revenueChart[$date]->total ?? 0);
        }

        // ── Order terbaru ────────────────────────────────────────────────────
        $recentOrders = Order::with(['user','items'])
            ->latest()
            ->take(8)
            ->get();

        // ── Order per status ─────────────────────────────────────────────────
        $ordersByStatus = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total','status')
            ->toArray();

        // ── Produk stok rendah ────────────────────────────────────────────────
        $lowStockProducts = Product::whereNull('deleted_at')
            ->where('stock','<=',5)
            ->where('status','available')
            ->with('category')
            ->orderBy('stock')
            ->take(5)
            ->get();

        // ── Commission terbaru ────────────────────────────────────────────────
        $recentCommissions = Commission::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats',
            'chartDays',
            'chartValues',
            'recentOrders',
            'ordersByStatus',
            'lowStockProducts',
            'recentCommissions',
        ));
    }
}