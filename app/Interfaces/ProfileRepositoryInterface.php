<?php

namespace App\Interfaces;

use App\Models\User;

interface ProfileRepositoryInterface
{
    public function getProfile(User $user): User;

    public function updateProfile(User $user, array $data, $avatarFile = null): User;

    public function updatePassword(User $user, string $newPassword): bool;
}
