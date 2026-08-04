<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

function trackingCheckoutPayload(): array
{
    return [
        'shipping_name' => 'John Doe',
        'shipping_email' => 'john@example.com',
        'shipping_phone' => '03001234567',
        'shipping_address' => '123 Main Street',
        'shipping_city' => 'Lahore',
        'payment_method' => 'cod',
    ];
}

it('is publicly accessible', function () {
    $this->get('/track-order')->assertSuccessful();
});

it('renders the tracking page component', function () {
    $this->get('/track-order')
        ->assertInertia(
            fn ($page) => $page->component('Orders/Track', false)->where('order', null)
        );
});

it('generates a tracking code when placing an order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5, 'price' => 50]);
    Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', trackingCheckoutPayload());

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect($order->tracking_code)->not->toBeNull();
    expect($order->tracking_code)->toMatch('/^BR-[A-Z0-9]{8}$/');
});

it('flashes the tracking code after checkout', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5, 'price' => 50]);
    Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', trackingCheckoutPayload());

    $order = Order::where('user_id', $user->id)->firstOrFail();
    expect(session('tracking_code'))->toBe($order->tracking_code);
});

it('generates unique tracking codes for each order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 20, 'price' => 20]);

    foreach (range(1, 3) as $i) {
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 1]);
        $this->actingAs($user)->post('/checkout', trackingCheckoutPayload());
    }

    $codes = Order::pluck('tracking_code')->all();
    expect($codes)->toHaveCount(3);
    expect(array_unique($codes))->toHaveCount(3);
});

it('finds an order by its tracking code', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->get('/track-order?code='.$order->tracking_code)
        ->assertSuccessful()
        ->assertInertia(
            fn ($page) => $page->component('Orders/Track', false)
                ->where('order.id', $order->id)
                ->where('order.tracking_code', $order->tracking_code)
        );
});

it('is case insensitive when looking up a tracking code', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->get('/track-order?code='.strtolower($order->tracking_code))
        ->assertSuccessful()
        ->assertInertia(
            fn ($page) => $page->component('Orders/Track', false)->where('order.id', $order->id)
        );
});

it('shows an error for an unknown tracking code', function () {
    $this->get('/track-order?code=BR-INVALID1')
        ->assertSuccessful()
        ->assertInertia(
            fn ($page) => $page->component('Orders/Track', false)
                ->where('order', null)
                ->has('error')
        );
});
