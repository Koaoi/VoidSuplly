<?php
// routes/web.php — FINAL VERSI TERBARU (FIXED)

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
use App\Http\Controllers\Api\RajaOngkirController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController   as AdminCategory;
use App\Http\Controllers\Admin\ProductController    as AdminProduct;
use App\Http\Controllers\Admin\OrderController      as AdminOrder;
use App\Http\Controllers\Admin\UserController       as AdminUser;
use App\Http\Controllers\Admin\CommissionController as AdminCommission;
use App\Http\Controllers\Admin\ReviewController     as AdminReview;
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
| RAJAONGKIR API ROUTES (AJAX — Public / Auth)
|==========================================================================
*/
// Public routes (tanpa auth) untuk cek ongkir
Route::prefix('api/shipping')->name('shipping.')->group(function () {
    // Step-by-step dropdown
    Route::get('/provinces', [RajaOngkirController::class, 'provinces'])->name('provinces');
    Route::get('/cities', [RajaOngkirController::class, 'cities'])->name('cities');
    Route::get('/districts', [RajaOngkirController::class, 'districts'])->name('districts');
    Route::get('/subdistricts', [RajaOngkirController::class, 'subdistricts'])->name('subdistricts');

    // Direct search (modern autocomplete)
    Route::get('/search', [RajaOngkirController::class, 'search'])->name('search');

    // Hitung ongkir
    Route::post('/cost', [RajaOngkirController::class, 'cost'])->name('cost');
});

/*
|==========================================================================
| OLD RAJAONGKIR ROUTES (Fallback untuk template lama)
|==========================================================================
*/
Route::get('/provinces', [RajaOngkirController::class, 'getProvinces']);
Route::get('/cities', [RajaOngkirController::class, 'getCities']);
Route::post('/cost', [RajaOngkirController::class, 'getCost']);

/*
|==========================================================================
| MIDTRANS WEBHOOK — exempt CSRF
|==========================================================================
*/
Route::post('/api/midtrans/callback', [PaymentController::class, 'midtransCallback'])
    ->name('midtrans.callback');

/*
|==========================================================================
| GUEST ROUTES (Not Authenticated)
|==========================================================================
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
|==========================================================================
| AUTHENTICATED ROUTES (Harus Login)
|==========================================================================
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ──────────────────────────────────────────────────────────────────────
    // PRODUCT NOTIFICATION
    // ──────────────────────────────────────────────────────────────────────
    Route::post('/product/{product}/notify', [ProductController::class, 'notifyMe'])->name('product.notify');

    // ──────────────────────────────────────────────────────────────────────
    // CART ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartController::class, 'remove'])->name('remove');
        Route::get('/count', [CartController::class, 'getCount'])->name('count');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/details', [CartController::class, 'details'])->name('details');
    });

    // ──────────────────────────────────────────────────────────────────────
    // WISHLIST ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
        Route::get('/check/{product}', [WishlistController::class, 'check'])->name('check');
    });

    // ──────────────────────────────────────────────────────────────────────
    // CHECKOUT ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    });

    // ──────────────────────────────────────────────────────────────────────
    // PAYMENT ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/{code}', [PaymentController::class, 'show'])->name('show');
        Route::post('/{code}/proof', [PaymentController::class, 'uploadProof'])->name('proof');
    });

    // ──────────────────────────────────────────────────────────────────────
    // ORDER ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{code}', [OrderController::class, 'show'])->name('show');
    });

    // ──────────────────────────────────────────────────────────────────────
    // REVIEW ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // ──────────────────────────────────────────────────────────────────────
    // COMMISSION ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('commission')->name('commission.')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index');
        Route::get('/create', [CommissionController::class, 'create'])->name('create');
        Route::post('/', [CommissionController::class, 'store'])->name('store');
        Route::get('/{commission}', [CommissionController::class, 'show'])->name('show');
        Route::delete('/{commission}', [CommissionController::class, 'destroy'])->name('destroy');
    });

    // ──────────────────────────────────────────────────────────────────────
    // PROFILE ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
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

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ──────────────────────────────────────────────────────────────────────
    // CATEGORIES
    // ──────────────────────────────────────────────────────────────────────
    Route::resource('categories', AdminCategory::class)->except(['show']);

    // ──────────────────────────────────────────────────────────────────────
    // PRODUCTS
    // ──────────────────────────────────────────────────────────────────────
    Route::resource('products', AdminProduct::class);
    Route::delete('/products/image/{image}', [AdminProduct::class, 'deleteImage'])->name('products.delete-image');
    Route::post('/products/image/{image}/primary', [AdminProduct::class, 'setPrimaryImage'])->name('products.set-primary');

    // ──────────────────────────────────────────────────────────────────────
    // ORDERS
    // ──────────────────────────────────────────────────────────────────────
    Route::get('/orders', [AdminOrder::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrder::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [AdminOrder::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/confirm', [AdminOrder::class, 'confirmPayment'])->name('orders.confirm');

    // ──────────────────────────────────────────────────────────────────────
    // USERS
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUser::class, 'index'])->name('index');
        Route::get('/{user}', [AdminUser::class, 'show'])->name('show');
        Route::put('/{user}', [AdminUser::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUser::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/role', [AdminUser::class, 'updateRole'])->name('role');
    });

    // ──────────────────────────────────────────────────────────────────────
    // COMMISSIONS
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [AdminCommission::class, 'index'])->name('index');
        Route::get('/{commission}', [AdminCommission::class, 'show'])->name('show');
        Route::patch('/{commission}/status', [AdminCommission::class, 'updateStatus'])->name('status');
    });

    // ──────────────────────────────────────────────────────────────────────
    // REVIEWS
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [AdminReview::class, 'index'])->name('index');
        Route::patch('/{review}/approve', [AdminReview::class, 'toggleApprove'])->name('approve');
        Route::delete('/{review}', [AdminReview::class, 'destroy'])->name('destroy');
    });
});

/*
|==========================================================================
| CEK ONGKIR PAGE (Testing / Kampus)
|==========================================================================
*/
Route::get('/cek-ongkir', function () {
    return view('ongkir');
})->name('cek-ongkir');

Route::get('/list-ongkir', function () {
    $response = Http::withHeaders([
        'key' => env('RAJAONGKIR_API_KEY')
    ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');
    
    return $response->json();
})->name('list-ongkir');

Route::get('/test-shipping', function () {
    return view('test-shipping');
})->name('test.shipping');

Route::get('/test-raja-status', function () {
    $apiKey = env('RAJAONGKIR_API_KEY');
    $baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    
    try {
        $response = Http::timeout(10)
            ->withHeaders(['key' => $apiKey])
            ->get($baseUrl . '/destination/province');
        
        return response()->json([
            'status' => $response->status(),
            'success' => $response->successful(),
            'message' => $response->successful() ? 'API OK' : 'API Error',
            'data' => $response->json()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
});