<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomOrderController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\WishlistController;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderCoupon;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    Route::get('/orders', function () {
        return Inertia::render('Orders/Index', [
            'orders' => Order::with('items.product', 'statuses.user')
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),
        ]);
    });

    Route::post('/checkout', function (Request $request) {

        $user = auth()->user();

        $validated = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_email' => ['required', 'email', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string'],
        ]);

        $cartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Cart is empty');
        }

        $outOfStock = $cartItems->first(function ($item) {
            return $item->product->stock < $item->qty;
        });

        if ($outOfStock) {
            return back()->with('error', '"'.$outOfStock->product->name.'" does not have enough stock.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->qty);
        $discount = 0;
        $coupon = null;

        if ($validated['coupon_code'] ?? null) {
            $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();

            if (! $coupon || ! $coupon->isValid()) {
                return back()->with('error', 'Invalid or expired coupon code.');
            }

            if ($subtotal < $coupon->min_order) {
                return back()->with('error', 'Minimum order of $'.$coupon->min_order.' required for this coupon.');
            }

            $discount = $coupon->calculateDiscount($subtotal);
        }

        $total = max(0, $subtotal - $discount);

        $createdOrder = null;

        DB::transaction(function () use ($user, $cartItems, $subtotal, $discount, $total, $coupon, $validated, &$createdOrder) {

            foreach ($cartItems as $item) {
                $item->product->decrement('stock', $item->qty);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => 'pending',
                'shipping_name' => $validated['shipping_name'],
                'shipping_email' => $validated['shipping_email'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->qty,
                    'price' => $item->product->price,
                ]);
            }

            if ($coupon) {
                OrderCoupon::create([
                    'order_id' => $order->id,
                    'coupon_id' => $coupon->id,
                    'discount_amount' => $discount,
                ]);

                $coupon->increment('used_count');
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'note' => 'Order placed',
            ]);

            Cart::where('user_id', $user->id)->delete();

            $createdOrder = $order;
        });

        return redirect('/orders')
            ->with('success', 'Order placed successfully!')
            ->with('tracking_code', $createdOrder->tracking_code);
    });

    Route::delete('/orders/{id}', function ($id) {
        Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Order deleted successfully!');
    });

    Route::post('/orders/{id}/reorder', function ($id) {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->with('items.product')
            ->firstOrFail();

        foreach ($order->items as $item) {
            if (! $item->product || ! $item->product->is_active) {
                continue;
            }

            $cart = Cart::where('user_id', auth()->id())
                ->where('product_id', $item->product_id)
                ->first();

            if ($cart) {
                $newQty = $cart->qty + $item->quantity;
                if ($newQty <= $item->product->stock) {
                    $cart->update(['qty' => $newQty]);
                } else {
                    $cart->update(['qty' => $item->product->stock]);
                }
            } else {
                Cart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $item->product_id,
                    'qty' => min($item->quantity, $item->product->stock),
                ]);
            }
        }

        return back()->with('success', 'Items added to your cart!');
    });

    Route::resource('categories', CategoryController::class)
        ->except(['create', 'edit', 'show']);

    Route::resource('subcategories', SubcategoryController::class)
        ->except(['create', 'edit', 'show']);

    Route::resource('products', ProductController::class)
        ->except(['show', 'home']);

    Route::post('/products/ai-description', [ProductController::class, 'generateDescription']);

    Route::get('/CreateProduct', [ProductController::class, 'create']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/Orders', function () {
        abort_if(! auth()->user()->can('orders.view'), 403);

        return Inertia::render('Products/Orders', [
            'orders' => Order::with('user', 'items.product', 'statuses.user')->latest()->get(),
        ]);
    });

    Route::post('/orders/{id}/status', function ($id, Request $request) {
        abort_if(! auth()->user()->can('orders.update'), 403);

        $order = Order::findOrFail($id);
        $previousStatus = $order->status;
        $newStatus = $request->status;

        $order->update(['status' => $newStatus]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => $newStatus,
            'note' => $previousStatus !== $newStatus
                ? "Status changed from \"{$previousStatus}\" to \"{$newStatus}\""
                : "Status re-set to \"{$newStatus}\"",
        ]);

        return back();
    });

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
