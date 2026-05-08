<?php


use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Unit;

describe('Product Model', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();
        $this->category = Category::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    });

    test('product can be created with required attributes', function () {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'name' => 'Test Product',
            'price' => 50000,
            'cost_price' => 30000,
        ]);

        expect($product)->toBeInstanceOf(Product::class)
            ->and($product->name)->toBe('Test Product')
            ->and($product->price)->toBe(50000)
            ->and($product->cost_price)->toBe(30000);
    });

    test('product belongs to category', function () {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
        ]);

        expect($product->category)->toBeInstanceOf(Category::class)
            ->and($product->category->id)->toBe($this->category->id);
    });

    test('product belongs to unit', function () {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
        ]);

        expect($product->unit)->toBeInstanceOf(Unit::class)
            ->and($product->unit->id)->toBe($this->unit->id);
    });

    test('product can be active or inactive', function () {
        $activeProduct = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ]);

        $inactiveProduct = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'is_active' => false,
        ]);

        expect($activeProduct->is_active)->toBeTrue()
            ->and($inactiveProduct->is_active)->toBeFalse();
    });

    test('product profit margin can be calculated', function () {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'price' => 100000,
            'cost_price' => 60000,
        ]);

        $profit = $product->price - $product->cost_price;
        $margin = ($profit / $product->price) * 100;

        expect($profit)->toBe(40000)
            ->and($margin)->toEqual(40);
    });

    test('product belongs to tenant', function () {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
        ]);

        expect($product->tenant)->toBeInstanceOf(Tenant::class)
            ->and($product->tenant->id)->toBe($this->tenant->id);
    });
});
