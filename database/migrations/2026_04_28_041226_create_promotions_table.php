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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // The text to be displayed in the marquee
            $table->text('content')->nullable();
            $table->string('type')->default('announcement'); // announcement, discount_percentage, discount_fixed, buy_x_get_y
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('min_purchase', 15, 2)->default(0);
            $table->decimal('max_discount', 15, 2)->nullable();
            $table->string('applicable_type')->default('all'); // all, products, categories
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_multiple')->default(true);
            $table->json('requirement_data')->nullable(); // For buy_x_get_y data
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
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
