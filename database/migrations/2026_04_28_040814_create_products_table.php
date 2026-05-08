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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('slug');
            $table->string('brand_name')->nullable();
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();

            // Pricing (Adopted from myresto-v2)
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0); // harga_jual
            $table->decimal('discount_amount', 15, 2)->default(0); // diskon_jual

            $table->decimal('ojol_price', 15, 2)->default(0); // harga_ojol
            $table->decimal('ojol_discount', 15, 2)->default(0); // diskon_ojol

            $table->decimal('wholesale_price', 15, 2)->default(0); // harga_grosir
            $table->decimal('wholesale_discount', 15, 2)->default(0); // diskon_grosir

            $table->decimal('tax_rate', 5, 2)->default(0); // pajak_barang (percentage)
            $table->decimal('service_charge_rate', 5, 2)->default(0); // service_charge_barang (percentage)

            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('stock_type')->default('storable'); // storable, service
            $table->decimal('minimum_stock', 15, 2)->default(0);
            $table->decimal('maximum_stock', 15, 2)->default(0);
            $table->decimal('reorder_quantity', 15, 2)->default(0);
            $table->decimal('safety_stock', 15, 2)->default(0);
            $table->integer('lead_time')->default(0); // in days
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'code']);
            $table->index(['code', 'barcode']);

            $table->unique(['tenant_id', 'code']);
            $table->unique(['tenant_id', 'slug']);
            $table->unique(['tenant_id', 'barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
