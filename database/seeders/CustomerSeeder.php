<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        Customer::factory(55)->create(['tenant_id' => $tenant->id]);
    }
}
