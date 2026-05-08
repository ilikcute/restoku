<?php

namespace Database\Seeders;

use App\Models\DailyClosing;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DailyClosingSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        $admin = User::where('email', 'admin@restoku.id')->first();

        for ($i = 0; $i < 55; $i++) {
            $date = now()->subDays(60 - $i);
            DailyClosing::create([
                'tenant_id' => $tenant->id,
                'user_id' => $admin->id,
                'closing_date' => $date,
                'total_revenue' => rand(1000000, 5000000),
                'total_transactions' => rand(20, 100),
                'total_discounts' => rand(0, 500000),
                'total_tax' => rand(100000, 500000),
                'net_revenue' => rand(800000, 4500000),
                'notes' => 'Daily closing for '.$date->format('Y-m-d'),
            ]);
        }
    }
}
