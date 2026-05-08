<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(50000, 200000);
        $tax = $subtotal * 0.1;

        return [
            'tenant_id' => Tenant::factory(),
            'shift_id' => Shift::factory(),
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.strtoupper($this->faker->bothify('??###')),
            'customer_name' => $this->faker->name(),
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $subtotal + $tax,
            'payment_method' => 'cash',
            'paid_amount' => $subtotal + $tax,
            'status' => 'completed',
        ];
    }
}
