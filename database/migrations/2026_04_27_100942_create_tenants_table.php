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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->text('footer_text')->nullable();
            $table->boolean('printer_use_default')->default(true);
            $table->string('kitchen_printer_connection_type')->nullable();
            $table->string('kitchen_printer_address')->nullable();
            $table->integer('kitchen_printer_port')->nullable();
            $table->string('printer_connection_type')->nullable();
            $table->string('printer_address')->nullable();
            $table->unsignedInteger('printer_port')->nullable();
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
