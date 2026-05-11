<?php

namespace App\Repositories;

use App\Interfaces\UnitRepositoryInterface;
use App\Models\Unit;

class UnitRepository implements UnitRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $perPage = null)
    {
        $query = Unit::with(['products' => function ($query) {
            $query->select('id', 'unit_id');
        }])
            ->where('tenant_id', $tenantId);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->orderBy('name');

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById(string $id, array $with = [])
    {
        return Unit::with($with)->where('id', $id)->first();
    }

    public function create(array $data)
    {
        return Unit::create($data);
    }

    public function update(string $id, array $data)
    {
        $unit = Unit::findOrFail($id);
        $unit->update($data);

        return $unit;
    }

    public function delete(string $id)
    {
        $unit = Unit::findOrFail($id);

        // Check if unit has products
        if ($unit->products()->count() > 0) {
            return false; // Cannot delete if has products
        }

        $unit->delete();

        return true;
    }
}
