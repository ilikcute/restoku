<?php

use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('view-shifts');
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);
    $this->user->givePermissionTo('view-shifts');
});

test('cannot view shift belonging to another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->cashier()->create(['tenant_id' => $otherTenant->id]);
    $shift = Shift::factory()->create([
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
        'status' => 'open',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/shifts/'.$shift->id);

    $response->assertForbidden();
});

test('cannot download shift report belonging to another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->cashier()->create(['tenant_id' => $otherTenant->id]);
    $shift = Shift::factory()->closed()->create([
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->get('/api/v1/shifts/'.$shift->id.'/report');

    $response->assertForbidden();
});

test('cannot open a new shift while current shift is still open', function () {
    config(['app.debug' => true]);

    Shift::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'status' => 'open',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/shifts/open', [
            'starting_cash' => 100000,
        ]);

    $response->assertStatus(500)
        ->assertJsonPath('status', 'error')
        ->assertJsonFragment([
            'message' => 'Anda masih memiliki Shift yang terbuka.',
        ]);
});

test('cannot open a new shift while having an active shift from a prior day', function () {
    config(['app.debug' => true]);

    Shift::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'status' => 'open',
        'start_time' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/shifts/open', [
            'starting_cash' => 100000,
        ]);

    $response->assertStatus(500)
        ->assertJsonPath('status', 'error')
        ->assertJsonFragment([
            'message' => 'Anda memiliki shift aktif dari hari sebelumnya yang belum ditutup. Harap tutup shift tersebut terlebih dahulu sebelum membuka shift baru.',
        ]);
});
