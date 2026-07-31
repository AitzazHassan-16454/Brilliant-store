<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Laravel\Ai\Embeddings;
use Throwable;

class SemanticSearchService
{
    /**
     * Build the text that represents a product for embedding purposes.
     */
    public function textFor(Product $product): string
    {
        return trim(implode(' ', array_filter([
            $product->name,
            $product->description,
            optional($product->category)->name,
            optional($product->subcategory)->name,
        ])));
    }

    /**
     * Generate the embedding vector for a single piece of text.
     *
     * @return array<int, float>
     */
    public function embedText(string $text): array
    {
        return Embeddings::for([$text])->generate()->first();
    }

    /**
     * Rank active products by semantic similarity to the given query.
     *
     * When a category ID is given, only products in that category are ranked.
     * Returns the ordered product IDs, or null when embeddings are
     * unavailable so the caller can fall back to a keyword search.
     *
     * @return array<int, int>|null
     */
    public function rankedIds(string $query, ?int $categoryId = null): ?array
    {
        try {
            $queryVector = $this->embedText($query);
        } catch (Throwable) {
            return null;
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereNotNull('embedding')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->get(['id', 'embedding']);

        if ($products->isEmpty()) {
            return null;
        }

        return $products
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'score' => $this->cosineSimilarity(
                    $queryVector,
                    is_array($product->embedding) ? $product->embedding : [],
                ),
            ])
            ->sortByDesc('score')
            ->values()
            ->pluck('id')
            ->all();
    }

    /**
     * Rank a given collection of products by similarity to a query vector.
     */
    public function rank(Collection $products, array $queryVector): Collection
    {
        return $products
            ->map(fn (Product $product) => [
                'product' => $product,
                'score' => $this->cosineSimilarity($queryVector, $product->embedding ?? []),
            ])
            ->sortByDesc('score')
            ->values()
            ->map(fn (array $item) => $item['product']);
    }

    /**
     * Compute the cosine similarity between two vectors.
     *
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $dot += $value * ($b[$i] ?? 0);
            $normA += $value * $value;
        }

        foreach ($b as $value) {
            $normB += $value * $value;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
