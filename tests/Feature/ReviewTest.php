<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createAdminRole(): void
{
    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(ApplicationPermissions::all());
}

it('requires authentication to store review', function () {
    $product = Product::factory()->create();

    $this->post('/reviews', [
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Great product!',
    ])->assertRedirect('/login');
});

it('requires authentication to update review', function () {
    $review = Review::factory()->create();

    $this->put("/reviews/{$review->id}", [
        'rating' => 4,
        'comment' => 'Updated',
    ])->assertRedirect('/login');
});

it('requires authentication to delete review', function () {
    $review = Review::factory()->create();

    $this->delete("/reviews/{$review->id}")->assertRedirect('/login');
});

it('can store a review', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Excellent product!',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Excellent product!',
    ]);
});

it('can store a review without a comment', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 3,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 3,
    ]);
});

it('validates required fields when storing review', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/reviews', [])
        ->assertSessionHasErrors('product_id', 'rating');
});

it('validates rating range', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 6,
        ])
        ->assertSessionHasErrors('rating');

    $this->actingAs($user)
        ->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 0,
        ])
        ->assertSessionHasErrors('rating');
});

it('validates product exists when storing review', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/reviews', [
            'product_id' => 9999,
            'rating' => 5,
        ])
        ->assertSessionHasErrors('product_id');
});

it('prevents duplicate reviews from same user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'First review',
    ]);

    $this->actingAs($user)
        ->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Second review',
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('reviews', 1);
});

it('can update own review', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->put("/reviews/{$review->id}", [
            'rating' => 2,
            'comment' => 'Changed my mind',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'rating' => 2,
        'comment' => 'Changed my mind',
    ]);
});

it('cannot update another users review', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->put("/reviews/{$review->id}", [
            'rating' => 1,
            'comment' => 'Hacked',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'rating' => $review->rating,
    ]);
});

it('can delete own review', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete("/reviews/{$review->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

it('cannot delete another users review', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->delete("/reviews/{$review->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('reviews', ['id' => $review->id]);
});

it('passes reviews to product page', function () {
    $product = Product::factory()->create(['is_active' => true]);
    $user = User::factory()->create();

    Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Amazing!',
        'status' => 'approved',
    ]);

    $this->actingAs($user)
        ->get("/products/{$product->uid}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('reviews'));
});

it('passes user review to product page', function () {
    $product = Product::factory()->create(['is_active' => true]);
    $user = User::factory()->create();

    Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 4,
        'comment' => 'Good stuff',
        'status' => 'approved',
    ]);

    $this->actingAs($user)
        ->get("/products/{$product->uid}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('userReview'));
});

it('shows null userReview when user has not reviewed', function () {
    $product = Product::factory()->create(['is_active' => true]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get("/products/{$product->uid}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('userReview', null));
});

it('requires authentication to access admin reviews', function () {
    $this->get('/reviews')->assertRedirect('/login');
});

it('requires permission to access admin reviews', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/reviews')
        ->assertForbidden();
});

it('admin can view reviews index', function () {
    createAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/reviews')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('reviews'));
});

it('admin can approve a review', function () {
    createAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');
    $review = Review::factory()->create(['status' => 'pending']);

    $this->actingAs($user)
        ->post("/reviews/{$review->id}/approve")
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'status' => 'approved',
    ]);
});

it('admin can reject a review', function () {
    createAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');
    $review = Review::factory()->create(['status' => 'pending']);

    $this->actingAs($user)
        ->post("/reviews/{$review->id}/reject")
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'status' => 'rejected',
    ]);
});

it('admin can delete a review', function () {
    createAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');
    $review = Review::factory()->create();

    $this->actingAs($user)
        ->delete("/reviews/{$review->id}/admin")
        ->assertRedirect();

    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

it('store sets review status to pending', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => 'pending',
    ]);
});

it('only shows approved reviews on product page', function () {
    $product = Product::factory()->create(['is_active' => true]);
    $user = User::factory()->create();

    Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Pending review',
        'status' => 'pending',
    ]);

    $this->actingAs(User::factory()->create())
        ->get("/products/{$product->uid}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('reviews', []));
});
