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
        // 1. Multi-Tenant Scoped Unique Constraints & Soft Delete Indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_order_number_unique');
            $table->unique(['tenant_id', 'order_number']);
            $table->index(['tenant_id', 'deleted_at']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique('purchases_purchase_number_unique');
            $table->unique(['tenant_id', 'purchase_number']);
            $table->index(['tenant_id', 'deleted_at']);
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropUnique('stock_adjustments_adjustment_number_unique');
            $table->unique(['tenant_id', 'adjustment_number']);
            $table->index(['tenant_id', 'deleted_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_transaction_number_unique');
            $table->unique(['tenant_id', 'transaction_number']);
            $table->index(['tenant_id', 'deleted_at']);
        });

        // 2. Quantity column decimal alignment (supporting fractional portions/weights)
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'deleted_at']);
            $table->dropUnique(['tenant_id', 'transaction_number']);
            $table->unique('transaction_number');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'deleted_at']);
            $table->dropUnique(['tenant_id', 'adjustment_number']);
            $table->unique('adjustment_number');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'deleted_at']);
            $table->dropUnique(['tenant_id', 'purchase_number']);
            $table->unique('purchase_number');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'deleted_at']);
            $table->dropUnique(['tenant_id', 'order_number']);
            $table->unique('order_number');
        });
    }
};
