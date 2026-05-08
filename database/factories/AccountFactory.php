<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->randomElement(['Kas Toko', 'Bank BCA', 'Bank Mandiri', 'Petty Cash']),
            'account_number' => $this->faker->bankAccountNumber(),
            'balance' => $this->faker->numberBetween(1000000, 10000000),
            'is_active' => true,
        ];
    }
}
