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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // PIC
            $table->string('adjustment_number')->unique();
            $table->date('adjustment_date');
            $table->string('status', 1)->default('I'); // I: Investigation, D: Done/Double-check, A: Adjusted
            $table->text('notes')->nullable();
            $table->decimal('total_loss_amount', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'adjustment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
