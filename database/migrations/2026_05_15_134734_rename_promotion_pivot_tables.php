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
        Schema::rename('promotion_product', 'promotion_products');
        Schema::rename('promotion_category', 'promotion_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('promotion_products', 'promotion_product');
        Schema::rename('promotion_categories', 'promotion_category');
    }
};
