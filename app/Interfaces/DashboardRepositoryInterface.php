<?php

namespace App\Interfaces;

interface DashboardRepositoryInterface
{
    public function getStats(int $tenantId): array;
}
