<?php

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createAdmin(): User
{
    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $role->syncPermissions(ApplicationPermissions::all());

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->assignRole($role);

    return $admin;
}

test('customer can upload a payment slip image for an order using tracking code', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'bank_transfer',
        'status' => 'pending',
    ]);

    $file = UploadedFile::fake()->image('receipt.jpg', 600, 800);

    $response = $this->post(route('track-order.payment-slip', ['code' => $order->tracking_code]), [
        'payment_slip' => $file,
    ]);

    $response->assertSessionHas('success');

    $order->refresh();
    expect($order->payment_slip)->not->toBeNull();
    expect($order->payment_slip_uploaded_at)->not->toBeNull();
    expect($order->payment_slip_url)->toBe(asset('storage/'.$order->payment_slip));
    Storage::disk('public')->assertExists($order->payment_slip);

    expect(OrderStatusHistory::where('order_id', $order->id)
        ->where('note', 'Payment slip uploaded by customer')
        ->exists()
    )->toBeTrue();
});

test('customer can upload a PDF payment slip', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'bank_transfer',
        'status' => 'pending',
    ]);

    $file = UploadedFile::fake()->create('receipt.pdf', 500, 'application/pdf');

    $response = $this->post(route('track-order.payment-slip', ['code' => $order->tracking_code]), [
        'payment_slip' => $file,
    ]);

    $response->assertSessionHas('success');

    $order->refresh();
    expect($order->payment_slip)->not->toBeNull();
    Storage::disk('public')->assertExists($order->payment_slip);
});

test('cannot upload non-allowed file types as payment slip', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'bank_transfer',
    ]);

    $file = UploadedFile::fake()->create('malicious.exe', 100);

    $response = $this->post(route('track-order.payment-slip', ['code' => $order->tracking_code]), [
        'payment_slip' => $file,
    ]);

    $response->assertSessionHasErrors('payment_slip');
});

test('cannot upload files larger than 5MB', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'bank_transfer',
    ]);

    $file = UploadedFile::fake()->create('large_receipt.jpg', 6000);

    $response = $this->post(route('track-order.payment-slip', ['code' => $order->tracking_code]), [
        'payment_slip' => $file,
    ]);

    $response->assertSessionHasErrors('payment_slip');
});

test('re-uploading replaces the previous payment slip on disk', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'bank_transfer',
    ]);

    $firstFile = UploadedFile::fake()->image('slip1.png');
    $this->post(route('track-order.payment-slip', ['code' => $order->tracking_code]), [
        'payment_slip' => $firstFile,
    ]);

    $order->refresh();
    $firstPath = $order->payment_slip;
    Storage::disk('public')->assertExists($firstPath);

    $secondFile = UploadedFile::fake()->image('slip2.png');
    $this->post(route('track-order.payment-slip', ['code' => $order->tracking_code]), [
        'payment_slip' => $secondFile,
    ]);

    $order->refresh();
    $secondPath = $order->payment_slip;

    expect($secondPath)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($secondPath);
});

test('cannot upload slip for unknown tracking code', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('slip.jpg');

    $response = $this->post(route('track-order.payment-slip', ['code' => 'BR-NONEXISTENT']), [
        'payment_slip' => $file,
    ]);

    $response->assertNotFound();
});

test('admin can confirm order and record verified status history', function () {
    $admin = createAdmin();
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'bank_transfer',
        'status' => 'pending',
        'payment_slip' => 'payment_slips/sample.jpg',
        'payment_slip_uploaded_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post("/orders/{$order->id}/status", [
        'status' => 'confirmed',
    ]);

    $response->assertRedirect();
    $order->refresh();
    expect($order->status)->toBe('confirmed');

    $latestStatus = OrderStatusHistory::where('order_id', $order->id)->latest('id')->first();
    expect($latestStatus->note)->toContain('Payment verified');
});

test('admin can remove an uploaded payment slip', function () {
    Storage::fake('public');

    $admin = createAdmin();
    $user = User::factory()->create();

    $slipPath = 'payment_slips/receipt_to_delete.png';
    Storage::disk('public')->put($slipPath, 'fake-content');

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_slip' => $slipPath,
        'payment_slip_uploaded_at' => now(),
    ]);

    $response = $this->actingAs($admin)->delete(route('orders.remove-slip', ['id' => $order->id]));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $order->refresh();
    expect($order->payment_slip)->toBeNull();
    expect($order->payment_slip_uploaded_at)->toBeNull();
    Storage::disk('public')->assertMissing($slipPath);

    expect(OrderStatusHistory::where('order_id', $order->id)
        ->where('note', 'Payment slip removed by admin')
        ->exists()
    )->toBeTrue();
});

test('non-admin user cannot remove a payment slip', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_slip' => 'payment_slips/test.jpg',
    ]);

    $response = $this->actingAs($user)->delete(route('orders.remove-slip', ['id' => $order->id]));

    $response->assertForbidden();
});
