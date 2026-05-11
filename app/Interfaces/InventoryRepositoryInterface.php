<?php

namespace App\Interfaces;

interface InventoryRepositoryInterface
{
    public function getStockLevels(int $tenantId, ?int $categoryId, ?string $search);

    public function getMovements(int $tenantId, string $startDate, string $endDate, ?int $categoryId, ?string $search);

    public function getMovementDetail(int $tenantId, int $productId, string $startDate, string $endDate);

    public function processAdjustment(int $tenantId, int $userId, array $validated);

    public function getAdjustmentHistory(int $tenantId, int $perPage = 20);
}
