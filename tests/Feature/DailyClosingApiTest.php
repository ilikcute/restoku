<?php

use App\Models\DailyClosing;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('view-closings');
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    $this->user->givePermissionTo('view-closings');
});

test('cannot view daily closing belonging to another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->admin()->create(['tenant_id' => $otherTenant->id]);
    $closing = DailyClosing::factory()->create([
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/daily-closings/'.$closing->id);

    $response->assertForbidden();
});

test('cannot download daily closing report belonging to another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->admin()->create(['tenant_id' => $otherTenant->id]);
    $closing = DailyClosing::factory()->create([
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->get('/api/v1/daily-closings/'.$closing->id.'/report');

    $response->assertForbidden();
});

test('cannot process daily closing while tenant has an open shift', function () {
    config(['app.debug' => true]);

    Shift::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'status' => 'open',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/daily-closings', [
            'closing_date' => now()->toDateString(),
        ]);

    $response->assertStatus(500)
        ->assertJsonPath('status', 'error');

    expect($response->json('message'))->toContain('Shift');
});
