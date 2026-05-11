<?php

namespace App\Interfaces;

interface PurchaseRepositoryInterface
{
    public function getAllByTenant(int $tenantId, int $perPage = 20);

    public function createPurchase(int $tenantId, int $userId, array $validated);
}
