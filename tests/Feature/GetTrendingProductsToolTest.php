<?php

use App\Ai\Agents\OrderSupportAgent;
use App\Ai\Tools\GetTrendingProducts;
use App\Models\Category;
use App\Models\Product;
use Laravel\Ai\Tools\Request;

it('returns the products the admin flagged as trending, newest first', function () {
    $category = Category::factory()->create(['name' => 'Watches']);

    $first = Product::factory()->create([
        'name' => 'First Pick',
        'category_id' => $category->id,
        'is_trending' => true,
        'updated_at' => now()->subMinutes(2),
    ]);

    $second = Product::factory()->create([
        'name' => 'Second Pick',
        'category_id' => $category->id,
        'is_trending' => true,
        'updated_at' => now()->subMinute(),
    ]);

    $response = json_decode((string) (new GetTrendingProducts)->handle(new Request([])), true);

    expect($response['products'])->toHaveCount(2)
        ->and($response['products'][0]['name'])->toBe('Second Pick')
        ->and($response['products'][1]['name'])->toBe('First Pick')
        ->and($response['products'][0]['uid'])->toBe($second->uid);
});

it('excludes products that are not flagged as trending or are inactive', function () {
    Product::factory()->create(['name' => 'Not Flagged', 'is_trending' => false, 'is_active' => true]);
    Product::factory()->create(['name' => 'Flagged But Inactive', 'is_trending' => true, 'is_active' => false]);

    $response = (string) (new GetTrendingProducts)->handle(new Request([]));

    expect($response)->toContain('no trending products right now')
        ->not->toContain('Not Flagged')
        ->not->toContain('Flagged But Inactive');
});

it('returns a friendly message when there are no trending products', function () {
    $response = (string) (new GetTrendingProducts)->handle(new Request([]));

    expect($response)->toContain('no trending products right now');
});

it('is registered on the order support agent', function () {
    $agent = new OrderSupportAgent;

    $toolClasses = collect($agent->tools())->map(fn ($tool) => $tool::class);

    expect($toolClasses)->toContain(GetTrendingProducts::class);
});
