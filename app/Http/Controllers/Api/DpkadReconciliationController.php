<?php

namespace App\Http\Controllers\Api;

use App\Services\DpkadReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DpkadReconciliationController extends BaseApiController
{
    public function __construct(
        protected DpkadReconciliationService $reconciliationService
    ) {}

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));

            $data = $this->reconciliationService->getReconciliation($user->tenant_id, $startDate, $endDate);

            return $this->successResponse($data, 'Berhasil memuat data rekonsiliasi DPKAD.');
        } catch (\Exception $e) {
            Log::error('Dpkad Reconciliation Error: '.$e->getMessage());

            return $this->errorResponse('Gagal memuat data rekonsiliasi: '.$e->getMessage(), 500);
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer',
        ]);

        try {
            $orderIds = $request->input('order_ids', []);
            $result = $this->reconciliationService->syncOrders($orderIds);

            return $this->successResponse($result, "Berhasil menyinkronkan {$result['synced_count']} transaksi ke database DPKAD.");
        } catch (\Exception $e) {
            Log::error('Dpkad Reconciliation Sync Error: '.$e->getMessage());

            return $this->errorResponse('Gagal menyinkronkan transaksi ke DPKAD: '.$e->getMessage(), 500);
        }
    }
}

