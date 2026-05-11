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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('kitchen_printer_connection_type')->nullable()->after('printer_use_default');
            $table->string('kitchen_printer_address')->nullable()->after('kitchen_printer_connection_type');
            $table->integer('kitchen_printer_port')->nullable()->after('kitchen_printer_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['kitchen_printer_connection_type', 'kitchen_printer_address', 'kitchen_printer_port']);
        });
    }
};
