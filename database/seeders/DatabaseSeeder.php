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
            RolePermissionSeeder::class,
            TenantSeeder::class,
            DemoDataSeeder::class,
            TokoRedjekiSeeder::class,
        ]);

        $this->command->info('🎉 Database seeding selesai!');
    }
}
