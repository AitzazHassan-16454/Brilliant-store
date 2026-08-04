<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;

it('requires authentication to view wishlist', function () {
    $this->get('/wishlist')->assertRedirect('/login');
});

it('requires authentication to toggle wishlist', function () {
    $product = Product::factory()->create();

    $this->post('/wishlist', ['product_id' => $product->id])->assertRedirect('/login');
});

it('requires authentication to remove from wishlist', function () {
    $wishlist = Wishlist::factory()->create();

    $this->delete("/wishlist/{$wishlist->id}")->assertRedirect('/login');
});

it('shows wishlist page for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/wishlist')
        ->assertSuccessful();
});

it('can add product to wishlist', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post('/wishlist', ['product_id' => $product->id])
        ->assertRedirect();

    $this->assertDatabaseHas('wishlists', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

it('can remove product from wishlist via toggle', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Wishlist::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($user)
        ->post('/wishlist', ['product_id' => $product->id])
        ->assertRedirect();

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

it('can remove wishlist item by id', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete("/wishlist/{$wishlist->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
});

it('cannot remove another users wishlist item', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $wishlist = Wishlist::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->delete("/wishlist/{$wishlist->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('wishlists', ['id' => $wishlist->id]);
});

it('validates product exists when toggling', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/wishlist', ['product_id' => 9999])
        ->assertSessionHasErrors('product_id');
});

it('passes wishlist data to home page', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);

    Wishlist::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('wishlistProductIds'));
});

it('shares wishlist count in shared props', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Wishlist::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('wishlistCount', 1));
});
