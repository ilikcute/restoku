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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // PIC
            $table->string('purchase_number')->unique();
            $table->date('purchase_date');

            // Financial Fields (Adopted from myresto-v2)
            $table->decimal('subtotal', 15, 2)->default(0); // total_beli_kotor
            $table->decimal('discount_amount', 15, 2)->default(0); // total_beli_diskon
            $table->decimal('tax_amount', 15, 2)->default(0); // ppn_beli
            $table->decimal('total_amount', 15, 2)->default(0); // total_beli_bersih
            $table->decimal('total_return', 15, 2)->default(0);
            $table->timestamp('return_date')->nullable();
            $table->foreignId('return_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('payment_status')->default('paid'); // paid, partial, unpaid
            $table->string('status')->default('completed'); // completed, pending, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'purchase_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
