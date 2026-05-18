<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'tokoredjeki')->first();
        if (!$tenant) {
            $tenant = Tenant::first();
        }

        if (!$tenant) return;

        $units = [
            ['name' => 'Pcs', 'short_name' => 'pcs'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'gr'],
            ['name' => 'Liter', 'short_name' => 'ltr'],
            ['name' => 'Pack', 'short_name' => 'pack'],
            ['name' => 'Botol', 'short_name' => 'btl'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $unit['name']],
                ['short_name' => $unit['short_name'], 'is_active' => true]
            );
        }
    }
}
