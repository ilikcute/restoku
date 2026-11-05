<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('cash_amount', 15, 2)->default(0)->after('payment_method');
            $table->decimal('non_cash_amount', 15, 2)->default(0)->after('cash_amount');
        });

        // Backfill existing data
        DB::table('orders')->where('payment_method', 'cash')->update([
            'cash_amount' => DB::raw('total_amount'),
            'non_cash_amount' => 0,
        ]);

        DB::table('orders')->where('payment_method', '!=', 'cash')->update([
            'cash_amount' => 0,
            'non_cash_amount' => DB::raw('total_amount'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cash_amount', 'non_cash_amount']);
        });
    }
};
