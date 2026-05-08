<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->tenant = Tenant::first() ?? Tenant::factory()->create();
    $this->user = User::factory()->for($this->tenant)->create(['role' => 'admin']);
    Permission::findOrCreate('view-business-profile');
    $this->user->givePermissionTo('view-business-profile');
});

it('can get printer settings', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/settings/printer');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'use_default',
                'connection_type',
                'address',
                'port',
                'defaults' => ['connection_type', 'address', 'port'],
            ],
        ]);
});

it('returns default settings when use_default is true', function () {
    $this->tenant->update([
        'printer_use_default' => true,
        'printer_connection_type' => null,
        'printer_address' => null,
        'printer_port' => null,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/settings/printer');

    $data = $response->json('data');
    expect($data['use_default'])->toBeTrue();
    expect($data['connection_type'])->toBe(config('printer.connection_type'));
    expect($data['address'])->toBe(config('printer.address'));
});

it('returns custom settings when use_default is false', function () {
    $this->tenant->update([
        'printer_use_default' => false,
        'printer_connection_type' => 'windows',
        'printer_address' => 'POS-58-Custom',
        'printer_port' => null,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/settings/printer');

    $data = $response->json('data');
    expect($data['use_default'])->toBeFalse();
    expect($data['connection_type'])->toBe('windows');
    expect($data['address'])->toBe('POS-58-Custom');
});

it('can update to use default settings', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/v1/settings/printer', [
            'use_default' => true,
            'connection_type' => null,
            'address' => null,
            'port' => null,
        ]);

    $response->assertOk()
        ->assertJson(['data' => ['use_default' => true]]);

    $this->tenant->refresh();
    expect($this->tenant->printer_use_default)->toBeTrue();
    expect($this->tenant->printer_connection_type)->toBeNull();
    expect($this->tenant->printer_address)->toBeNull();
    expect($this->tenant->printer_port)->toBeNull();
});

it('can update to custom windows printer', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/v1/settings/printer', [
            'use_default' => false,
            'connection_type' => 'windows',
            'address' => 'POS-80-Thermal',
            'port' => null,
        ]);

    $response->assertOk()
        ->assertJson(['data' => ['use_default' => false]]);

    $this->tenant->refresh();
    expect($this->tenant->printer_use_default)->toBeFalse();
    expect($this->tenant->printer_connection_type)->toBe('windows');
    expect($this->tenant->printer_address)->toBe('POS-80-Thermal');
    expect($this->tenant->printer_port)->toBeNull();
});

it('can update to custom network printer', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/v1/settings/printer', [
            'use_default' => false,
            'connection_type' => 'network',
            'address' => '192.168.1.50',
            'port' => 9100,
        ]);

    $response->assertOk()
        ->assertJson(['data' => ['use_default' => false]]);

    $this->tenant->refresh();
    expect($this->tenant->printer_use_default)->toBeFalse();
    expect($this->tenant->printer_connection_type)->toBe('network');
    expect($this->tenant->printer_address)->toBe('192.168.1.50');
    expect($this->tenant->printer_port)->toBe(9100);
});

it('validates required fields for custom printer', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/v1/settings/printer', [
            'use_default' => false,
            'connection_type' => '',
            'address' => '',
            'port' => null,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['connection_type', 'address']);
});

it('requires port for network printer', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/v1/settings/printer', [
            'use_default' => false,
            'connection_type' => 'network',
            'address' => '192.168.1.50',
            'port' => null,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['port']);
});

it('clears custom settings when switching to default', function () {
    // First set custom printer
    $this->tenant->update([
        'printer_use_default' => false,
        'printer_connection_type' => 'windows',
        'printer_address' => 'OLD-PRINTER',
        'printer_port' => 9100,
    ]);

    // Then switch to default
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/v1/settings/printer', [
            'use_default' => true,
        ]);

    $response->assertOk();

    $this->tenant->refresh();
    expect($this->tenant->printer_use_default)->toBeTrue();
    expect($this->tenant->printer_connection_type)->toBeNull();
    expect($this->tenant->printer_address)->toBeNull();
    expect($this->tenant->printer_port)->toBeNull();
});
