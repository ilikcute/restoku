<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TokoRedjekiSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(database_path('seeders/data/toko_redjeki.json'));
        $data = json_decode($json, true);

        // 1. Create Tenant
        foreach ($data['tenants'] as $tData) {
            $tenant = Tenant::updateOrCreate(
                ['slug' => $tData['slug']],
                [
                    'name' => $tData['name'],
                    'address' => $tData['address'],
                    'phone' => $tData['phone'],
                    'email' => $tData['email'],
                    'logo' => $tData['logo'],
                    'footer_text' => $tData['footer_text'],
                    'is_active' => $tData['is_active'],
                ]
            );

            // 2. Create Users for this tenant
            foreach ($data['users'] as $uData) {
                if ($uData['tenant_id'] == $tData['id']) {
                    User::updateOrCreate(
                        ['email' => $uData['email']],
                        [
                            'tenant_id' => $tenant->id,
                            'name' => $uData['name'],
                            'password' => Hash::make('password'), // Reset password to default
                            'role' => $uData['role'],
                            'is_active' => $uData['is_active'],
                        ]
                    );
                }
            }

            // 3. Create Suppliers
            $supplierMap = [];
            foreach ($data['suppliers'] as $sData) {
                if ($sData['tenant_id'] == $tData['id']) {
                    $supplier = Supplier::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'name' => $sData['name']],
                        [
                            'contact_person' => $sData['contact_person'],
                            'phone' => $sData['phone'],
                            'email' => $sData['email'],
                            'address' => $sData['address'],
                            'is_pkp' => $sData['is_pkp'],
                            'is_active' => $sData['is_active'],
                        ]
                    );
                    $supplierMap[$sData['id']] = $supplier->id;
                }
            }

            // 4. Create Units
            $unitMap = [];
            foreach ($data['units'] as $unitData) {
                if ($unitData['tenant_id'] == $tData['id']) {
                    $unit = Unit::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'name' => $unitData['name']],
                        [
                            'short_name' => $unitData['short_name'],
                            'is_active' => $unitData['is_active'],
                        ]
                    );
                    $unitMap[$unitData['id']] = $unit->id;
                }
            }

            // 5. Create Categories
            $categoryMap = [];
            foreach ($data['categories'] as $catData) {
                if ($catData['tenant_id'] == $tData['id']) {
                    $category = Category::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'slug' => $catData['slug']],
                        [
                            'name' => $catData['name'],
                            'description' => $catData['description'],
                            'is_active' => $catData['is_active'],
                        ]
                    );
                    $categoryMap[$catData['id']] = $category->id;
                }
            }

            // 6. Create Products
            foreach ($data['products'] as $pData) {
                if ($pData['tenant_id'] == $tData['id']) {
                    Product::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'code' => $pData['code']],
                        [
                            'category_id' => $categoryMap[$pData['category_id']] ?? null,
                            'unit_id' => $unitMap[$pData['unit_id']] ?? null,
                            'supplier_id' => $supplierMap[$pData['supplier_id']] ?? null,
                            'name' => $pData['name'],
                            'short_name' => $pData['short_name'],
                            'slug' => $pData['slug'],
                            'brand_name' => $pData['brand_name'],
                            'barcode' => $pData['barcode'],
                            'description' => $pData['description'],
                            'cost_price' => $pData['cost_price'],
                            'price' => $pData['price'],
                            'discount_amount' => $pData['discount_amount'],
                            'ojol_price' => $pData['ojol_price'],
                            'ojol_discount' => $pData['ojol_discount'],
                            'wholesale_price' => $pData['wholesale_price'],
                            'wholesale_discount' => $pData['wholesale_discount'],
                            'tax_rate' => $pData['tax_rate'],
                            'service_charge_rate' => $pData['service_charge_rate'],
                            'image' => $pData['image'],
                            'is_active' => $pData['is_active'],
                            'stock_type' => $pData['stock_type'],
                            'minimum_stock' => $pData['minimum_stock'],
                            'maximum_stock' => $pData['maximum_stock'],
                            'reorder_quantity' => $pData['reorder_quantity'],
                            'safety_stock' => $pData['safety_stock'],
                            'lead_time' => $pData['lead_time'],
                        ]
                    );
                }
            }
        }
    }
}
