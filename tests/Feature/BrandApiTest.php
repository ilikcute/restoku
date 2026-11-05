<?php

use App\Models\Brand;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-categories');
    $this->user->givePermissionTo('view-categories');
});

test('admin can list brands', function () {
    Brand::factory(3)->create([
        'tenant_id' => $this->tenant->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.v1.brands.index'));

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('admin can create a brand', function () {
    $brandData = [
        'name' => 'New Brand',
        'description' => 'Brand Description',
        'is_active' => true,
    ];

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.v1.brands.store'), $brandData);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'New Brand');

    $this->assertDatabaseHas('brands', [
        'tenant_id' => $this->tenant->id,
        'name' => 'New Brand',
    ]);
});

test('admin can update a brand', function () {
    $brand = Brand::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.v1.brands.update', $brand->id), [
            'name' => 'Updated Brand Name',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Brand Name');
});

test('admin can delete a brand', function () {
    $brand = Brand::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.v1.brands.destroy', $brand->id));

    $response->assertStatus(200);

    $this->assertSoftDeleted('brands', ['id' => $brand->id]);
});

test('user cannot see brands from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    Brand::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.v1.brands.index'));

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});
