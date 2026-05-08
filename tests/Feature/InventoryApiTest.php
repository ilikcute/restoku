<?php

use App\Models\Product;
use App\Models\Stock;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-stocks');
    Permission::findOrCreate('view-stock-movements');
    $this->user->givePermissionTo(['view-stocks', 'view-stock-movements']);
    $this->product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->stock = Stock::factory()->create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'current_stock' => 10,
    ]);
});

test('admin can view stock list', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.v1.inventory.index'));

    $response->assertStatus(200);
});

test('admin can view stock movements', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.v1.inventory.movements'));

    $response->assertStatus(200);
});

test('inventory routes are properly named', function () {
    expect(route('api.v1.inventory.index'))->toContain('inventory');
    expect(route('api.v1.inventory.movements'))->toContain('movements');
    expect(route('api.v1.inventory.adjust'))->toContain('adjustments');
});
