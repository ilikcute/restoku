<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        ExpenseCategory::factory(55)->create(['tenant_id' => $tenant->id]);
    }
}
