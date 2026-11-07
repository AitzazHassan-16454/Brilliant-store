<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;

it('renders the category show page with active products and subcategories', function () {
    $category = Category::factory()->create(['name' => 'Luxury Timepieces']);
    $activeProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Chronograph Watch',
        'is_active' => true,
    ]);
    $inactiveProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Draft Watch',
        'is_active' => false,
    ]);

    $response = $this->get(route('categories.show', $category->uid));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Categories/Show', false)
            ->where('category.name', 'Luxury Timepieces')
            ->has('products.data', 1)
            ->where('products.data.0.uid', $activeProduct->uid)
            ->has('subcategories')
            ->has('filters')
        );
});

it('filters products within category by search term', function () {
    $category = Category::factory()->create();
    $goldWatch = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Royal Gold Watch',
        'is_active' => true,
    ]);
    $leatherStrap = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Leather Strap',
        'is_active' => true,
    ]);

    $response = $this->get(route('categories.show', [
        'category' => $category->uid,
        'search' => 'Gold',
    ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Royal Gold Watch')
        );
});

it('filters products within category by price bounds', function () {
    $category = Category::factory()->create();
    $affordable = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 50,
        'is_active' => true,
    ]);
    $premium = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 450,
        'is_active' => true,
    ]);

    $response = $this->get(route('categories.show', [
        'category' => $category->uid,
        'min_price' => 100,
        'max_price' => 500,
    ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $premium->id)
        );
});

it('filters products within category by under 50 and 250 plus price presets', function () {
    $category = Category::factory()->create();
    $under50 = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 35,
        'is_active' => true,
    ]);
    $over250 = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 300,
        'is_active' => true,
    ]);

    // Test Under $50 preset (min: 0, max: 50)
    $this->get(route('categories.show', [
        'category' => $category->uid,
        'min_price' => 0,
        'max_price' => 50,
    ]))->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $under50->id)
        );

    // Test $250 & Above preset (min: 250)
    $this->get(route('categories.show', [
        'category' => $category->uid,
        'min_price' => 250,
    ]))->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $over250->id)
        );
});

it('only displays subcategories that have active products on client side', function () {
    $category = Category::factory()->create();
    $subWithProduct = Subcategory::create([
        'uid' => 'SUB_TEST1',
        'category_id' => $category->id,
        'name' => 'Active Sub',
    ]);
    $subEmpty = Subcategory::create([
        'uid' => 'SUB_TEST2',
        'category_id' => $category->id,
        'name' => 'Empty Sub',
    ]);

    Product::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subWithProduct->id,
        'is_active' => true,
    ]);

    $response = $this->get(route('categories.show', $category->uid));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('subcategories', 1)
            ->where('subcategories.0.id', $subWithProduct->id)
        );
});
