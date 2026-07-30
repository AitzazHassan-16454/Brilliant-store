<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('total');
            $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            $table->string('shipping_name')->nullable()->after('discount');
            $table->string('shipping_email')->nullable()->after('shipping_name');
            $table->string('shipping_phone')->nullable()->after('shipping_email');
            $table->text('shipping_address')->nullable()->after('shipping_phone');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_postal_code')->nullable()->after('shipping_city');
            $table->string('payment_method')->nullable()->after('shipping_postal_code');
            $table->text('notes')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal', 'discount', 'shipping_name', 'shipping_email',
                'shipping_phone', 'shipping_address', 'shipping_city',
                'shipping_postal_code', 'payment_method', 'notes',
            ]);
        });
    }
};
