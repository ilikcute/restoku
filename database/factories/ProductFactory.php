<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $costPrice = $this->faker->numberBetween(5000, 50000);
        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'code' => 'PRD-' . strtoupper($this->faker->unique()->bothify('??###')),
            'name' => $name,
            'brand_name' => $this->faker->company(),
            'short_name' => Str::limit($name, 20),
            'slug' => Str::slug($name),
            'barcode' => $this->faker->ean13(),
            'description' => $this->faker->sentence(),
            'cost_price' => $costPrice,
            'price' => $costPrice * 1.5,
            'is_active' => true,
            'stock_type' => 'trackable',
        ];
    }
}
