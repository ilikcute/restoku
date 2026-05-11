<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected AuthRepositoryInterface $authRepository
    ) {}

    /**
     * Register a new user and tenant
     */
    public function register(array $data): array
    {
        $tenant = $this->authRepository->createTenant([
            'name' => $data['tenant_name'],
            'slug' => Str::slug($data['tenant_name']),
        ]);

        $user = $this->authRepository->createUser([
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
        $user = $this->authRepository->findUserByEmail($data['email']);

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
        $this->authRepository->revokeCurrentToken($user);
    }
}
