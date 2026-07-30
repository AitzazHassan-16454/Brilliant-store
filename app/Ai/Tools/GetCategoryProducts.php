<?php

namespace App\Ai\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCategoryProducts implements Tool
{
    public function description(): Stringable|string
    {
        return 'List all products available in a given category. Use this when a customer asks about products in a specific category.';
    }

    public function handle(Request $request): Stringable|string
    {
        $category = Category::where('name', 'LIKE', '%'.$request['category'].'%')
            ->with(['products' => function ($q) {
                $q->where('is_active', true)->limit(20);
            }])
            ->first();

        if (! $category) {
            $categories = Category::pluck('name');

            return 'Category not found. Available categories: '.$categories->implode(', ');
        }

        if ($category->products->isEmpty()) {
            return 'The "'.$category->name.'" category currently has no products listed.';
        }

        $products = $category->products->map(fn ($p) => [
            'name' => $p->name,
            'price' => $p->price,
            'in_stock' => $p->is_in_stock,
            'uid' => $p->uid,
            'image' => $p->image,
            'slug' => $p->uid,
        ])->values();

        return json_encode([
            'category' => $category->name,
            'product_count' => $products->count(),
            'products' => $products,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('The category name to search for products in')->required(),
        ];
    }
}
