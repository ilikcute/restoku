<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface ReportRepositoryInterface
{
    public function getReportData(int $tenantId, string $startDate, string $endDate): array;

    public function aggregateTaxReportData(Collection $orders): Collection;

    public function getDpkadOrders(int $tenantId, string $startDate, string $endDate): Collection;

    public function getDailyChart(int $tenantId, string $startDate, string $endDate): Collection;

    public function getTopProducts(int $tenantId, string $startDate, string $endDate): Collection;

    public function getTransactions(int $tenantId, string $startDate, string $endDate): Collection;

    public function getPurchases(int $tenantId, string $startDate, string $endDate): Collection;

    public function getSalesReturns(int $tenantId, string $startDate, string $endDate): Collection;

    public function getPurchaseReturns(int $tenantId, string $startDate, string $endDate): Collection;

    public function getSalesDetailItems(int $tenantId, string $startDate, string $endDate): Collection;

    public function getShiftSalesTotals(): Collection;

    public function getShifts(int $tenantId, string $startDate, string $endDate): Collection;

    public function getOrdersByStatus(int $tenantId, string $startDate, string $endDate, string $status = 'completed'): Collection;
}
