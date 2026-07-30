<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $subcategories = [
            'Fashion' => [
                'T-Shirts',
                'Jeans',
                'Sneakers',
                'Jackets',
                'Dresses',
                'Watches',
                'Sunglasses',
                'Hats',
            ],
            'Electronic' => [
                'Smartphones',
                'Laptops',
                'Headphones',
                'Cameras',
                'Tablets',
                'Smartwatches',
                'Speakers',
                'Accessories',
            ],
        ];

        foreach ($subcategories as $categoryName => $names) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($names as $name) {
                Subcategory::create([
                    'uid' => 'SUB_'.strtoupper(Str::random(8)),
                    'category_id' => $category->id,
                    'name' => $name,
                ]);
            }
        }
    }
}
