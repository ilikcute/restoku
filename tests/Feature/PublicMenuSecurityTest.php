<?php

use App\Models\PendingOrder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('view-pos');
});

test('cannot fetch pending order token from another tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userTenantB = User::factory()->create(['tenant_id' => $tenantB->id]);
    $userTenantB->givePermissionTo('view-pos');

    $pendingOrder = PendingOrder::create([
        'token' => 'PO-ABC123',
        'tenant_id' => $tenantA->id,
        'customer_name' => 'Customer A',
        'table_number' => '01',
        'items' => [['product_id' => 1, 'quantity' => 1]],
        'status' => 'pending',
    ]);

    $response = $this->actingAs($userTenantB, 'sanctum')
        ->getJson('/api/v1/orders/pending/'.$pendingOrder->token);

    $response->assertNotFound();
});

test('can fetch pending order token from same tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->givePermissionTo('view-pos');

    $pendingOrder = PendingOrder::create([
        'token' => 'PO-DEF456',
        'tenant_id' => $tenant->id,
        'customer_name' => 'Customer T',
        'table_number' => '09',
        'items' => [['product_id' => 1, 'quantity' => 2]],
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/orders/pending/'.$pendingOrder->token);

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $pendingOrder->id);
});
