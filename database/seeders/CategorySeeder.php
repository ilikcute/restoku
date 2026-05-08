<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        Category::factory(6)->create(['tenant_id' => $tenant->id]);
    }
}
