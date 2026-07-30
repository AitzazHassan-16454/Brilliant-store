<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetProductInfo implements Tool
{
    private array $synonyms = [
        'shoe' => ['sneakers', 'boots', 'loafers', 'sandals', 'trainers', 'footwear'],
        'shoes' => ['sneakers', 'boots', 'loafers', 'sandals', 'trainers', 'footwear'],
        'pant' => ['jeans', 'chinos', 'trousers', 'joggers', 'pants'],
        'pants' => ['jeans', 'chinos', 'trousers', 'joggers', 'pants'],
        'shirt' => ['t-shirt', 'tshirt', 'tee', 'polo', 'blouse'],
        'jacket' => ['coat', 'hoodie', 'blazer', 'vest', 'anorak'],
        'bag' => ['backpack', 'handbag', 'tote', 'satchel'],
        'hat' => ['cap', 'beanie', 'beret'],
        'dress' => ['gown', 'frock', 'skirt'],
    ];

    public function description(): Stringable|string
    {
        return 'Search for products by name, keyword, or ID. Returns multiple matching products with details. Broadens search terms automatically (e.g. "shoes" also searches for sneakers, boots, loafers).';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $request['query'];

        if (is_numeric($query)) {
            $product = Product::with('category')->find((int) $query);

            if (! $product) {
                return 'No product found with ID '.$query.'.';
            }

            return json_encode([
                'products' => [$this->formatProduct($product)],
            ]);
        }

        $terms = $this->expandTerms($query);
        $products = Product::with('category')
            ->where('is_active', true)
            ->where(function ($q) use ($terms) {
                foreach ($terms as $i => $term) {
                    $safe = str_replace(['%', '_'], ['\%', '\_'], $term);
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method}(function ($sq) use ($safe) {
                        $sq->where('name', 'LIKE', '%'.$safe.'%')
                            ->orWhere('description', 'LIKE', '%'.$safe.'%')
                            ->orWhereHas('category', fn ($cq) => $cq->where('name', 'LIKE', '%'.$safe.'%'));
                    });
                }
            })
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            return 'No products found matching "'.$query.'".';
        }

        return json_encode([
            'query' => $query,
            'products' => $products->map(fn ($p) => $this->formatProduct($p))->values(),
        ]);
    }

    protected function formatProduct($product): array
    {
        return [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
            'in_stock' => $product->is_in_stock,
            'description' => $product->description,
            'category' => $product->category?->name,
            'rating' => $product->average_rating ? $product->average_rating.'/5' : 'No reviews yet',
            'uid' => $product->uid,
            'image' => $product->image,
        ];
    }

    protected function expandTerms(string $query): array
    {
        $lower = mb_strtolower(trim($query));

        if (isset($this->synonyms[$lower])) {
            return array_merge([$query], $this->synonyms[$lower]);
        }

        foreach ($this->synonyms as $key => $syns) {
            if (str_contains($lower, $key)) {
                return array_merge([$query], $syns);
            }
        }

        return [$query];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The product name, keyword, or ID to search for')->required(),
        ];
    }
}
