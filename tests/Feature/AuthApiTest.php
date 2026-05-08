<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
