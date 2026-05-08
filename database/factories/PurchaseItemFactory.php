<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(10, 100);
        $costPrice = $this->faker->numberBetween(3000, 70000);

        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->word(),
            'cost_price' => $costPrice,
            'quantity' => $quantity,
            'discount_amount' => 0,
            'tax_amount' => $costPrice * 0.1 * $quantity,
            'subtotal' => ($costPrice * $quantity) + ($costPrice * 0.1 * $quantity),
        ];
    }
}
