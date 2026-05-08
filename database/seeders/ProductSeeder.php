<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(InventoryService $inventoryService): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        $categories = Category::where('tenant_id', $tenant->id)->get();
        $units = Unit::where('tenant_id', $tenant->id)->get();
        $suppliers = Supplier::where('tenant_id', $tenant->id)->get();
        $admin = User::where('email', 'admin@restoku.id')->first();

        for ($i = 0; $i < 40; $i++) {
            $product = Product::factory()->create([
                'tenant_id' => $tenant->id,
                'category_id' => $categories->random()->id,
                'unit_id' => $units->random()->id,
                'supplier_id' => $suppliers->random()->id,
            ]);

            // Initialize Stock
            $inventoryService->updateStock(
                $tenant->id,
                $product->id,
                rand(50, 500),
                'initial',
                $admin->id,
                null,
                null,
                'Initial mass stock'
            );
        }
    }
}
