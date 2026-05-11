<?php

namespace App\Interfaces;

interface DailyClosingRepositoryInterface
{
    public function getAllByTenant(int $tenantId, int $perPage = 20);

    public function processClosing(int $tenantId, int $userId, array $validated);

    public function getClosingById(int $id);
}
