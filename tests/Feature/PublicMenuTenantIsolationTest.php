<?php

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public catalog only returns active products from requested tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $productA = Product::factory()->create([
        'tenant_id' => $tenantA->id,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'tenant_id' => $tenantB->id,
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/catalog?tenant_id='.$tenantA->id);

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $productA->id);
});

test('public order rejects product from another tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $otherTenantProduct = Product::factory()->create([
        'tenant_id' => $tenantB->id,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/save-order', [
        'tenant_id' => $tenantA->id,
        'customer_name' => 'Public Customer',
        'items' => [
            [
                'product_id' => $otherTenantProduct->id,
                'quantity' => 1,
            ],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.product_id']);
});
