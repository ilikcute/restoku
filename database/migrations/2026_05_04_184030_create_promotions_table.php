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
        Schema::create('promotions', function (Blueprint $header) {
            $header->id();
            $header->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $header->string('title'); // The text to be displayed in the marquee
            $header->text('content')->nullable();
            $header->string('type')->default('announcement'); // announcement, discount_percentage, discount_fixed, buy_x_get_y
            $header->decimal('discount_value', 15, 2)->default(0);
            $header->decimal('min_purchase', 15, 2)->default(0);
            $header->decimal('max_discount', 15, 2)->nullable();
            $header->string('applicable_type')->default('all'); // all, products, categories
            $header->json('requirement_data')->nullable(); // For buy_x_get_y data
            $header->boolean('is_active')->default(true);
            $header->date('start_date')->nullable();
            $header->date('end_date')->nullable();
            $header->integer('priority')->default(0);
            $header->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
