<?php

use App\Models\Category;
use App\Models\Product;

it('renders the home page with curated props and stats', function () {
    $category = Category::factory()->create(['name' => 'Horology Atelier']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Royal Tourbillon',
        'is_active' => true,
    ]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Home', false)
            ->has('categories')
            ->has('stats')
            ->has('trendingProducts')
            ->has('cartProductIds')
            ->has('wishlistProductIds')
        );
});

it('passes the latest active product to spotlight on home page', function () {
    $category = Category::factory()->create();
    $olderProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Classic Skeleton',
        'is_active' => true,
        'created_at' => now()->subDays(5),
    ]);
    $latestProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Celestial Perpetual',
        'is_active' => true,
        'created_at' => now(),
    ]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Home', false)
            ->where('latestProduct.id', $latestProduct->id)
            ->where('latestProduct.name', 'Celestial Perpetual')
        );
});

it('includes categories with products count on home page', function () {
    $category = Category::factory()->create(['name' => 'Fine Horology']);
    Product::factory()->count(3)->create([
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Home', false)
            ->where('categories.0.products_count', 3)
        );
});
