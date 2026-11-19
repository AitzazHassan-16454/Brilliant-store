<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BusinessSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetDir = storage_path('app/public/branding');
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $sourceFile = public_path('images/logo.png');
        $targetFile = $targetDir.'/logo.png';

        if (File::exists($sourceFile) && ! File::exists($targetFile)) {
            File::copy($sourceFile, $targetFile);
        }

        BusinessSetting::updateOrCreate(
            ['id' => 1],
            [
                'site_name' => 'GharBazaar',
                'tagline' => 'Shop Smart • Live Better',
                'logo' => 'branding/logo.png',
                'primary_color' => '#D4AF37',
                'accent_color' => '#B8960F',
                'contact_email' => 'info@gharbazaar.com',
                'contact_hours' => 'Mon - Fri: 9:00 AM - 6:00 PM',
                'footer_description' => 'GharBazaar — Shop Smart, Live Better. Everyday essentials and curated collections delivered to your doorstep.',
            ]
        );
    }
}
