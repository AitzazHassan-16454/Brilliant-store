<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('???###')),
            'type' => fake()->randomElement(['percentage', 'fixed']),
            'value' => fake()->randomFloat(2, 1, 50),
            'min_order' => 0,
            'max_uses' => null,
            'used_count' => 0,
            'expires_at' => null,
            'active' => true,
        ];
    }

    public function percentage(): static
    {
        return $this->state(fn () => ['type' => 'percentage']);
    }

    public function fixed(): static
    {
        return $this->state(fn () => ['type' => 'fixed']);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }

    public function maxedOut(): static
    {
        return $this->state(fn () => ['max_uses' => 1, 'used_count' => 1]);
    }
}
