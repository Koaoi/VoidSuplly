<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\RajaOngkirControllerV2;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController   as AdminCategory;
use App\Http\Controllers\Admin\ProductController    as AdminProduct;
use App\Http\Controllers\Admin\OrderController      as AdminOrder;
use App\Http\Controllers\Admin\UserController       as AdminUser;
use App\Http\Controllers\Admin\CommissionController as AdminCommission;
use App\Http\Controllers\Admin\ReviewController     as AdminReview;
use App\Http\Controllers\Admin\ReportController     as ReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

/*
|==========================================================================
| PUBLIC ROUTES (Tanpa Login)
|==========================================================================
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/product/quick-view/{id}', [ProductController::class, 'quickView'])->name('product.quick-view');

/*
|==========================================================================
| RAJAONGKIR / ONGKIR ROUTES
|==========================================================================
*/
Route::middleware('auth')
    ->prefix('ongkir')
    ->name('ongkir.')
    ->group(function () {
        Route::get('/provinces',    [RajaOngkirControllerV2::class, 'getProvinces'])->name('provinces');
        Route::get('/cities',       [RajaOngkirControllerV2::class, 'getCities'])->name('cities');
        Route::get('/subdistricts', [RajaOngkirControllerV2::class, 'getSubdistricts'])->name('subdistricts');
        Route::post('/calculate',   [RajaOngkirControllerV2::class, 'calculateOngkir'])->name('calculate');
        Route::get('/couriers',     [RajaOngkirControllerV2::class, 'getCouriers'])->name('couriers');
    });

/*
|==========================================================================
| MIDTRANS WEBHOOK — exempt CSRF
|==========================================================================
*/
Route::post('/api/midtrans/callback', [PaymentController::class, 'midtransCallback'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('midtrans.callback');

/*
|==========================================================================
| GUEST ROUTES (Not Authenticated)
|==========================================================================
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');
});

/*
|==========================================================================
| AUTHENTICATED ROUTES (Harus Login)
|==========================================================================
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/product/{product}/notify', [ProductController::class, 'notifyMe'])->name('product.notify');

    // Cart Routes
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartController::class, 'remove'])->name('remove');
        Route::get('/count', [CartController::class, 'getCount'])->name('count');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/details', [CartController::class, 'details'])->name('details');
    });

    // Wishlist Routes
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
        Route::get('/check/{product}', [WishlistController::class, 'check'])->name('check');
    });

    // Checkout Routes
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    });

    // Order Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{code}', [OrderController::class, 'show'])->name('show');
    });

    // Review Routes
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Commission Routes
    Route::prefix('commission')->name('commission.')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index');
        Route::get('/create', [CommissionController::class, 'create'])->name('create');
        Route::post('/', [CommissionController::class, 'store'])->name('store');
        Route::get('/{commission}', [CommissionController::class, 'show'])->name('show');
        Route::delete('/{commission}', [CommissionController::class, 'destroy'])->name('destroy');
    });

    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Payment Routes (Digabung ke dalam satu middleware 'auth' agar lebih optimal)
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/{code}', [PaymentController::class, 'show'])->name('show');
        Route::post('/{code}/proof', [PaymentController::class, 'uploadProof'])->name('proof');
        Route::post('/{code}/snap-token', [PaymentController::class, 'getSnapToken'])->name('get-snap-token');
        Route::post('/{code}/callback-manual', [PaymentController::class, 'manualCallback'])->name('callback-manual');
    });
});

/*
|==========================================================================
| ADMIN ROUTES — middleware: auth + isAdmin
|==========================================================================
*/
Route::middleware(['auth', 'isAdmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', AdminCategory::class)->except(['show']);
        Route::resource('products', AdminProduct::class);
        Route::delete('/products/image/{image}', [AdminProduct::class, 'deleteImage'])->name('products.delete-image');
        Route::post('/products/image/{image}/primary', [AdminProduct::class, 'setPrimaryImage'])->name('products.set-primary');

        Route::get('/orders', [AdminOrder::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrder::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/status', [AdminOrder::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/confirm-payment', [AdminOrder::class, 'confirmPayment'])->name('orders.confirm-payment');
        Route::post('/orders/{order}/reject-payment', [AdminOrder::class, 'rejectPayment'])->name('orders.reject-payment');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUser::class, 'index'])->name('index');
            Route::get('/{user}', [AdminUser::class, 'show'])->name('show');
            Route::put('/{user}', [AdminUser::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUser::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/role', [AdminUser::class, 'updateRole'])->name('role');
        });

        Route::prefix('commissions')->name('commissions.')->group(function () {
            Route::get('/', [AdminCommission::class, 'index'])->name('index');
            Route::get('/{commission}', [AdminCommission::class, 'show'])->name('show');
            Route::patch('/{commission}/status', [AdminCommission::class, 'updateStatus'])->name('status');
        });

        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [AdminReview::class, 'index'])->name('index');
            Route::patch('/{review}/approve', [AdminReview::class, 'toggleApprove'])->name('approve');
            Route::delete('/{review}', [AdminReview::class, 'destroy'])->name('destroy');
        });
    });

/*
|==========================================================================
| ADMIN REPORT ROUTES (Di luar group admin agar lebih rapi)
|==========================================================================
*/
Route::middleware(['auth', 'isAdmin'])
    ->prefix('admin/reports')
    ->name('admin.reports.')
    ->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('sales');
        Route::get('/products', [App\Http\Controllers\Admin\ReportController::class, 'products'])->name('products');
        Route::get('/print-sales', [App\Http\Controllers\Admin\ReportController::class, 'printSales'])->name('print-sales');
        Route::get('/print-products', [App\Http\Controllers\Admin\ReportController::class, 'printProducts'])->name('print-products');
    });
/*
|==========================================================================
| TEST ROUTES (Untuk Debug)
|==========================================================================
*/
Route::get('/test-ongkir-final', function () {
    $apiKey = env('RAJAONGKIR_API_KEY');
    
    $response = Http::timeout(15)
        ->asForm()
        ->withHeaders(['key' => $apiKey])
        ->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
            'origin' => 17473,
            'destination' => 17473,
            'weight' => 1000,
            'courier' => 'jne',
            'price' => 'lowest'
        ]);
    
    $data = $response->json();
    
    if (isset($data['data']) && is_array($data['data'])) {
        $costs = [];
        foreach ($data['data'] as $item) {
            $costs[] = [
                'service' => $item['service'],
                'description' => $item['description'],
                'cost' => $item['cost'],
                'etd' => $item['etd']
            ];
        }
        return response()->json($costs);
    }
    
    return response()->json($data);
});

Route::get('/test-midtrans', function () {
    $serverKey = config('midtrans.server_key');
    
    return response()->json([
        'server_key_exists' => !empty($serverKey),
        'server_key_prefix' => !empty($serverKey) ? substr($serverKey, 0, 15) . '...' : 'null',
        'is_sandbox_format' => !empty($serverKey) && str_starts_with($serverKey, 'SB-Mid-server-'),
        'client_key_exists' => !empty(config('midtrans.client_key')),
    ]);
});

Route::get('/test-callback-manual/{orderCode}', function ($orderCode) {
    $order = \App\Models\Order::where('order_code', $orderCode)->first();
    
    if (!$order) {
        
        return response()->json(['error' => 'Order not found']);
    }
    
    $order->update(['status' => 'paid']);
    
    $payment = \App\Models\Payment::where('order_id', $order->id)->first();
    if ($payment) {
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
    
    return response()->json([
        'success' => true,
        'order_code' => $order->order_code,
        'status' => $order->status
    ]);
});