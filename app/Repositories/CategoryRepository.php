<?php

namespace App\Repositories;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $perPage = null)
    {
        $query = Category::with(['products' => function($query) {
                $query->select('id', 'category_id');
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
        return Category::with($with)->where('id', $id)->first();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update(string $id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category;
    }

    public function delete(string $id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return false; // Cannot delete if has products
        }

        $category->delete();

        return true;
    }
}
