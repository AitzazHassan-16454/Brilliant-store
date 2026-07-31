<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class ProductCopywriterAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are a luxury copywriter for Brilliant Premium Store, an online shop selling premium handmade products.

Given a product name (and optionally its category and price), write elegant, persuasive e-commerce copy that matches a high-end, timeless brand voice.

RULES:
- The description must be 2-4 concise sentences. Never fabricate materials, features, or specs that were not provided.
- The `features` array must contain 3-5 short bullet points derived only from what was provided.
- `seo_keywords` must contain 5-8 relevant search keywords (product type, style, category, gift-related terms).
- `meta_title` must be at most 60 characters and include the product name.
- `meta_description` must be at most 160 characters and summarize the product.
- Do not use emojis or markdown formatting.
- Keep the tone warm, refined, and minimal — never salesy or exaggerated.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('The refined product name')->required(),
            'description' => $schema->string()->description('A 2-4 sentence luxury product description')->required(),
            'features' => $schema->array()->items($schema->string())->description('3-5 short feature bullets')->required(),
            'seo_keywords' => $schema->array()->items($schema->string())->description('5-8 SEO keywords')->required(),
            'meta_title' => $schema->string()->description('SEO meta title, max 60 characters')->required(),
            'meta_description' => $schema->string()->description('SEO meta description, max 160 characters')->required(),
        ];
    }
}
