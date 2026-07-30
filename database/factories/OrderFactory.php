<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 500);
        $discount = fake()->randomFloat(2, 0, min(20, $subtotal * 0.2));

        return [
            'user_id' => User::factory(),
            'subtotal' => $subtotal,
            'total' => $subtotal - $discount,
            'discount' => $discount,
            'shipping_name' => fake()->name(),
            'shipping_email' => fake()->safeEmail(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_postal_code' => fake()->postcode(),
            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'stripe']),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
        ];
    }

    public function withItems(int $count = 2): static
    {
        return $this->afterCreating(function (Order $order) use ($count) {
            OrderItemFactory::new()->count($count)->create([
                'order_id' => $order->id,
            ]);

            $order->update([
                'total' => $order->items->sum(fn ($item) => $item->price * $item->quantity) - $order->discount,
            ]);
        });
    }
}
