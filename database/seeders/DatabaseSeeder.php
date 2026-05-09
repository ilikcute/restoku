<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,   // Paling pertama (wajib)
            TenantSeeder::class,           // Tenant + User default
            DemoDataSeeder::class,         // Data dummy (produk, kategori, dll)
        ]);

        $this->command->info('🎉 Database seeding selesai!');
    }
}
