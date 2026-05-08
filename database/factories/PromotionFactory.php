<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
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
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'is_active' => true,
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(30),
            'priority' => $this->faker->numberBetween(1, 10),
        ];
    }
}
