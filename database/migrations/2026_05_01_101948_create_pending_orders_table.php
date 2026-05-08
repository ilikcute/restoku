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
        Schema::create('pending_orders', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique(); // For the QR Code
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('customer_name')->nullable();
            $table->string('table_number')->nullable();
            $table->json('items'); // [{product_id, quantity, notes}]
            $table->enum('status', ['pending', 'processed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_orders');
    }
};
