<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function definition(): array
    {
        $names = [
            'Budi Santoso', 'Siti Aminah', 'Agus Setiawan', 'Dewi Lestari',
            'Bambang Pamungkas', 'Ahmad Fauzi', 'Linda Wijaya', 'Rizky Ramadhan',
            'Putri Handayani', 'Eko Prasetyo', 'Maya Sari', 'Hendra Wijaya',
            'Slamet Raharjo', 'Ani Suryani', 'Dedi Kurniawan', 'Rina Permata',
            'Andi Wijaya', 'Yanti Susanti', 'Fajar Pratama', 'Diana Putri',
        ];

        return [
            'name' => fake()->randomElement($names),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::CASHIER,
            'is_active' => true,
        ];
    }

    /**
     * State: Admin
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN,
        ]);
    }

    /**
     * State: Manager
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::MANAGER,
        ]);
    }

    /**
     * State: Cashier
     */
    public function cashier(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::CASHIER,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
