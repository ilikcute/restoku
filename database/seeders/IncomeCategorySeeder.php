<?php

namespace Database\Seeders;

use App\Models\IncomeCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class IncomeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        IncomeCategory::factory(55)->create(['tenant_id' => $tenant->id]);
    }
}
