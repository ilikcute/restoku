<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Exception;
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
        DB::beginTransaction();
        try {
            $user = User::create($data);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            if (isset($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            }

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function update(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->update($data);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            if (isset($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            }

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function delete(string $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->delete();
            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
