<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
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
            'user_id' => User::factory(),
            'adjustment_number' => 'ADJ-' . $this->faker->unique()->numberBetween(1000, 9999),
            'adjustment_date' => now(),
            'status' => 'completed',
            'notes' => $this->faker->sentence(),
            'total_loss_amount' => $this->faker->numberBetween(0, 500000),
        ];
    }
}
