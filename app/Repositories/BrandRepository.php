<?php

namespace App\Repositories;

use App\Interfaces\BrandRepositoryInterface;
use App\Models\Brand;

class BrandRepository implements BrandRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $perPage = null)
    {
        $query = Brand::where('tenant_id', $tenantId);

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
        return Brand::with($with)->findOrFail($id);
    }

    public function create(array $data)
    {
        return Brand::create($data);
    }

    public function update(string $id, array $data)
    {
        $brand = Brand::findOrFail($id);
        $brand->update($data);

        return $brand;
    }

    public function delete(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return true;
    }
}
