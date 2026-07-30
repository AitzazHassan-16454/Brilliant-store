<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Fashion' => [
                'Summer Cotton T-Shirt' => 'summer-cotton-tshirt.jpg',
                'Slim Fit Denim Jeans' => 'slim-fit-denim-jeans.jpg',
                'Leather Chelsea Boots' => 'leather-chelsea-boots.jpg',
                'Wool Blend Blazer' => 'wool-blend-blazer.jpg',
                'Silk Pocket Square' => 'silk-pocket-square.jpg',
                'Cashmere Crewneck Sweater' => 'cashmere-crewneck-sweater.jpg',
                'High-Waist Chinos' => 'high-waist-chinos.jpg',
                'Linen Shirt' => 'linen-shirt.jpg',
                'Suede Loafers' => 'suede-loafers.jpg',
                'Tailored Shorts' => 'tailored-shorts.jpg',
                'Striped Polo Shirt' => 'striped-polo-shirt.jpg',
                'Oversized Hoodie' => 'oversized-hoodie.jpg',
                'Puffer Jacket' => 'puffer-jacket.jpg',
                'Canvas Sneakers' => 'canvas-sneakers.jpg',
                'Graphic Tee' => 'graphic-tee.jpg',
                'Cargo Pants' => 'cargo-pants.jpg',
                'Ribbed Knit Cardigan' => 'ribbed-knit-cardigan.jpg',
                'Platform Sandals' => 'platform-sandals.jpg',
                'Quilted Vest' => 'quilted-vest.jpg',
                'Corduroy Trousers' => 'corduroy-trousers.jpg',
                'Denim Jacket' => 'denim-jacket.jpg',
                'Athleisure Joggers' => 'athleisure-joggers.jpg',
                'Merino Wool Beanie' => 'merino-wool-beanie.jpg',
                'Patent Leather Belt' => 'patent-leather-belt.jpg',
                'Rain Shell Anorak' => 'rain-shell-anorak.jpg',
            ],
            'Electronic' => [
                'Wireless Noise-Cancelling Headphones' => 'wireless-noise-cancelling-headphones.jpg',
                'Mechanical Gaming Keyboard' => 'mechanical-gaming-keyboard.jpg',
                '4K Ultra HD Monitor' => '4k-ultra-hd-monitor.jpg',
                'Portable Bluetooth Speaker' => 'portable-bluetooth-speaker.jpg',
                'USB-C Docking Station' => 'usb-c-docking-station.jpg',
                'Smart LED Desk Lamp' => 'smart-led-desk-lamp.jpg',
                'Wireless Charging Pad' => 'wireless-charging-pad.jpg',
                'External SSD 1TB' => 'external-ssd-1tb.jpg',
                'Webcam 1080p HDR' => 'webcam-1080p-hdr.jpg',
                'Gaming Mouse' => 'gaming-mouse.jpg',
                'Mini Projector' => 'mini-projector.jpg',
                'Smart Power Strip' => 'smart-power-strip.jpg',
                'Action Camera 4K' => 'action-camera-4k.jpg',
                'Wireless Earbuds' => 'wireless-earbuds.jpg',
                'Laptop Cooling Pad' => 'laptop-cooling-pad.jpg',
                'USB Microphone' => 'usb-microphone.jpg',
                'E-Reader Tablet' => 'e-reader-tablet.jpg',
                'Robot Vacuum' => 'robot-vacuum.jpg',
                'Smart Thermostat' => 'smart-thermostat.jpg',
                'Digital Drawing Tablet' => 'digital-drawing-tablet.jpg',
                'Mesh Wi-Fi Router' => 'mesh-wi-fi-router.jpg',
                'Portable Monitor' => 'portable-monitor.jpg',
                'Fitness Tracker Band' => 'fitness-tracker-band.jpg',
                'Smart Doorbell Camera' => 'smart-doorbell-camera.jpg',
                'Electric Standing Desk' => 'electric-standing-desk.jpg',
            ],
        ]);

        $subcategoryMap = [
            'Fashion' => [
                'Summer Cotton T-Shirt' => 'T-Shirts',
                'Striped Polo Shirt' => 'T-Shirts',
                'Graphic Tee' => 'T-Shirts',
                'Linen Shirt' => 'T-Shirts',
                'Slim Fit Denim Jeans' => 'Jeans',
                'Corduroy Trousers' => 'Jeans',
                'Cargo Pants' => 'Jeans',
                'High-Waist Chinos' => 'Jeans',
                'Canvas Sneakers' => 'Sneakers',
                'Leather Chelsea Boots' => 'Sneakers',
                'Suede Loafers' => 'Sneakers',
                'Platform Sandals' => 'Sneakers',
                'Wool Blend Blazer' => 'Jackets',
                'Puffer Jacket' => 'Jackets',
                'Denim Jacket' => 'Jackets',
                'Rain Shell Anorak' => 'Jackets',
                'Cashmere Crewneck Sweater' => 'Jackets',
                'Ribbed Knit Cardigan' => 'Jackets',
                'Quilted Vest' => 'Jackets',
                'Oversized Hoodie' => 'Jackets',
                'Tailored Shorts' => 'Dresses',
                'Silk Pocket Square' => 'Hats',
                'Merino Wool Beanie' => 'Hats',
                'Patent Leather Belt' => 'Accessories',
                'Athleisure Joggers' => 'Dresses',
            ],
            'Electronic' => [
                'Wireless Noise-Cancelling Headphones' => 'Headphones',
                'Wireless Earbuds' => 'Headphones',
                'USB Microphone' => 'Accessories',
                '4K Ultra HD Monitor' => 'Laptops',
                'Portable Monitor' => 'Laptops',
                'Mechanical Gaming Keyboard' => 'Accessories',
                'Gaming Mouse' => 'Accessories',
                'Laptop Cooling Pad' => 'Accessories',
                'Electric Standing Desk' => 'Accessories',
                'Portable Bluetooth Speaker' => 'Speakers',
                'USB-C Docking Station' => 'Accessories',
                'Smart LED Desk Lamp' => 'Accessories',
                'Wireless Charging Pad' => 'Accessories',
                'External SSD 1TB' => 'Accessories',
                'Webcam 1080p HDR' => 'Accessories',
                'Mini Projector' => 'Accessories',
                'Smart Power Strip' => 'Accessories',
                'Action Camera 4K' => 'Cameras',
                'Smart Doorbell Camera' => 'Cameras',
                'E-Reader Tablet' => 'Tablets',
                'Digital Drawing Tablet' => 'Tablets',
                'Robot Vacuum' => 'Smartwatches',
                'Smart Thermostat' => 'Smartwatches',
                'Mesh Wi-Fi Router' => 'Smartphones',
                'Fitness Tracker Band' => 'Smartwatches',
            ],
        ];

        $descriptions = [
            'Fashion' => 'Curated apparel and accessories crafted for timeless style and everyday luxury.',
            'Electronic' => 'Premium gadgets and devices designed to elevate your digital lifestyle.',
        ];

        $categories->each(function ($products, $categoryName) use ($subcategoryMap, $descriptions) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                [
                    'uid' => 'CAT_'.strtoupper(Str::random(8)),
                    'description' => $descriptions[$categoryName] ?? null,
                ]
            );

            foreach ($products as $productName => $imageFile) {
                $subcategoryName = $subcategoryMap[$categoryName][$productName] ?? null;
                $subcategory = $subcategoryName
                    ? Subcategory::firstOrCreate(
                        ['name' => $subcategoryName, 'category_id' => $category->id],
                        ['uid' => 'SUB_'.strtoupper(Str::random(8))]
                    )
                    : null;

                Product::firstOrCreate(
                    ['name' => $productName],
                    [
                        'uid' => 'PRD_'.strtoupper(Str::random(8)),
                        'price' => fake()->randomFloat(2, 9.99, 999.99),
                        'stock' => fake()->numberBetween(0, 150),
                        'is_active' => true,
                        'image' => 'products/'.$imageFile,
                        'description' => fake()->paragraph(),
                        'category_id' => $category->id,
                        'subcategory_id' => $subcategory?->id,
                    ]
                );
            }
        });
    }
}
