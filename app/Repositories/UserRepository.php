<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $perPage = null)
    {
        $query = User::with(['roles', 'permissions'])
            ->where('tenant_id', $tenantId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById(string $id, array $with = [])
    {
        return User::with($with)->where('id', $id)->first();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            if (isset($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            }

            return $user;
        });
    }

    public function update(string $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);
            $user->update($data);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            if (isset($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            }

            return $user;
        });
    }

    public function delete(string $id)
    {
        return DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);
            $user->delete();

            return $user;
        });
    }
}
