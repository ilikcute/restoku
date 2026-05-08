<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockAdjustmentItem>
 */
class StockAdjustmentItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $recorded = $this->faker->numberBetween(10, 50);
        $actual = $this->faker->numberBetween(5, 55);
        $diff = $actual - $recorded;

        return [
            'stock_adjustment_id' => StockAdjustment::factory(),
            'product_id' => Product::factory(),
            'recorded_stock' => $recorded,
            'actual_stock' => $actual,
            'adjustment_quantity' => $diff,
            'cost_price' => $this->faker->numberBetween(5000, 50000),
            'reason' => $this->faker->randomElement(['Barang Rusak', 'Selisih Stok', 'Kadaluarsa', 'Lain-lain']),
        ];
    }
}
