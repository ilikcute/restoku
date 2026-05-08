<?php

namespace Database\Factories;

use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
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
            'start_time' => now(),
            'starting_cash' => $this->faker->numberBetween(100000, 500000),
            'status' => 'open',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'end_time' => now()->addHours(8),
            'ending_cash' => $attributes['starting_cash'] + 1000000,
            'total_sales' => 1000000,
            'total_expected' => $attributes['starting_cash'] + 1000000,
            'difference' => 0,
        ]);
    }
}
