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
        Schema::create('order_promotions', function (Blueprint $table) {
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->primary(['order_id', 'promotion_id']);
        });

        Schema::create('order_item_promotions', function (Blueprint $table) {
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->primary(['order_item_id', 'promotion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_promotions');
        Schema::dropIfExists('order_promotions');
    }
};
