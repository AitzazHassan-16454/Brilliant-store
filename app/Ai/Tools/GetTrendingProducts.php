<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetTrendingProducts implements Tool
{
    public function description(): Stringable|string
    {
        return 'List the trending products featured on the store homepage. Use this when a customer asks about trending, popular, featured, or best-selling products.';
    }

    public function handle(Request $request): Stringable|string
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('is_trending', true)
            ->orderByDesc('updated_at')
            ->get();

        if ($products->isEmpty()) {
            return 'There are no trending products right now. Check back soon or ask about a specific category.';
        }

        return json_encode([
            'products' => $products->map(fn ($p) => [
                'name' => $p->name,
                'price' => $p->price,
                'stock' => $p->stock,
                'in_stock' => $p->is_in_stock,
                'description' => $p->description,
                'category' => $p->category?->name,
                'rating' => $p->average_rating ? $p->average_rating.'/5' : 'No reviews yet',
                'uid' => $p->uid,
                'image' => $p->image,
            ])->values(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
