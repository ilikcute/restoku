<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        $cashier = User::where('email', 'cashier@restoku.id')->first();

        for ($i = 0; $i < 55; $i++) {
            Shift::factory()->create([
                'tenant_id' => $tenant->id,
                'user_id' => $cashier->id,
                'status' => 'closed',
                'start_time' => now()->subDays(60 - $i),
                'end_time' => now()->subDays(60 - $i)->addHours(8),
            ]);
        }
    }
}
