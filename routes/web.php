<?php
// routes/web.php — versi final lengkap

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
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\RajaOngkirController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/product/quick-view/{id}', [ProductController::class, 'quickView'])->name('product.quick-view');

/*
|--------------------------------------------------------------------------
| RajaOngkir Routes (Cek Ongkir)
|--------------------------------------------------------------------------
*/
// Halaman Cek Ongkir
Route::get('/cek-ongkir', fn () => view('ongkir'));

// Debug: List Provinsi langsung dari API
Route::get('/list-ongkir', function () {
    $response = Http::withHeaders([
        'key' => env('RAJAONGKIR_API_KEY')
    ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');
    
    return $response->json();
});

// API Endpoints untuk Cek Ongkir
Route::get('/provinces', [RajaOngkirController::class, 'getProvinces']);
Route::get('/cities', [RajaOngkirController::class, 'getCities']);
Route::post('/cost', [RajaOngkirController::class, 'getCost']);
Route::get('/subdistricts', [RajaOngkirController::class, 'getSubdistricts']);
Route::get('/couriers', [RajaOngkirController::class, 'getCouriers']);

/*
|--------------------------------------------------------------------------
| Midtrans Webhook (CSRF exempt)
|--------------------------------------------------------------------------
*/
Route::post('/api/midtrans/callback', [PaymentController::class, 'midtransCallback'])
    ->name('midtrans.callback');

/*
|--------------------------------------------------------------------------
| Guest Routes (Not Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Authentication
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Social Login
    Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Harus Login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ─── Product Notification ────────────────────────────────────────────────
    Route::post('/product/{product}/notify', [ProductController::class, 'notifyMe'])->name('product.notify');

    // ─── Cart Routes ─────────────────────────────────────────────────────
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartController::class, 'remove'])->name('remove');
        Route::get('/count', [CartController::class, 'getCount'])->name('count');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/details', [CartController::class, 'details'])->name('details');
    });

    // ─── Wishlist Routes ─────────────────────────────────────────────────
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
        Route::get('/check/{product}', [WishlistController::class, 'check'])->name('check');
    });

    // ─── Checkout Routes ─────────────────────────────────────────────────
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/cities', [CheckoutController::class, 'getCities'])->name('cities');
        Route::post('/ongkir', [CheckoutController::class, 'getOngkir'])->name('ongkir');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    });

    // ─── Payment Routes ──────────────────────────────────────────────────
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/{code}', [PaymentController::class, 'show'])->name('show');
        Route::post('/{code}/proof', [PaymentController::class, 'uploadProof'])->name('proof');
    });

    // ─── Order Routes ────────────────────────────────────────────────────
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{code}', [OrderController::class, 'show'])->name('show');
    });

    // ─── Order Shipping & Payment Routes ─────────────────────────────────
    Route::prefix('order')->name('order.')->group(function () {
        Route::post('select-shipping', [OrderController::class, 'selectShipping'])->name('selectShipping');
        Route::post('update-ongkir', [OrderController::class, 'updateOngkir'])->name('update-ongkir');
        Route::get('select-payment', [OrderController::class, 'selectPayment'])->name('selectpayment');
        Route::get('complete', [OrderController::class, 'complete'])->name('complete');
        Route::get('history', [OrderController::class, 'orderHistory'])->name('history');
        Route::get('invoice/{id}', [OrderController::class, 'invoiceFrontend'])->name('invoice');
    });

    // ─── Review Routes ───────────────────────────────────────────────────
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // ─── Commission Routes ───────────────────────────────────────────────
    Route::prefix('commission')->name('commission.')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index');
        Route::get('/create', [CommissionController::class, 'create'])->name('create');
        Route::post('/', [CommissionController::class, 'store'])->name('store');
        Route::get('/{commission}', [CommissionController::class, 'show'])->name('show');
        Route::delete('/{commission}', [CommissionController::class, 'destroy'])->name('destroy');
    });

    // ─── Profile Routes ──────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resource Controllers (Full CRUD)
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    
    // Resource Controllers (Partial)
    Route::resource('orders', App\Http\Controllers\Admin\OrderController::class)->only(['index', 'show', 'update']);
    Route::resource('commissions', App\Http\Controllers\Admin\CommissionController::class)->only(['index', 'show', 'update']);
    Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class)->only(['index', 'destroy']);

    // Users Management (Custom)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
        Route::get('/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
        Route::put('/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/role', [App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('role');
    });

    // Custom Status Update Routes
    Route::put('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::put('/commissions/{commission}/status', [App\Http\Controllers\Admin\CommissionController::class, 'updateStatus'])->name('commissions.update-status');

    // AJAX Routes for Product Images
    Route::prefix('products')->name('products.')->group(function () {
        Route::delete('/image/{image}', [App\Http\Controllers\Admin\ProductController::class, 'deleteImage'])->name('delete-image');
        Route::post('/image/{image}/primary', [App\Http\Controllers\Admin\ProductController::class, 'setPrimaryImage'])->name('set-primary');
    });
});