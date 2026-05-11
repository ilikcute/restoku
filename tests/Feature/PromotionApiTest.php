<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->admin = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'admin'
    ]);
    
    Permission::findOrCreate('view-promotions');
    Permission::findOrCreate('create-promotions');
    Permission::findOrCreate('edit-promotions');
    Permission::findOrCreate('delete-promotions');
    
    $this->admin->givePermissionTo([
        'view-promotions',
        'create-promotions',
        'edit-promotions',
        'delete-promotions'
    ]);
});

test('can list promotions', function () {
    Promotion::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson(route('api.v1.promotions.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can create percentage discount promotion for specific products', function () {
    $product1 = Product::factory()->create(['tenant_id' => $this->tenant->id]);
    $product2 = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $payload = [
        'title' => 'Flash Sale 20%',
        'content' => 'Diskon khusus produk pilihan',
        'type' => 'discount_percentage',
        'discount_value' => 20,
        'applicable_type' => 'products',
        'product_ids' => [$product1->id, $product2->id],
        'is_active' => true,
        'priority' => 1
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('api.v1.promotions.store'), $payload);

    $response->assertCreated();
    
    $promotion = Promotion::first();
    expect($promotion->title)->toBe('Flash Sale 20%')
        ->and($promotion->type)->toBe('discount_percentage')
        ->and($promotion->products)->toHaveCount(2);
});

test('can create fixed discount promotion for specific categories', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

    $payload = [
        'title' => 'Minuman Hemat 5rb',
        'type' => 'discount_fixed',
        'discount_value' => 5000,
        'applicable_type' => 'categories',
        'category_ids' => [$category->id],
        'is_active' => true
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('api.v1.promotions.store'), $payload);

    $response->assertCreated();
    
    $promotion = Promotion::first();
    expect($promotion->categories)->toHaveCount(1)
        ->and($promotion->categories->first()->id)->toBe($category->id);
});

test('can update promotion and its relations', function () {
    $promotion = Promotion::factory()->create([
        'tenant_id' => $this->tenant->id,
        'applicable_type' => 'all'
    ]);
    
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $payload = [
        'title' => 'Updated Title',
        'type' => 'discount_percentage',
        'discount_value' => 50,
        'applicable_type' => 'products',
        'product_ids' => [$product->id],
        'is_active' => true
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson(route('api.v1.promotions.update', $promotion), $payload);

    $response->assertOk();
    
    $promotion->refresh();
    expect($promotion->title)->toBe('Updated Title')
        ->and($promotion->applicable_type)->toBe('products')
        ->and($promotion->products)->toHaveCount(1);
});

test('can delete promotion', function () {
    $promotion = Promotion::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson(route('api.v1.promotions.destroy', $promotion));

    $response->assertOk();
    expect(Promotion::count())->toBe(0);
});

test('promotion is applied correctly to order items', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 100000
    ]);
    
    // Create 10% discount promotion for this product
    $promotion = Promotion::create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Sale 10%',
        'type' => 'discount_percentage',
        'discount_value' => 10,
        'applicable_type' => 'products',
        'is_active' => true,
        'priority' => 1
    ]);
    $promotion->products()->sync([$product->id]);

    $orderService = app(\App\Services\OrderService::class);
    $items = [
        ['product_id' => $product->id, 'quantity' => 1, 'price' => 100000]
    ];
    
    $totals = $orderService->calculateOrderTotals($items, 'regular', $this->tenant->id);
    
    // Subtotal: 100.000
    // Discount: 10% of 100.000 = 10.000
    // Grand Total: 90.000
    expect($totals['discount_total'])->toBe(10000.0)
        ->and($totals['grand_total'])->toBe(90000.0)
        ->and($totals['items'][0]['discount_amount'])->toBe(10000.0);
});

test('fixed discount is applied correctly', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 100000
    ]);
    
    // Create 5.000 fixed discount
    $promotion = Promotion::create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Hemat 5rb',
        'type' => 'discount_fixed',
        'discount_value' => 5000,
        'applicable_type' => 'all',
        'is_active' => true,
        'priority' => 1
    ]);

    $orderService = app(\App\Services\OrderService::class);
    $items = [
        ['product_id' => $product->id, 'quantity' => 2, 'price' => 100000]
    ];
    
    $totals = $orderService->calculateOrderTotals($items, 'regular', $this->tenant->id);
    
    // Subtotal: 200.000
    // Discount: 5.000 per item * 2 = 10.000
    // Grand Total: 190.000
    expect($totals['discount_total'])->toBe(10000.0)
        ->and($totals['grand_total'])->toBe(190000.0);
});

test('promotion is NOT applied if min_purchase not met', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 50000
    ]);
    
    // Create 10.000 discount with 100.000 min purchase
    $promotion = Promotion::create([
        'tenant_id' => $this->tenant->id,
        'title' => 'Big Sale',
        'type' => 'discount_fixed',
        'discount_value' => 10000,
        'min_purchase' => 100000,
        'applicable_type' => 'all',
        'is_active' => true,
        'priority' => 1
    ]);

    $orderService = app(\App\Services\OrderService::class);
    $items = [
        ['product_id' => $product->id, 'quantity' => 1, 'price' => 50000]
    ];
    
    $totals = $orderService->calculateOrderTotals($items, 'regular', $this->tenant->id);
    
    expect($totals['discount_total'])->toBe(0.0)
        ->and($totals['grand_total'])->toBe(50000.0);
});

