<?php

namespace App\Repositories;

use App\Interfaces\SupplierRepositoryInterface;
use App\Models\Supplier;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $perPage = null)
    {
        $query = Supplier::where('tenant_id', $tenantId);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById(string $id, array $with = [])
    {
        return Supplier::with($with)->find($id);
    }

    public function create(array $data)
    {
        return Supplier::create($data);
    }

    public function update(string $id, array $data)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);

        return $supplier;
    }

    public function delete(string $id)
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->products()->exists()) {
            return false;
        }

        return $supplier->delete();
    }
}