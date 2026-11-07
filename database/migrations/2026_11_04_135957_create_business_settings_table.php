<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Brilliant');
            $table->string('tagline')->nullable()->default('Premium Store');
            $table->string('logo')->nullable();
            $table->string('primary_color', 7)->default('#D4AF37');
            $table->string('accent_color', 7)->default('#B8960F');
            $table->string('contact_email')->nullable()->default('info@brilliantstore.com');
            $table->string('contact_hours')->nullable()->default('Mon - Fri: 9:00 AM - 6:00 PM');
            $table->string('footer_description')->nullable()->default('Premium products crafted with precision and passion.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
