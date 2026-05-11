<?php

namespace App\Interfaces;

interface PublicMenuRepositoryInterface
{
    public function getActiveProducts(int $tenantId);

    public function createPendingOrder(array $validated): array;

    public function getPendingOrderByToken(string $token, ?int $tenantId = null): array;
}
