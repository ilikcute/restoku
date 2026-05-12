<?php

namespace App\Repositories;

use App\Interfaces\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleRepository implements RoleRepositoryInterface
{
    public function getAll()
    {
        return Role::with('permissions')->get();
    }

    public function findById(int $id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role;
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $role = Role::findOrFail($id);
            
            if (isset($data['name'])) {
                $role->name = $data['name'];
                $role->save();
            }

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            // Clear permission cache
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return $role;
        });
    }

    public function delete(int $id)
    {
        $role = Role::findOrFail($id);
        
        if (in_array($role->name, ['admin', 'manager', 'cashier'])) {
            throw new \Exception('Cannot delete system roles.');
        }

        return $role->delete();
    }
}
