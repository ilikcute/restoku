<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $guard = 'web';
    Permission::findOrCreate('manage-roles', $guard);
    Permission::findOrCreate('view-dashboard', $guard);
    Permission::findOrCreate('view-master-data', $guard);
    Permission::findOrCreate('view-products', $guard);
    
    $this->admin = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole(Role::findOrCreate('admin', $guard));
    $this->admin->givePermissionTo('manage-roles');
    
    $this->cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);
    $this->cashier->assignRole(Role::findOrCreate('cashier', $guard));
});

test('admin can list roles', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/roles');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'name', 'permissions']
            ]
        ]);
});

test('cashier cannot list roles', function () {
    $response = $this->actingAs($this->cashier, 'sanctum')
        ->getJson('/api/v1/roles');

    $response->assertForbidden();
});

test('admin can create a new role', function () {
    $roleData = [
        'name' => 'supervisor',
        'permissions' => ['view-dashboard', 'view-products']
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/roles', $roleData);

    $response->assertCreated();
    $this->assertDatabaseHas('roles', ['name' => 'supervisor']);
    
    $role = Role::findByName('supervisor', 'web');
    expect($role->hasPermissionTo('view-dashboard'))->toBeTrue();
    expect($role->hasPermissionTo('view-products'))->toBeTrue();
});

test('admin can update role permissions', function () {
    $role = Role::create(['name' => 'editor']);
    
    $updateData = [
        'permissions' => ['view-master-data']
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/roles/{$role->id}", $updateData);

    $response->assertOk();
    expect($role->fresh()->hasPermissionTo('view-master-data'))->toBeTrue();
    expect($role->fresh()->hasPermissionTo('view-dashboard'))->toBeFalse();
});

test('admin cannot delete system roles', function () {
    $cashierRole = Role::findOrCreate('cashier');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson("/api/v1/roles/{$cashierRole->id}");

    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => 'Cannot delete system roles.']);
});

test('admin can delete custom roles', function () {
    $customRole = Role::create(['name' => 'temporary']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson("/api/v1/roles/{$customRole->id}");

    $response->assertOk();
    $this->assertDatabaseMissing('roles', ['name' => 'temporary']);
});
