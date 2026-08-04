<?php

use App\Mail\OrderStatusUpdated;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function adminRole(): Role
{
    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    return tap(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']), function (Role $role) {
        $role->syncPermissions(ApplicationPermissions::all());
    });
}

function orderWithItem(User $user, array $overrides = []): Order
{
    return Order::factory()->create(array_merge(['user_id' => $user->id], $overrides));
}

it('requires authentication to view orders', function () {
    $this->get('/orders')->assertRedirect('/login');
});

it('shows only the current users orders', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownOrder = orderWithItem($user);
    $otherOrder = orderWithItem($otherUser);

    $this->actingAs($user)->get('/orders')
        ->assertInertia(
            fn ($page) => $page->component('Orders/Index', false)
                ->has('orders', 1)
                ->where('orders.0.id', $ownOrder->id)
        );
});

it('can delete the current users order', function () {
    $user = User::factory()->create();
    $order = orderWithItem($user);

    $this->actingAs($user)->delete("/orders/{$order->id}")
        ->assertRedirect()
        ->assertSessionHas('success', 'Order deleted successfully!');

    expect(Order::find($order->id))->toBeNull();
});

it('cannot delete another users order', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $order = orderWithItem($otherUser);

    $this->actingAs($user)->delete("/orders/{$order->id}");

    expect(Order::find($order->id))->not->toBeNull();
});

it('reorders items back into the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 10]);
    $order = orderWithItem($user);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'price' => 50,
    ]);

    $this->actingAs($user)->post("/orders/{$order->id}/reorder")
        ->assertRedirect()
        ->assertSessionHas('success', 'Items added to your cart!');

    $cart = Cart::where('user_id', $user->id)->firstOrFail();
    expect($cart->product_id)->toBe($product->id);
    expect($cart->qty)->toBe(3);
});

it('reorder caps the cart quantity at available stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 2]);
    $order = orderWithItem($user);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'price' => 50,
    ]);

    $this->actingAs($user)->post("/orders/{$order->id}/reorder");

    $cart = Cart::where('user_id', $user->id)->firstOrFail();
    expect($cart->qty)->toBe(2);
});

it('requires orders.view permission for the admin orders page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/Orders')->assertForbidden();
});

it('renders the admin orders page with permission', function () {
    adminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->get('/Orders')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Products/Orders', false));
});

it('requires orders.update permission to change a status', function () {
    $user = User::factory()->create();
    $order = orderWithItem($user);

    $this->actingAs($user)->post("/orders/{$order->id}/status", ['status' => 'confirmed'])
        ->assertForbidden();
});

it('updates the status and records history', function () {
    adminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');
    $order = orderWithItem($user, ['status' => 'pending']);

    $this->actingAs($user)->post("/orders/{$order->id}/status", ['status' => 'confirmed'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('confirmed');
    $history = OrderStatusHistory::where('order_id', $order->id)->firstOrFail();
    expect($history->status)->toBe('confirmed');
    expect($history->note)->toBe('Status changed from "pending" to "confirmed"');
});

it('emails the customer when the status changes', function () {
    Mail::fake();
    adminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');
    $order = orderWithItem($user, ['status' => 'pending']);

    $this->actingAs($user)->post("/orders/{$order->id}/status", ['status' => 'confirmed']);

    Mail::assertSent(OrderStatusUpdated::class, function (OrderStatusUpdated $mail) use ($order) {
        return $mail->hasTo($order->shipping_email) && $mail->order->status === 'confirmed';
    });
});
