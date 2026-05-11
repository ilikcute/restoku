<?php

namespace App\Interfaces;

use App\Models\Shift;

interface ShiftRepositoryInterface
{
    public function getAllByTenant(int $tenantId, int $perPage = 20);

    public function getById(int $id): Shift;

    public function getCurrentShift(int $tenantId, int $userId): ?Shift;

    public function openShift(int $tenantId, int $userId, array $data): Shift;

    public function closeShift(int $tenantId, int $userId, array $data): Shift;
}
