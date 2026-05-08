<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyClosing>
 */
class DailyClosingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $revenue = $this->faker->numberBetween(1000000, 5000000);
        $expense = $this->faker->numberBetween(100000, 500000);
        
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'closing_date' => now()->subDay(),
            'total_revenue' => $revenue,
            'total_transactions' => $this->faker->numberBetween(10, 50),
            'total_discounts' => $revenue * 0.05,
            'total_tax' => $revenue * 0.1,
            'total_income' => $revenue,
            'total_expense' => $expense,
            'net_revenue' => $revenue - $expense,
            'notes' => $this->faker->sentence(),
        ];
    }
}
