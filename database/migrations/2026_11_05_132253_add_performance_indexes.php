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
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['tenant_id', 'status', 'created_at'], 'orders_tenant_status_created_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['tenant_id', 'product_id'], 'stock_movements_tenant_product_idx');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['tenant_id', 'type', 'transaction_date'], 'transactions_tenant_type_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_tenant_status_created_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_tenant_product_idx');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_tenant_type_date_idx');
        });
    }
};
