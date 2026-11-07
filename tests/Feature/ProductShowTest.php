<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

it('renders the product detail page with product, category, and approved reviews', function () {
    $category = Category::factory()->create(['name' => 'Fine Jewelry']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Sapphire Pendant',
        'price' => 1250.00,
        'stock' => 5,
        'is_active' => true,
    ]);

    $reviewer = User::factory()->create(['name' => 'Elena Rostova']);
    Review::factory()->create([
        'product_id' => $product->id,
        'user_id' => $reviewer->id,
        'rating' => 5,
        'comment' => 'Exceptional craftsmanship and breathtaking clarity.',
        'status' => 'approved',
    ]);

    $response = $this->get(route('products.show', $product->uid));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Product', false)
            ->where('product.name', 'Sapphire Pendant')
            ->where('product.category.name', 'Fine Jewelry')
            ->has('reviews', 1)
            ->where('reviews.0.comment', 'Exceptional craftsmanship and breathtaking clarity.')
            ->has('cartProductIds')
            ->has('wishlistProductIds')
        );
});

it('allows authenticated users to submit a review for a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($user)
        ->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Stunning in every way.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'product_id' => $product->id,
        'user_id' => $user->id,
        'rating' => 5,
        'comment' => 'Stunning in every way.',
    ]);
});
