<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $categoryId = null, ?int $perPage = null)
    {
        $query = Product::with(['category', 'unit', 'supplier', 'stock'])
            ->where('tenant_id', $tenantId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $query->latest();

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById(int $id, array $with = [])
    {
        return Product::with($with)->where('id', $id)->first();
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update(int $id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);

        return $product;
    }

    public function delete(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return $product;
    }

    public function getNextCode(int $tenantId): string
    {
        $lastProduct = Product::where('tenant_id', $tenantId)
            ->whereRaw('code REGEXP "^[0-9]+$"')
            ->orderByRaw('CAST(code AS UNSIGNED) DESC')
            ->first();

        if (! $lastProduct) {
            return '10000001';
        }

        return (string) ((int) $lastProduct->code + 1);
    }
}