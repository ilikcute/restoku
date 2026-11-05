<?php

use App\Interfaces\ReportRepositoryInterface;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('export excel does not expose internal exception details', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Permission::findOrCreate('view-reports');
    $user->givePermissionTo('view-reports');

    $this->app->bind(ReportRepositoryInterface::class, fn () => new class implements ReportRepositoryInterface
    {
        public function getReportData(int $tenantId, string $startDate, string $endDate): array
        {
            throw new RuntimeException('SQLSTATE[HY000]: leaked DB details');
        }

        public function aggregateTaxReportData(Collection $orders): Collection
        {
            return collect();
        }

        public function getDpkadOrders(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getDailyChart(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getTopProducts(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getTransactions(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getPurchases(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getSalesReturns(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getPurchaseReturns(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getSalesDetailItems(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getShiftSalesTotals(): Collection
        {
            return collect();
        }

        public function getShifts(int $tenantId, string $startDate, string $endDate): Collection
        {
            return collect();
        }

        public function getOrdersByStatus(int $tenantId, string $startDate, string $endDate, string $status = 'completed'): Collection
        {
            return collect();
        }
    });

    $response = $this->actingAs($user, 'sanctum')
        ->get('/api/v1/reports/export/excel');

    $response->assertStatus(500)
        ->assertJson([
            'status' => 'error',
            'message' => 'Gagal membuat file Excel.',
        ]);
});
