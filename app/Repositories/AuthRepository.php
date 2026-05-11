<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\Tenant;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function createTenant(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}