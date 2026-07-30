<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            $users = User::factory()->count(3)->create(['role' => 'user']);
            $users->each(fn ($u) => $u->assignRole('user'));
        }

        $products = Product::all();

        $statusFlows = [
            ['pending', 'processing', 'shipped', 'delivered'],
            ['pending', 'processing', 'shipped'],
            ['pending', 'processing'],
            ['pending'],
            ['pending', 'cancelled'],
        ];

        $statusTimeline = [
            'pending' => 'Order placed successfully',
            'processing' => 'Payment confirmed, preparing your order',
            'shipped' => 'Package has been shipped via standard delivery',
            'delivered' => 'Package delivered successfully',
            'cancelled' => 'Order cancelled due to customer request',
        ];

        foreach ($users as $user) {
            $orderCount = rand(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $subtotal = 0;
                $itemCount = rand(1, 4);
                $orderProducts = $products->random($itemCount);

                $order = Order::create([
                    'user_id' => $user->id,
                    'subtotal' => 0,
                    'total' => 0,
                    'discount' => 0,
                    'shipping_name' => $user->name,
                    'shipping_email' => $user->email,
                    'shipping_phone' => fake()->phoneNumber(),
                    'shipping_address' => fake()->streetAddress(),
                    'shipping_city' => fake()->city(),
                    'shipping_postal_code' => fake()->postcode(),
                    'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'stripe']),
                    'status' => 'pending',
                ]);

                foreach ($orderProducts as $product) {
                    $quantity = rand(1, 3);
                    $price = $product->price;
                    $subtotal += $price * $quantity;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                }

                $discount = rand(0, 1) ? round($subtotal * 0.1, 2) : 0;
                $total = round($subtotal - $discount, 2);

                $order->update([
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'discount' => $discount,
                ]);

                $flow = $statusFlows[array_rand($statusFlows)];
                $admin = User::where('is_admin', true)->first();

                foreach ($flow as $status) {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'user_id' => $admin?->id,
                        'status' => $status,
                        'note' => $statusTimeline[$status] ?? null,
                    ]);

                    $order->update(['status' => $status]);
                }
            }
        }
    }
}
