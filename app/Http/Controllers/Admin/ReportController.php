<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request)
    {
        $startDate = $request->start_date ? date('Y-m-d 00:00:00', strtotime($request->start_date)) : now()->startOfMonth();
        $endDate = $request->end_date ? date('Y-m-d 23:59:59', strtotime($request->end_date)) : now()->endOfMonth();

        $orders = Order::with(['user', 'items'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total_price');
        $totalProducts = $orders->sum(fn($order) => $order->items->sum('quantity'));
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $bestSellers = OrderItem::with('product')
            ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'cancelled'))
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();

        return view('admin.reports.sales', compact('orders', 'totalOrders', 'totalRevenue', 'totalProducts', 'averageOrder', 'bestSellers', 'startDate', 'endDate'));
    }

    public function products(Request $request)
    {
        // Ambil semua produk dengan relasi kategori
        $products = Product::with(['category'])
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                });
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Hitung total revenue per produk (manual)
        foreach ($products as $product) {
            $product->total_revenue = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('subtotal');
        }

        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');
        $totalValue = Product::sum(DB::raw('price * stock'));
        $lowStockProducts = Product::where('stock', '<=', 10)->count();

        return view('admin.reports.products', compact('products', 'totalProducts', 'totalStock', 'totalValue', 'lowStockProducts'));
    }

    public function printSales(Request $request)
    {
        $startDate = $request->start_date ? date('Y-m-d 00:00:00', strtotime($request->start_date)) : now()->startOfMonth();
        $endDate = $request->end_date ? date('Y-m-d 23:59:59', strtotime($request->end_date)) : now()->endOfMonth();

        $orders = Order::with(['user', 'items'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total_price');
        $totalProducts = $orders->sum(fn($order) => $order->items->sum('quantity'));

        $html = view('admin.reports.print-sales', compact('orders', 'totalOrders', 'totalRevenue', 'totalProducts', 'startDate', 'endDate'))->render();
        
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('laporan-penjualan-' . date('Y-m-d') . '.pdf');
    }

    public function printProducts()
    {
        $products = Product::with(['category'])
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                });
            }])
            ->get();
        
        // Hitung total revenue per produk
        foreach ($products as $product) {
            $product->total_revenue = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('subtotal');
        }
            
        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');
        $totalValue = Product::sum(DB::raw('price * stock'));

        $html = view('admin.reports.print-products', compact('products', 'totalProducts', 'totalStock', 'totalValue'))->render();
        
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('laporan-produk-' . date('Y-m-d') . '.pdf');
    }
}