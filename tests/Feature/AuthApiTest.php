<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin');
    Role::findOrCreate('manager');
    Role::findOrCreate('cashier');
});

test('user can register with a new tenant', function () {
    $response = $this->postJson(route('api.v1.auth.register'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '08123456789',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'tenant_name' => 'John Store',
        'device_name' => 'POS-Unit-01',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => [
                    'attributes' => ['name', 'email', 'role'],
                ],
                'token',
            ],
        ]);

    $this->assertDatabaseHas('tenants', ['name' => 'John Store']);
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});

test('user can login and receive a token', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'password' => bcrypt('Password123!'),
    ]);

    $response = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'Password123!',
        'device_name' => 'POS-Unit-01',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => ['user', 'token'],
        ]);
});

test('inactive user cannot login', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'password' => bcrypt('Password123!'),
        'is_active' => false,
    ]);

    $response = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'Password123!',
        'device_name' => 'POS-Unit-01',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('manager login token only has manager abilities', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->manager()->create([
        'tenant_id' => $tenant->id,
        'password' => bcrypt('Password123!'),
        'is_active' => true,
    ]);

    $response = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'Password123!',
        'device_name' => 'POS-Unit-01',
    ]);

    $response->assertSuccessful();

    $token = $user->fresh()->tokens()->latest()->first();

    expect($token)->not->toBeNull()
        ->and($token->can('pos:manage'))->toBeTrue()
        ->and($token->can('pos:report'))->toBeTrue()
        ->and($token->can('pos:cashier'))->toBeFalse()
        ->and($token->can('unlisted:ability'))->toBeFalse();
});

test('user can get profile info when authenticated', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson(route('api.v1.auth.me'));

    $response->assertStatus(200)
        ->assertJsonPath('data.attributes.email', $user->email);
});

test('user can logout', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson(route('api.v1.auth.logout'));

    $response->assertStatus(200);
    $this->assertCount(0, $user->tokens);
});
