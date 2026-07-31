<?php

use App\Models\Category;
use App\Models\Product;
use App\Services\SemanticSearchService;
use Laravel\Ai\Embeddings;

it('embeds products that do not have embeddings yet', function () {
    $productA = Product::factory()->create(['embedding' => null]);
    $productB = Product::factory()->create(['embedding' => null]);

    Embeddings::fake([
        [[0.1, 0.2, 0.3], [0.4, 0.5, 0.6]],
    ]);

    $this->artisan('products:embed')
        ->expectsOutputToContain('Embedded 2 products.')
        ->assertSuccessful();

    expect($productA->fresh()->embedding)->toBe([0.1, 0.2, 0.3])
        ->and($productB->fresh()->embedding)->toBe([0.4, 0.5, 0.6]);

    Embeddings::assertGenerated(fn ($prompt) => count($prompt->inputs) === 2);
});

it('skips products that already have embeddings', function () {
    Product::factory()->create(['embedding' => [0.1, 0.2]]);

    Embeddings::fake();

    $this->artisan('products:embed')
        ->expectsOutputToContain('No products need embeddings.')
        ->assertSuccessful();

    Embeddings::assertNothingGenerated();
});

it('re-embeds products with the force option', function () {
    $product = Product::factory()->create(['embedding' => [0.1, 0.2]]);

    Embeddings::fake([
        [[0.9, 0.8, 0.7]],
    ]);

    $this->artisan('products:embed --force')->assertSuccessful();

    expect($product->fresh()->embedding)->toBe([0.9, 0.8, 0.7]);
});

it('ranks products by semantic similarity', function () {
    $productA = Product::factory()->create(['embedding' => [0, 1, 0]]);
    $productB = Product::factory()->create(['embedding' => [1, 0, 0]]);

    $service = new SemanticSearchService;
    $ranked = $service->rank(collect([$productA, $productB]), [1, 0, 0]);

    expect($ranked->first()->id)->toBe($productB->id);
});

it('uses semantic search to rank home page results', function () {
    $boots = Product::factory()->create([
        'name' => 'Leather Chelsea Boots',
        'embedding' => [0.9, 0.1, 0.0],
    ]);
    $watch = Product::factory()->create([
        'name' => 'Gold Watch',
        'embedding' => [0.0, 0.0, 1.0],
    ]);
    $ring = Product::factory()->create([
        'name' => 'Diamond Ring',
        'embedding' => [0.2, 0.0, 0.8],
    ]);

    Embeddings::fake(fn () => [[1.0, 0.0, 0.0]]);

    $this->get('/?search=brown leather shoes')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 3)
            ->where('products.data.0.name', 'Leather Chelsea Boots')
            ->where('products.data.1.name', 'Diamond Ring')
            ->where('products.data.2.name', 'Gold Watch')
        );

    Embeddings::assertGenerated(fn ($prompt) => $prompt->inputs === ['brown leather shoes']);
});

it('falls back to keyword search when no products have embeddings', function () {
    Product::factory()->create([
        'name' => 'Leather Chelsea Boots',
        'embedding' => null,
    ]);
    Product::factory()->create([
        'name' => 'Gold Ring',
        'embedding' => null,
    ]);

    Embeddings::fake();

    $this->get('/?search=Boots')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Leather Chelsea Boots')
        );
});

it('ranks category page results semantically and scopes them to the category', function () {
    $fashion = Category::factory()->create(['name' => 'Fashion']);
    $electronics = Category::factory()->create(['name' => 'Electronics']);

    $boots = Product::factory()->create([
        'category_id' => $fashion->id,
        'name' => 'Leather Chelsea Boots',
        'embedding' => [0.9, 0.1, 0.0],
    ]);
    Product::factory()->create([
        'category_id' => $electronics->id,
        'name' => 'Noise Cancelling Headphones',
        'embedding' => [0.9, 0.0, 0.1],
    ]);
    Product::factory()->create([
        'category_id' => $fashion->id,
        'name' => 'Summer Cotton T-Shirt',
        'embedding' => [0.0, 0.0, 1.0],
    ]);

    Embeddings::fake(fn () => [[1.0, 0.0, 0.0]]);

    $this->get("/categories/{$fashion->uid}?search=brown shoes")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 2)
            ->where('products.data.0.name', 'Leather Chelsea Boots')
            ->where('products.data.1.name', 'Summer Cotton T-Shirt')
        );
});
