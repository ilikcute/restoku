<?php

namespace App\Interfaces;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Collection;

interface PromotionRepositoryInterface
{
    public function getAllByTenant(int $tenantId, bool $activeOnly = true): Collection;

    public function findById(int $id): Promotion;

    public function create(array $data): Promotion;

    public function update(int $id, array $data): Promotion;

    public function delete(int $id): bool;
}
