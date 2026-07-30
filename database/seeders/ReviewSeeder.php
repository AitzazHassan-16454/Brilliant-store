<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory()->count(10)->create([
            'role' => 'user',
        ]);

        $users->each(function ($user) {
            $user->assignRole('user');
        });

        $products = Product::all();

        $comments = [
            5 => [
                'Absolutely love this product! Exceeded my expectations.',
                'Best purchase I\'ve made this year. Highly recommend!',
                'Perfect quality and fast shipping. Will buy again.',
                'Five stars all the way. Amazing value for money.',
                'Outstanding product. Does exactly what it promises.',
            ],
            4 => [
                'Great product, minor issue with packaging but overall happy.',
                'Really good quality for the price. Would recommend.',
                'Solid product. Works well, just missing one feature I wanted.',
                'Very satisfied with this purchase. Almost perfect.',
                'Good build quality and looks great. Minor improvements needed.',
            ],
            3 => [
                'Decent product. Does the job but nothing special.',
                'Average quality. Expected a bit more for the price.',
                'It works fine but I\'ve seen better alternatives.',
                'Okay product. Met my basic expectations.',
                'Not bad, not great. Fair for the price point.',
            ],
            2 => [
                'Disappointed with the quality. Expected better.',
                'Product works but feels cheap. Wouldn\'t recommend.',
                'Below average. Had issues after a week of use.',
                'Not worth the price. Look elsewhere.',
                'Had high hopes but let down. Needs improvement.',
            ],
            1 => [
                'Terrible product. Broke after two days.',
                'Worst purchase ever. Do not buy this.',
                'Complete waste of money. Returning immediately.',
                'Doesn\'t work as described. Very frustrated.',
                'Absolutely horrible experience. Zero stars if I could.',
            ],
        ];

        foreach ($users as $user) {
            $reviewableProducts = $products->random(min(3, $products->count()));

            foreach ($reviewableProducts as $product) {
                $rand = fake()->numberBetween(1, 100);
                $rating = $rand <= 5 ? 1 : ($rand <= 15 ? 2 : ($rand <= 35 ? 3 : ($rand <= 70 ? 4 : 5)));

                Review::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'rating' => $rating,
                    'comment' => fake()->randomElement($comments[$rating]),
                    'status' => fake()->randomElement(['pending', 'approved', 'approved']),
                ]);
            }
        }
    }
}
