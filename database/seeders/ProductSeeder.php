<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'tokoredjeki')->first();
        if (!$tenant) return;

        // Ensure category and unit exist
        $category = Category::where('tenant_id', $tenant->id)->first() ?? Category::factory()->create(['tenant_id' => $tenant->id, 'name' => 'FOOD']);
        $unit = Unit::where('tenant_id', $tenant->id)->first() ?? Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Pcs', 'short_name' => 'pcs']);

        $products = [
            [
                'code' => '10000001',
                'name' => 'ANGGUR HIJAU',
                'cost_price' => 35000,
                'price' => 50000,
            ],
            [
                'code' => '10000002',
                'name' => 'USUS GORENG ORI',
                'cost_price' => 22200,
                'price' => 35000,
            ],
            [
                'code' => '10000003',
                'name' => 'USUS GORENG PEDAS',
                'cost_price' => 22200,
                'price' => 35000,
            ],
            [
                'code' => '10000004',
                'name' => 'KERIPIK TEMPE',
                'cost_price' => 18600,
                'price' => 30000,
            ],
            [
                'code' => '10000005',
                'name' => 'BASRENG ORI',
                'cost_price' => 21000,
                'price' => 35000,
            ],
            [
                'code' => '10000006',
                'name' => 'BASRENG PEDAS',
                'cost_price' => 21000,
                'price' => 35000,
            ],
            [
                'code' => '10000007',
                'name' => 'MACARONI ORI',
                'cost_price' => 13080,
                'price' => 22000,
            ],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $p['code']],
                [
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']),
                    'cost_price' => $p['cost_price'],
                    'price' => $p['price'],
                    'tax_rate' => 11,
                    'service_charge_rate' => 10,
                    'is_active' => true,
                    'stock_type' => 'trackable',
                ]
            );
        }
    }
}
