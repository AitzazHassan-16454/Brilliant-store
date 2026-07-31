<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\SemanticSearchService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Embeddings;

#[Signature('products:embed {--force : Re-embed products that already have embeddings}')]
#[Description('Generate and store embeddings for active products')]
class EmbedProducts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SemanticSearchService $search): int
    {
        $query = Product::query()->where('is_active', true);

        if ($this->option('force')) {
            $query->whereNotNull('embedding');
        } else {
            $query->whereNull('embedding');
        }

        $products = $query->with('category')->get();

        if ($products->isEmpty()) {
            $this->info('No products need embeddings.');

            return self::SUCCESS;
        }

        $vectors = Embeddings::for(
            $products->map(fn (Product $product) => $search->textFor($product))->all()
        )->generate();

        foreach ($products as $index => $product) {
            $product->forceFill(['embedding' => $vectors->embeddings[$index] ?? null])->save();
        }

        $this->info("Embedded {$products->count()} products.");

        return self::SUCCESS;
    }
}
