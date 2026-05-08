<?php

use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-purchases');
    $this->user->givePermissionTo('view-purchases');
    $this->supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('cannot create purchase using supplier from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherSupplier = Supplier::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.v1.purchases.store'), [
            'supplier_id' => $otherSupplier->id,
            'payment_method' => 'cash',
            'account_id' => $this->account->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'cost_price' => 10000,
                ],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['supplier_id']);
});

test('cannot create purchase using account from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherAccount = Account::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.v1.purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'payment_method' => 'cash',
            'account_id' => $otherAccount->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'cost_price' => 10000,
                ],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['account_id']);
});

test('cannot create purchase using product from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherProduct = Product::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.v1.purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'payment_method' => 'cash',
            'account_id' => $this->account->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $otherProduct->id,
                    'quantity' => 1,
                    'cost_price' => 10000,
                ],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.product_id']);
});
