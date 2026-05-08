<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->numberBetween(5000, 100000);
        $costPrice = $price * 0.7;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->word(),
            'price' => $price,
            'cost_price' => $costPrice,
            'quantity' => $quantity,
            'discount_amount' => 0,
            'tax_amount' => $price * 0.1 * $quantity,
            'subtotal' => ($price * $quantity) + ($price * 0.1 * $quantity),
        ];
    }
}
