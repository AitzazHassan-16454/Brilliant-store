<?php

use App\Enums\PaymentMethod;
use App\Mail\OrderPlaced;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'shipping_name' => 'John Doe',
        'shipping_email' => 'john@example.com',
        'shipping_phone' => '03001234567',
        'shipping_address' => '123 Main Street',
        'shipping_city' => 'Lahore',
        'shipping_postal_code' => '54000',
        'notes' => 'Leave at the door',
        'coupon_code' => null,
        'payment_method' => 'cod',
    ], $overrides);
}

function withCart(User $user, array $products): void
{
    foreach ($products as $product) {
        Cart::create(['user_id' => $user->id, 'product_id' => $product['product']->id, 'qty' => $product['qty']]);
    }
}

it('requires authentication to check out', function () {
    $this->post('/checkout', checkoutPayload())->assertRedirect('/login');
});

it('requires a valid payment method', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 5]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)
        ->post('/checkout', checkoutPayload(['payment_method' => 'bitcoin']))
        ->assertSessionHasErrors('payment_method');
});

it('creates an order with items and status history', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 25.50, 'stock' => 5]);
    withCart($user, [['product' => $product, 'qty' => 2]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload());

    $order = Order::where('user_id', $user->id)->firstOrFail();

    expect($order->subtotal)->toBe('51.00');
    expect($order->discount)->toBe('0.00');
    expect($order->total)->toBe('51.00');
    expect($order->shipping_name)->toBe('John Doe');
    expect($order->notes)->toBe('Leave at the door');
    expect($order->tracking_code)->toMatch('/^BR-[A-Z0-9]{8}$/');

    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->product_id)->toBe($product->id);
    expect($order->items->first()->quantity)->toBe(2);
    expect($order->items->first()->price)->toBe('25.50');

    expect($order->statuses)->toHaveCount(1);
    expect($order->statuses->first()->status)->toBe($order->status);
    expect($order->statuses->first()->note)->toBe('Order placed (Cash on Delivery)');
});

it('confirms cash on delivery orders immediately', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 5]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload(['payment_method' => 'cod']));

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect($order->status)->toBe('confirmed');
    expect($order->payment_method)->toBe(PaymentMethod::CashOnDelivery->value);
});

it('keeps bank transfer and whatsapp orders pending', function (string $method) {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 5]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload(['payment_method' => $method]));

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect($order->status)->toBe('pending');
    expect($order->payment_method)->toBe($method);
})->with(['bank_transfer', 'whatsapp']);

it('decrements product stock when placing an order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 10]);
    withCart($user, [['product' => $product, 'qty' => 4]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload());

    expect($product->fresh()->stock)->toBe(6);
});

it('clears the cart after placing an order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 5]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload());

    expect(Cart::where('user_id', $user->id)->count())->toBe(0);
});

it('applies a valid coupon to the order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'stock' => 5]);
    $coupon = Coupon::factory()->fixed()->create(['code' => 'SAVE10', 'value' => 10, 'min_order' => 0]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload(['coupon_code' => 'save10']));

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect($order->discount)->toBe('10.00');
    expect($order->total)->toBe('90.00');
    expect($order->coupon)->not->toBeNull();
    expect($order->coupon->coupon_id)->toBe($coupon->id);
    expect($coupon->fresh()->used_count)->toBe(1);
});

it('rejects an invalid coupon without creating an order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 5]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload(['coupon_code' => 'NOPE']))
        ->assertRedirect()
        ->assertSessionHas('error', 'Invalid or expired coupon code.');

    expect(Order::count())->toBe(0);
});

it('rejects a coupon below the minimum order value', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10, 'stock' => 5]);
    $coupon = Coupon::factory()->fixed()->create(['code' => 'MIN50', 'value' => 5, 'min_order' => 50]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload(['coupon_code' => 'MIN50']))
        ->assertRedirect()
        ->assertSessionHas('error', 'Minimum order of $50.00 required for this coupon.');

    expect(Order::count())->toBe(0);
});

it('rejects checkout when the cart is empty', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/checkout', checkoutPayload())
        ->assertRedirect()
        ->assertSessionHas('error', 'Cart is empty');

    expect(Order::count())->toBe(0);
});

it('rejects checkout when an item is out of stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 2]);
    withCart($user, [['product' => $product, 'qty' => 3]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload())
        ->assertRedirect()
        ->assertSessionHas('error', '"'.$product->name.'" does not have enough stock.');

    expect(Order::count())->toBe(0);
    expect(Cart::where('user_id', $user->id)->count())->toBe(1);
});

it('flashes the tracking code and redirects to orders', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 5]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload())
        ->assertRedirect('/orders')
        ->assertSessionHas('success', 'Order placed successfully!');

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect(session('tracking_code'))->toBe($order->tracking_code);
});

it('creates no order when a cart item exceeds available stock', function () {
    $user = User::factory()->create();
    $productA = Product::factory()->create(['name' => 'Sofa', 'price' => 100, 'stock' => 5]);
    $productB = Product::factory()->create(['name' => 'Table', 'price' => 50, 'stock' => 1]);
    withCart($user, [
        ['product' => $productA, 'qty' => 2],
        ['product' => $productB, 'qty' => 2],
    ]);

    $this->actingAs($user)->post('/checkout', checkoutPayload())
        ->assertRedirect()
        ->assertSessionHas('error', '"Table" does not have enough stock.');

    expect(Order::count())->toBe(0);
    expect(OrderItem::count())->toBe(0);
    expect($productA->fresh()->stock)->toBe(5);
});

it('sends an order confirmation email to the shipping address', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 5]);
    withCart($user, [['product' => $product, 'qty' => 1]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload());

    $order = Order::where('user_id', $user->id)->firstOrFail();

    Mail::assertSent(OrderPlaced::class, function (OrderPlaced $mail) use ($order) {
        return $mail->hasTo('john@example.com') && $mail->order->is($order);
    });
});

it('does not send a confirmation email when checkout fails', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 1]);
    withCart($user, [['product' => $product, 'qty' => 3]]);

    $this->actingAs($user)->post('/checkout', checkoutPayload());

    Mail::assertNothingSent();
});

it('renders the order confirmation email', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'payment_method' => 'cod']);

    $html = (new OrderPlaced($order))->render();

    expect($html)->toBeString();
    expect($html)->toContain($order->tracking_code);
    expect($html)->toContain('Cash on Delivery');
});
