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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique();
            $table->string('idempotency_key')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('table_number')->nullable();

            // Financial Fields (Adopted from myresto-v2)
            $table->decimal('subtotal', 15, 2)->default(0); // total_kotor
            $table->decimal('discount_amount', 15, 2)->default(0); // total_diskon
            $table->decimal('tax_amount', 15, 2)->default(0); // pajak_jual / ppn_jual
            $table->decimal('service_charge', 15, 2)->default(0); // service_jual
            $table->decimal('rounding', 15, 2)->default(0); // pembulatan_jual
            $table->decimal('total_amount', 15, 2)->default(0); // total_jual
            $table->decimal('total_return', 15, 2)->default(0);
            $table->timestamp('return_date')->nullable();
            $table->foreignId('return_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Payment Details
            $table->string('payment_method')->default('cash'); // cash, debit, credit, e-wallet
            $table->decimal('paid_amount', 15, 2)->default(0); // bayar_tunai / total bayar
            $table->decimal('change_amount', 15, 2)->default(0); // kembali

            $table->string('status')->default('completed'); // completed, pending, cancelled
            $table->boolean('is_synced_to_dpkad')->default(false);
            $table->timestamp('synced_to_dpkad_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'idempotency_key']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
