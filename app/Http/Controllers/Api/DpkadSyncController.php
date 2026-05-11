<?php

namespace App\Http\Controllers\Api;

use App\Services\DpkadSyncService;
use Illuminate\Http\Request;

class DpkadSyncController extends BaseApiController
{
    public function __construct(
        protected DpkadSyncService $syncService
    ) {}

    /**
     * Sync orders to DPKAD database.
     */
    public function sync(Request $request)
    {
        $orderIds = $request->input('order_ids', []);

        if (empty($orderIds)) {
            return $this->errorResponse('Tidak ada order yang dipilih.', 400);
        }

        $result = $this->syncService->syncOrders($orderIds);
        $syncedCount = $result['synced_count'];

        return $this->successResponse([
            'synced_count' => $syncedCount,
        ], "Berhasil menyinkronkan $syncedCount data ke DPKAD.");
    }
}
