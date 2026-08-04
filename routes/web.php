<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomOrderController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\WishlistController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ProductController::class, 'home'])->name('home');

Route::get('/login', fn () => redirect('/'))->name('login');

Route::get('/custom-order', [CustomOrderController::class, 'create'])->name('custom-order.create');

Route::get('/faq', [FaqController::class, 'index'])->name('faq');

Route::get('/products/{product}', [ProductController::class, 'show'])
    ->name('products.show');

Route::get('/track-order', [OrderTrackingController::class, 'show'])
    ->name('track-order');

Route::get('/about', fn () => Inertia::render('About'))->name('about');

Route::get('/categories/{category}', [CategoryController::class, 'show'])
    ->name('categories.show');

Route::middleware('guest')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {

    Route::post('/welcome/seen', [AuthController::class, 'markWelcomeSeen']);

    Route::get('/Profile', function () {
        return Inertia::render('Profile', [
            'ordersCount' => Order::where('user_id', auth()->id())->count(),
        ]);
    })->name('profile');

    Route::get('/EditProfile', function () {
        return Inertia::render('EditProfile', [
            'user' => auth()->user(),
        ]);
    });

    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::post('/cart/increase/{id}', [CartController::class, 'increase']);
    Route::post('/cart/decrease/{id}', [CartController::class, 'decrease']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'toggle']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);
    Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve']);
    Route::post('/reviews/{review}/reject', [ReviewController::class, 'reject']);
    Route::delete('/reviews/{review}/admin', [ReviewController::class, 'adminDestroy']);

    Route::post('/ai/chat', [ChatBotController::class, 'chat']);
    Route::get('/ai/history', [ChatBotController::class, 'history']);

    Route::get('/orders', [OrderController::class, 'index']);

    Route::post('/checkout', [CheckoutController::class, 'checkout']);

    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

    Route::post('/orders/{id}/reorder', [OrderController::class, 'reorder']);

    Route::resource('categories', CategoryController::class)
        ->except(['create', 'edit', 'show']);

    Route::resource('subcategories', SubcategoryController::class)
        ->except(['create', 'edit', 'show']);

    Route::resource('products', ProductController::class)
        ->except(['show', 'home']);

    Route::post('/products/ai-description', [ProductController::class, 'generateDescription']);

    Route::get('/CreateProduct', [ProductController::class, 'create']);

    Route::post('/products/{product}/toggle-trending', [ProductController::class, 'toggleTrending']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/Orders', [OrderController::class, 'adminIndex']);

    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    Route::get('/dashboard', [ProductController::class, 'dashboard']);

    Route::get('/users', [AuthController::class, 'users'])->name('users.index');
    Route::get('/users/create', [AuthController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AuthController::class, 'storeUser'])->name('users.store');

    Route::resource('roles', RoleController::class)
        ->except(['show']);

    Route::post('/coupons', [CouponController::class, 'store']);
    Route::put('/coupons/{coupon}', [CouponController::class, 'update']);
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy']);
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons/validate', [CouponController::class, 'validateCode']);

    Route::post('/custom-order', [CustomOrderController::class, 'store']);
    Route::get('/custom-orders', [CustomOrderController::class, 'index']);

    Route::get('/faqs', [FaqController::class, 'adminIndex']);
});
