<?php

namespace App\Interfaces;

use App\Models\Tenant;
use App\Models\User;

interface AuthRepositoryInterface
{
    public function findUserByEmail(string $email): ?User;

    public function createTenant(array $data): Tenant;

    public function createUser(array $data): User;

    public function revokeCurrentToken(User $user): void;
}
