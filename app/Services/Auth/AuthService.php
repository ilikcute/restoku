<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user and tenant
     */
    public function register(array $data): array
    {
        $tenant = Tenant::create([
            'name' => $data['tenant_name'],
            'slug' => Str::slug($data['tenant_name']),
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'tenant_id' => $tenant->id,
            'role' => UserRole::ADMIN,
        ]);

        $token = $user->createToken($data['device_name'], ['*'])->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user and generate token
     *
     * @throws ValidationException
     */
    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive.'],
            ]);
        }

        // Abilities based on role for POS
        $abilities = $this->getUserAbilities($user);

        $token = $user->createToken($data['device_name'], $abilities)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Get user abilities based on role
     */
    protected function getUserAbilities(User $user): array
    {
        return match ($user->role) {
            UserRole::ADMIN => ['*'],
            UserRole::MANAGER => ['pos:manage', 'pos:report'],
            UserRole::CASHIER => ['pos:cashier'],
            default => [],
        };
    }

    /**
     * Logout user by revoking current token
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
