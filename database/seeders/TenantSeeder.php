<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'restoku-pos-demo'],
            ['name' => 'Restoku POS Demo']
        );

        // Roles & Permissions
        $permissions = [
            'view-dashboard',
            'view-master-data', 'view-products', 'view-categories', 'view-units', 'view-suppliers', 'view-customers',
            'view-inventory', 'view-stocks', 'view-stock-movements', 'view-stock-adjustments', 'view-inventory-alerts',
            'view-sales', 'view-pos', 'view-shifts', 'view-orders', 'view-sales-returns',
            'view-purchasing', 'view-purchases', 'view-purchase-returns', 'view-procurement',
            'view-finance', 'view-accounts', 'view-transactions', 'view-closings',
            'view-reports', 'view-report-sales', 'view-report-purchases', 'view-report-returns', 'view-report-tax',
            'view-settings', 'view-profile', 'view-business-profile', 'manage-users', 'view-promotions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->syncPermissions([
            'view-dashboard',
            'view-master-data', 'view-products', 'view-categories', 'view-units', 'view-suppliers', 'view-customers',
            'view-inventory', 'view-stocks', 'view-stock-movements', 'view-stock-adjustments', 'view-inventory-alerts',
            'view-sales', 'view-pos', 'view-orders', 'view-sales-returns',
            'view-reports', 'view-report-sales', 'view-report-returns',
            'view-settings', 'view-profile',
        ]);

        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $cashierRole->syncPermissions([
            'view-dashboard',
            'view-sales', 'view-pos', 'view-orders',
            'view-settings', 'view-profile',
        ]);

        // Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@restoku.id'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Budi Santoso',
                'role' => UserRole::ADMIN,
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@restoku.id'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Dewi Lestari',
                'role' => UserRole::MANAGER,
                'password' => Hash::make('password'),
            ]
        );
        $manager->assignRole('manager');

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@restoku.id'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Agus Setiawan',
                'role' => UserRole::CASHIER,
                'password' => Hash::make('password'),
            ]
        );
        $cashier->assignRole('cashier');
    }
}
