<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'tokoredjeki')->first();
        
        foreach (['FOOD', 'CIGARET', 'BEVERAGES'] as $name) {
            Category::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name)
            ]);
        }
    }
}
