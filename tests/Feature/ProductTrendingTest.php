<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createTrendingAdminRole(): void
{
    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(ApplicationPermissions::all());
}

function trendingProduct(array $overrides = []): Product
{
    return Product::factory()->create(array_merge([
        'category_id' => Category::factory(),
    ], $overrides));
}

it('requires authentication to toggle trending', function () {
    $product = trendingProduct();

    $this->post("/products/{$product->uid}/toggle-trending")->assertRedirect('/login');
});

it('requires the products.update permission to toggle trending', function () {
    $product = trendingProduct();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/products/{$product->uid}/toggle-trending")
        ->assertForbidden();
});

it('allows an authorized admin to toggle a product into trending', function () {
    createTrendingAdminRole();
    $product = trendingProduct(['is_trending' => false]);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->post("/products/{$product->uid}/toggle-trending")
        ->assertRedirect(route('products.index'));

    expect($product->fresh()->is_trending)->toBeTrue();
});

it('allows an authorized admin to toggle a product out of trending', function () {
    createTrendingAdminRole();
    $product = trendingProduct(['is_trending' => true]);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->post("/products/{$product->uid}/toggle-trending")
        ->assertRedirect(route('products.index'));

    expect($product->fresh()->is_trending)->toBeFalse();
});

it('only shows flagged active products in the homepage trending section', function () {
    $flagged = trendingProduct(['is_trending' => true, 'is_active' => true]);
    trendingProduct(['is_trending' => true, 'is_active' => false]);
    trendingProduct(['is_trending' => false, 'is_active' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Home', false)
            ->has('trendingProducts', 1)
            ->where('trendingProducts.0.uid', $flagged->uid)
        );
});

it('persists is_trending when updating a product through the edit form', function () {
    createTrendingAdminRole();
    $product = trendingProduct(['is_trending' => false]);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->put("/products/{$product->uid}", [
        'name' => $product->name,
        'price' => $product->price,
        'stock' => $product->stock,
        'is_active' => true,
        'is_trending' => true,
        'category_id' => $product->category_id,
    ])->assertRedirect(route('products.index'));

    expect($product->fresh()->is_trending)->toBeTrue();
});

it('persists is_trending when creating a product', function () {
    createTrendingAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->post(route('products.store'), [
        'name' => 'New Trending Product',
        'price' => 100,
        'stock' => 5,
        'is_active' => true,
        'is_trending' => true,
        'category_id' => Category::factory()->create()->id,
        'image' => UploadedFile::fake()->image('product.jpg'),
    ])->assertRedirect(route('products.index'));

    expect(Product::where('name', 'New Trending Product')->first()->is_trending)->toBeTrue();
});
