<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'tenant_id'    => Tenant::inRandomOrder()->first()?->id ?? 1,
            'name'         => $this->faker->name(),
            'email'        => $this->faker->unique()->safeEmail(),
            'role'         => UserRole::CASHIER,   // default
            'password'     => Hash::make('password'),
            'is_active'    => true,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    // ==================== STATE METHODS ====================

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::ADMIN,
            'name' => 'Admin ' . $this->faker->lastName(),
        ])->withSpatieRole('admin');
    }

    public function manager(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::MANAGER,
            'name' => 'Manager ' . $this->faker->lastName(),
        ])->withSpatieRole('manager');
    }

    public function cashier(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::CASHIER,
        ])->withSpatieRole('cashier');
    }

    /**
     * Assign Spatie Permission Role setelah user dibuat
     */
    public function withSpatieRole(string $roleName): static
    {
        return $this->afterCreating(function (\App\Models\User $user) use ($roleName) {
            $user->assignRole($roleName);
        });
    }
}
