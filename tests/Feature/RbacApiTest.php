<?php

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    Permission::findOrCreate('view-business-profile');
    Permission::findOrCreate('view-profile');
    Permission::findOrCreate('manage-users');
    Permission::findOrCreate('view-products');
    Permission::findOrCreate('view-categories');
    Permission::findOrCreate('view-stocks');
    Permission::findOrCreate('view-orders');
    Permission::findOrCreate('view-purchases');
    Permission::findOrCreate('view-transactions');
    Permission::findOrCreate('view-sales-returns');
});

test('user without business profile permission cannot access tenant settings', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/settings/tenant');

    $response->assertForbidden();
});

test('user with business profile permission can access tenant settings', function () {
    $manager = User::factory()->manager()->create(['tenant_id' => $this->tenant->id]);
    $manager->givePermissionTo('view-business-profile');

    $response = $this->actingAs($manager, 'sanctum')
        ->getJson('/api/v1/settings/tenant');

    $response->assertOk();
});

test('user without manage users permission cannot access users endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/users');

    $response->assertForbidden();
});

test('user with manage users permission can access users endpoint', function () {
    $admin = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    $admin->givePermissionTo('manage-users');

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/users');

    $response->assertOk();
});

test('user without profile permission cannot access profile endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/profile');

    $response->assertForbidden();
});

test('user with profile permission can access profile endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);
    $cashier->givePermissionTo('view-profile');

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/profile');

    $response->assertOk();
});

test('user without products permission cannot access products endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/products');

    $response->assertForbidden();
});

test('user with products permission can access products endpoint', function () {
    $manager = User::factory()->manager()->create(['tenant_id' => $this->tenant->id]);
    $manager->givePermissionTo('view-products');

    $response = $this->actingAs($manager, 'sanctum')
        ->getJson('/api/v1/products');

    $response->assertOk();
});

test('user without categories permission cannot access categories endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/categories');

    $response->assertForbidden();
});

test('user with categories permission can access categories endpoint', function () {
    $manager = User::factory()->manager()->create(['tenant_id' => $this->tenant->id]);
    $manager->givePermissionTo('view-categories');

    $response = $this->actingAs($manager, 'sanctum')
        ->getJson('/api/v1/categories');

    $response->assertOk();
});

test('user without stocks permission cannot access inventory stocks endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/inventory/stocks');

    $response->assertForbidden();
});

test('user without orders permission cannot access orders endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/orders');

    $response->assertForbidden();
});

test('user without purchases permission cannot access purchases endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/purchases');

    $response->assertForbidden();
});

test('user without transactions permission cannot access finance transactions endpoint', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/v1/finance/transactions');

    $response->assertForbidden();
});

test('user without sales returns permission cannot create sales return', function () {
    $cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($cashier, 'sanctum')
        ->postJson('/api/v1/returns/orders', []);

    $response->assertForbidden();
});
