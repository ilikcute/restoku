<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PendingOrder>
 */
class PendingOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => 'PO-' . strtoupper(Str::random(6)),
            'tenant_id' => Tenant::factory(),
            'customer_name' => $this->faker->name(),
            'table_number' => $this->faker->numberBetween(1, 20),
            'items' => [
                [
                    'product_id' => 1,
                    'product_name' => 'Sample Item',
                    'price' => 15000,
                    'quantity' => 2,
                    'notes' => 'No spicy'
                ]
            ],
            'status' => 'pending',
        ];
    }
}
