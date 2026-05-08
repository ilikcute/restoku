<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $units = [
            ['name' => 'Pcs', 'short_name' => 'pcs'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'gr'],
            ['name' => 'Liter', 'short_name' => 'ltr'],
        ];
        $unit = $this->faker->randomElement($units);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $unit['name'],
            'short_name' => $unit['short_name'],
        ];
    }
}
