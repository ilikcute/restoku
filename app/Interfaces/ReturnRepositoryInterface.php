<?php

namespace App\Interfaces;

interface ReturnRepositoryInterface
{
    public function processOrderReturn(int $tenantId, int $orderId, array $items, int $userId, ?int $accountId);

    public function processPurchaseReturn(int $tenantId, int $purchaseId, array $items, int $userId, ?int $accountId);

    public function searchTransaction(int $tenantId, string $number, string $type);
}
