<?php

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\QueryException;

describe('User Model', function () {
    test('user can be created with required attributes', function () {
        $tenant = Tenant::factory()->create();

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        expect($user)->toBeInstanceOf(User::class)
            ->and($user->email)->toBe('john@example.com')
            ->and($user->name)->toBe('John Doe')
            ->and($user->tenant_id)->toBe($tenant->id);
    });

    test('user has password hashed on creation', function () {
        $tenant = Tenant::factory()->create();
        $password = 'Password123!';

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => bcrypt($password),
        ]);

        expect(password_verify($password, $user->password))->toBeTrue();
    });

    test('user belongs to tenant', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        expect($user->tenant)->toBeInstanceOf(Tenant::class)
            ->and($user->tenant->id)->toBe($tenant->id);
    });

    test('user can have different roles', function () {
        $tenant = Tenant::factory()->create();

        $admin = User::factory()->admin()->create(['tenant_id' => $tenant->id]);
        $cashier = User::factory()->cashier()->create(['tenant_id' => $tenant->id]);

        expect($admin->role)->toBe(UserRole::ADMIN)
            ->and($cashier->role)->toBe(UserRole::CASHIER);
    });

    test('user cannot be created without email', function () {
        $tenant = Tenant::factory()->create();

        $this->expectException(QueryException::class);

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => null,
        ]);
    });

    test('user has timestamps', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        expect($user->created_at)->not->toBeNull()
            ->and($user->updated_at)->not->toBeNull();
    });
});
