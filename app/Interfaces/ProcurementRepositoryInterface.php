<?php

namespace App\Interfaces;

interface ProcurementRepositoryInterface
{
    public function getRecommendations(int $tenantId, int $days);

    public function getAlerts(int $tenantId);
}
