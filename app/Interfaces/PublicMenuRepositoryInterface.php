<?php

namespace App\Interfaces;

interface PublicMenuRepositoryInterface
{
    public function getActiveProducts();

    public function createPendingOrder(array $validated): array;

    public function getPendingOrderByToken(string $token): array;
}
