<?php

use App\Models\Category;
use App\Models\Product;

it('renders the all products page with active products', function () {
    $category = Category::factory()->create();
    $activeProduct = Product::factory()->create([
        'category_id' => $category->id,
        'is_active' => true,
    ]);
    $inactiveProduct = Product::factory()->create([
        'category_id' => $category->id,
        'is_active' => false,
    ]);

    $response = $this->get(route('products.all'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Products/All', false)
            ->has('products.data', 1)
            ->where('products.data.0.uid', $activeProduct->uid)
            ->has('categories')
        );
});

it('filters products by category on the all products page', function () {
    $categoryA = Category::factory()->create();
    $categoryB = Category::factory()->create();

    $productA = Product::factory()->create(['category_id' => $categoryA->id, 'is_active' => true]);
    $productB = Product::factory()->create(['category_id' => $categoryB->id, 'is_active' => true]);

    $this->get(route('products.all', ['category' => $categoryA->uid]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.uid', $productA->uid)
        );
});

it('filters products by search keyword', function () {
    $category = Category::factory()->create();
    $watch = Product::factory()->create(['name' => 'Gold Watch', 'category_id' => $category->id, 'is_active' => true]);
    $ring = Product::factory()->create(['name' => 'Silver Ring', 'category_id' => $category->id, 'is_active' => true]);

    $this->get(route('products.all', ['search' => 'Watch']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Gold Watch')
        );
});

it('filters products by price range', function () {
    $category = Category::factory()->create();
    $cheap = Product::factory()->create(['price' => 50, 'category_id' => $category->id, 'is_active' => true]);
    $expensive = Product::factory()->create(['price' => 500, 'category_id' => $category->id, 'is_active' => true]);

    $this->get(route('products.all', ['min_price' => 100, 'max_price' => 600]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.uid', $expensive->uid)
        );
});

it('redirects /shop to /all-products', function () {
    $this->get('/shop')->assertRedirect(route('products.all'));
});

it('supports customizable per_page pagination', function () {
    $category = Category::factory()->create();
    Product::factory()->count(15)->create([
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $this->get(route('products.all', ['per_page' => 8]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 8)
            ->where('products.per_page', 8)
            ->where('products.total', 15)
        );

    $this->get(route('products.all', ['per_page' => 24]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 15)
            ->where('products.per_page', 24)
        );
});
