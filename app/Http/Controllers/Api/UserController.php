<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\StoreUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends BaseApiController
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function roles()
    {
        return $this->successResponse(Role::all()->pluck('name'));
    }

    public function permissions()
    {
        return $this->successResponse(Permission::all()->pluck('name'));
    }

    public function index(Request $request)
    {
        $users = $this->userRepository->getAllByTenant(
            $request->user()->tenant_id,
            $request->search,
            $request->integer('per_page') ?: null
        );

        return $this->successResponse(UserResource::collection($users));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $data = [
            'tenant_id' => $request->user()->tenant_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'roles' => [$validated['role']],
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (isset($validated['permissions'])) {
            $data['permissions'] = $validated['permissions'];
        }

        $user = $this->userRepository->create($data);

        return $this->successResponse(new UserResource($user), 'User created successfully', 201);
    }

    public function show(User $user)
    {
        $this->authorizeTenant($user);

        $user->load(['roles', 'permissions']);

        return $this->successResponse(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizeTenant($user);

        $validated = $request->validated();

        $data = [];

        if (isset($validated['name'])) {
            $data['name'] = $validated['name'];
        }

        if (isset($validated['email'])) {
            $data['email'] = $validated['email'];
        }

        if (isset($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['role'])) {
            $data['role'] = $validated['role'];
            $data['roles'] = [$validated['role']];
        }

        if (array_key_exists('is_active', $validated)) {
            $data['is_active'] = $validated['is_active'];
        }

        if (isset($validated['permissions'])) {
            $data['permissions'] = $validated['permissions'];
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = $this->userRepository->update($user->id, $data);

        return $this->successResponse(new UserResource($user), 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $this->authorizeTenant($user);

        if ($user->id === auth()->id()) {
            return $this->errorResponse('You cannot delete yourself.', 422);
        }

        $this->userRepository->delete($user->id);

        return $this->successResponse(null, 'User deleted successfully');
    }
}
