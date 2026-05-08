<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-products');
    $this->user->givePermissionTo('view-products');
    $this->category = Category::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->unit = Unit::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('admin can list products', function () {
    Product::factory(3)->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'unit_id' => $this->unit->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.v1.products.index'));

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data.data');
});

test('admin can create a product', function () {
    $productData = [
        'category_id' => $this->category->id,
        'unit_id' => $this->unit->id,
        'code' => 'PRD001',
        'name' => 'New Product',
        'price' => 15000,
        'cost_price' => 10000,
        'stock_type' => 'trackable',
        'is_active' => true,
    ];

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.v1.products.store'), $productData);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'New Product');

    $this->assertDatabaseHas('products', [
        'tenant_id' => $this->tenant->id,
        'name' => 'New Product',
    ]);
});

test('admin cannot create a product using master data from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherCategory = Category::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherUnit = Unit::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherSupplier = Supplier::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.v1.products.store'), [
            'supplier_id' => $otherSupplier->id,
            'category_id' => $otherCategory->id,
            'unit_id' => $otherUnit->id,
            'code' => 'PRD-TENANT-GAP',
            'name' => 'Invalid Tenant Product',
            'price' => 15000,
            'cost_price' => 10000,
            'stock_type' => 'trackable',
            'is_active' => true,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['supplier_id', 'category_id', 'unit_id']);
});

test('admin can update a product', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'unit_id' => $this->unit->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.v1.products.update', $product->id), [
            'name' => 'Updated Name',
            'price' => 20000,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Name');
});

test('admin cannot update a product using master data from another tenant', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'unit_id' => $this->unit->id,
    ]);
    $otherTenant = Tenant::factory()->create();
    $otherCategory = Category::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherUnit = Unit::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherSupplier = Supplier::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.v1.products.update', $product->id), [
            'supplier_id' => $otherSupplier->id,
            'category_id' => $otherCategory->id,
            'unit_id' => $otherUnit->id,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['supplier_id', 'category_id', 'unit_id']);
});

test('admin can delete a product', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'unit_id' => $this->unit->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.v1.products.destroy', $product->id));

    $response->assertStatus(200);

    // Check if it's soft deleted
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('user cannot see products from another tenant', function () {
    // Create product in another tenant
    $otherTenant = Tenant::factory()->create();
    Product::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.v1.products.index'));

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data.data');
});
