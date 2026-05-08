<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
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
            'product_id' => Product::factory(),
            'current_stock' => $this->faker->numberBetween(10, 100),
            'minimum_stock' => 5,
        ];
    }
}
