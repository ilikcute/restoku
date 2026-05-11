<?php

namespace App\Repositories;

use App\Interfaces\ProfileRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function getProfile(User $user): User
    {
        return $user->load('tenant');
    }

    public function updateProfile(User $user, array $data, $avatarFile = null): User
    {
        if ($avatarFile) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $avatarFile->store('avatars', 'public');
        }

        $user->update($data);

        return $user;
    }

    public function updatePassword(User $user, string $newPassword): bool
    {
        return $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
