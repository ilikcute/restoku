<?php

namespace App\Repositories;

use App\Interfaces\PromotionRepositoryInterface;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Collection;

class PromotionRepository implements PromotionRepositoryInterface
{
    public function getAllByTenant(int $tenantId, bool $activeOnly = true): Collection
    {
        $query = Promotion::where('tenant_id', $tenantId)
            ->with(['products', 'categories']);

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): Promotion
    {
        return Promotion::with(['products', 'categories'])->findOrFail($id);
    }

    public function create(array $data): Promotion
    {
        return DB::transaction(function () use ($data) {
            $promotion = Promotion::create($data);

            if (isset($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            if (isset($data['category_ids'])) {
                $promotion->categories()->sync($data['category_ids']);
            }

            return $promotion->load(['products', 'categories']);
        });
    }

    public function update(int $id, array $data): Promotion
    {
        return DB::transaction(function () use ($id, $data) {
            $promotion = $this->findById($id);
            $promotion->update($data);

            if (isset($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            if (isset($data['category_ids'])) {
                $promotion->categories()->sync($data['category_ids']);
            }

            return $promotion->load(['products', 'categories']);
        });
    }

    public function delete(int $id): bool
    {
        $promotion = $this->findById($id);

        return $promotion->delete();
    }
}
