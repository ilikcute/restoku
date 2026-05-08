<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\StoreUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends BaseApiController
{
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
        $users = User::where('tenant_id', $request->user()->tenant_id)
            ->get();

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
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($data);

        $user->assignRole($validated['role']);

        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return $this->successResponse(new UserResource($user), 'User created successfully', 201);
    }

    public function show(User $user)
    {
        $this->authorizeTenant($user);

        return $this->successResponse(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizeTenant($user);

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return $this->successResponse(new UserResource($user), 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $this->authorizeTenant($user);

        if ($user->id === auth()->id()) {
            return $this->errorResponse('You cannot delete yourself.', 422);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
}
