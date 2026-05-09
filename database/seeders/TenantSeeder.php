<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Tenant utama (Demo)
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'restoku-pos-demo'],
            ['name' => 'Restoku POS Demo']
        );

        // Panggil Role & Permission Seeder
        $this->call(RolePermissionSeeder::class);

        // ==================== USERS ====================
        $admin = User::firstOrCreate(
            ['email' => 'admin@restoku.id'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Budi Santoso',
                'role'      => UserRole::ADMIN,           // Kolom enum (bisa kita diskusikan lagi)
                'password'  => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@restoku.id'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Dewi Lestari',
                'role'      => UserRole::MANAGER,
                'password'  => Hash::make('password'),
            ]
        );
        $manager->assignRole('manager');

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@restoku.id'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Agus Setiawan',
                'role'      => UserRole::CASHIER,
                'password'  => Hash::make('password'),
            ]
        );
        $cashier->assignRole('cashier');

        $this->command->info('✅ Tenant Demo + Users berhasil dibuat!');
    }
}
