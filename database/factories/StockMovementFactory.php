<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prev = $this->faker->numberBetween(0, 100);
        $qty = $this->faker->numberBetween(1, 20);
        $type = $this->faker->randomElement(['in', 'out', 'adjustment']);
        $new = $type === 'in' ? $prev + $qty : ($type === 'out' ? $prev - $qty : $prev + $qty);

        return [
            'tenant_id' => Tenant::factory(),
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'type' => $type,
            'quantity' => $qty,
            'previous_stock' => $prev,
            'new_stock' => $new,
            'reference_type' => null,
            'reference_id' => null,
            'notes' => $this->faker->sentence(),
        ];
    }
}
