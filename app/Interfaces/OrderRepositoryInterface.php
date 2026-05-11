<?php

namespace App\Interfaces;

use App\Models\Shift;

interface OrderRepositoryInterface
{
    public function getAllByTenant(int $tenantId, int $perPage = 20);

    public function processOrder(int $tenantId, int $userId, Shift $shift, array $validated, string $idempotencyKey);
}
