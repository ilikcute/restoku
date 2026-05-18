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
        // Buat Tenant Demo
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'tokoredjeki'],
            ['name' => 'Toko Redjeki'],
        );

        // Setup Role & Permission terlebih dahulu
        $this->call(RolePermissionSeeder::class);

        // ==================== USER DEFAULT ====================
        $admin = User::firstOrCreate(
            ['email' => 'admin@tokoredjeki.id'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Budi Santoso',
                'role'      => UserRole::ADMIN,
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@tokoredjeki.id'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Dewi Lestari',
                'role'      => UserRole::MANAGER,
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $manager->assignRole('manager');

        $cashier = User::firstOrCreate(
            ['email' => 'kasir@tokoredjeki.id'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Agus Setiawan',
                'role'      => UserRole::CASHIER,
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $cashier->assignRole('cashier');

        $this->command->info('✅ Tenant Demo + 3 User default (Admin, Manager, Cashier) berhasil dibuat!');
    }
}
