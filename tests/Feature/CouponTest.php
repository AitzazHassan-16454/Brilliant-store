<?php

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createCouponAdminRole(): void
{
    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(ApplicationPermissions::all());
}

function couponCheckoutPayload(array $overrides = []): array
{
    return array_merge([
        'shipping_name' => 'John Doe',
        'shipping_email' => 'john@example.com',
        'shipping_phone' => '03001234567',
        'shipping_address' => '123 Main Street',
        'shipping_city' => 'Lahore',
        'payment_method' => 'bank_transfer',
    ], $overrides);
}

it('requires authentication to view coupons', function () {
    $this->get('/coupons')->assertRedirect('/login');
});

it('requires permission to view coupons', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/coupons')->assertForbidden();
});

it('shows coupons page for authorized admin', function () {
    createCouponAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->get('/coupons')->assertSuccessful();
});

it('can create a coupon', function () {
    createCouponAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->post('/coupons', [
        'code' => 'SAVE20',
        'type' => 'percentage',
        'value' => 20,
        'min_order' => 0,
    ])->assertRedirect();

    $this->assertDatabaseHas('coupons', [
        'code' => 'SAVE20',
        'type' => 'percentage',
    ]);
});

it('can update a coupon', function () {
    createCouponAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');
    $coupon = Coupon::factory()->create(['code' => 'OLD']);

    $this->actingAs($user)->put("/coupons/{$coupon->id}", [
        'code' => 'NEW',
        'type' => 'fixed',
        'value' => 10,
        'min_order' => 0,
    ])->assertRedirect();

    $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'code' => 'NEW']);
});

it('can delete a coupon', function () {
    createCouponAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');
    $coupon = Coupon::factory()->create();

    $this->actingAs($user)->delete("/coupons/{$coupon->id}")->assertRedirect();

    $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
});

it('validates coupon code exists', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/coupons/validate', [
        'code' => 'NONEXISTENT',
        'subtotal' => 100,
    ])->assertRedirect();
});

it('validates expired coupon', function () {
    $user = User::factory()->create();
    $coupon = Coupon::factory()->expired()->create(['code' => 'EXPIRED']);

    $this->actingAs($user)->post('/coupons/validate', [
        'code' => 'EXPIRED',
        'subtotal' => 100,
    ])->assertRedirect();

    session()->has('error');
    $this->assertStringContainsString('expired', session('error'));
});

it('validates maxed out coupon', function () {
    $user = User::factory()->create();
    $coupon = Coupon::factory()->maxedOut()->create(['code' => 'MAXED']);

    $this->actingAs($user)->post('/coupons/validate', [
        'code' => 'MAXED',
        'subtotal' => 100,
    ])->assertRedirect();

    $this->assertStringContainsString('usage limit', session('error'));
});

it('validates minimum order amount', function () {
    $user = User::factory()->create();
    Coupon::factory()->create(['code' => 'MIN100', 'min_order' => 100]);

    $this->actingAs($user)->post('/coupons/validate', [
        'code' => 'MIN100',
        'subtotal' => 50,
    ])->assertRedirect();

    $this->assertStringContainsString('Minimum order', session('error'));
});

it('returns valid coupon with discount', function () {
    $user = User::factory()->create();
    Coupon::factory()->percentage()->create(['code' => 'PCT20', 'value' => 20]);

    $this->actingAs($user)->post('/coupons/validate', [
        'code' => 'PCT20',
        'subtotal' => 100,
    ])->assertRedirect();

    $coupon = session('coupon');
    $this->assertNotNull($coupon);
    $this->assertEquals('PCT20', $coupon['code']);
    $this->assertEquals(20.0, $coupon['discount']);
});

it('applies coupon during checkout', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 100]);
    $coupon = Coupon::factory()->percentage()->create(['code' => 'CHECKOUT20', 'value' => 20]);

    Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 1]);

    $this->actingAs($user)->post('/checkout', couponCheckoutPayload(['coupon_code' => 'CHECKOUT20']));

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'subtotal' => 100,
        'discount' => 20,
        'total' => 80,
    ]);

    $this->assertDatabaseHas('order_coupons', [
        'coupon_id' => $coupon->id,
        'discount_amount' => 20,
    ]);

    $coupon->refresh();
    $this->assertEquals(1, $coupon->used_count);
});

it('checkout without coupon has no discount', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 50]);

    Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 2]);

    $this->actingAs($user)->post('/checkout', couponCheckoutPayload());

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'subtotal' => 100,
        'discount' => 0,
        'total' => 100,
    ]);
});
