<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        Account::factory(55)->create([
            'tenant_id' => $tenant->id,
            'balance' => 10000000,
        ]);
    }
}
