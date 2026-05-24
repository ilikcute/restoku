<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;

class TokoRedjekiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load JSON data from the seeders/data directory
        $jsonPath = database_path('seeders/data/toko_redjeki.json');
        $data = json_decode(file_get_contents($jsonPath), true);

        // ---- Tenants ----------------------------------------------------
        foreach ($data['tenants'] ?? [] as $tenantData) {
            Tenant::updateOrCreate(
                ['id' => $tenantData['id']],
                $tenantData
            );
        }

        // ---- Categories -------------------------------------------------
        foreach ($data['categories'] ?? [] as $catData) {
            $tenant = Tenant::find($catData['tenant_id']);
            if (! $tenant) {
                continue;
            }
            Category::updateOrCreate(
                ['id' => $catData['id']],
                array_merge($catData, ['tenant_id' => $tenant->id])
            );
        }

        // ---- Units ------------------------------------------------------
        foreach ($data['units'] ?? [] as $unitData) {
            $tenant = Tenant::find($unitData['tenant_id']);
            if (! $tenant) {
                continue;
            }
            Unit::updateOrCreate(
                ['id' => $unitData['id']],
                array_merge($unitData, ['tenant_id' => $tenant->id])
            );
        }

        // ---- Products ---------------------------------------------------
        foreach ($data['products'] ?? [] as $productData) {
            $tenant = Tenant::find($productData['tenant_id']);
            $category = Category::find($productData['category_id']);
            $unit = Unit::find($productData['unit_id']);
            if (! $tenant || ! $category || ! $unit) {
                continue;
            }
                        $productData = array_merge($productData, [
                'tenant_id'   => $tenant->id,
                'category_id' => $category->id,
                'unit_id'     => $unit->id,
            ]);
            // Remove the original primary key to let Laravel handle auto‑increment
            unset($productData['id']);
            Product::updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $productData['code']],
                $productData
            );
        }
    }
}
?>
