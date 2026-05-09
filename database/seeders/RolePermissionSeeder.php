<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permissions agar tidak ada data lama
        app()->make('cache')->forget('spatie.permission.cache');

        // ==================== PERMISSIONS ====================
        $permissions = [
            // Dashboard
            'view-dashboard',

            // Master Data
            'view-master-data',
            'create-master-data',
            'edit-master-data',
            'delete-master-data',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',
            'view-units',
            'create-units',
            'edit-units',
            'delete-units',
            'view-suppliers',
            'create-suppliers',
            'edit-suppliers',
            'delete-suppliers',
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            // POS & Sales
            'view-pos',
            'create-order',
            'edit-order',
            'delete-order',
            'view-orders',
            'view-sales',
            'view-sales-returns',

            // Inventory & Stok
            'view-inventory',
            'view-stocks',
            'create-stock-adjustment',
            'view-stock-movements',
            'view-purchases',
            'create-purchases',

            // Finance & Closing
            'view-finance',
            'view-closings',
            'create-expense',
            'create-income',
            'view-reports',
            'view-report-sales',
            'view-report-inventory',

            // Settings & User Management
            'view-settings',
            'manage-users',
            'manage-roles',
            'view-profile',
            'edit-profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ==================== ROLES ====================
        // Admin - Full Access
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        // Manager
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->syncPermissions([
            'view-dashboard',
            'view-master-data',
            'view-products',
            'view-categories',
            'view-suppliers',
            'view-customers',
            'view-pos',
            'create-order',
            'view-orders',
            'view-sales',
            'view-inventory',
            'view-stocks',
            'view-purchases',
            'view-finance',
            'view-closings',
            'view-reports',
            'view-report-sales',
            'view-settings',
            'view-profile',
            'edit-profile',
        ]);

        // Cashier - Paling terbatas
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $cashierRole->syncPermissions([
            'view-dashboard',
            'view-pos',
            'create-order',
            'view-orders',
            'view-profile',
            'edit-profile',
        ]);

        $this->command->info('✅ Role & Permission berhasil dibuat!');
    }
}
