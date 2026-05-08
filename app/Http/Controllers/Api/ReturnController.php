<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Finance\Return\StoreOrderReturnRequest;
use App\Http\Requests\Api\Finance\Return\StorePurchaseReturnRequest;
use App\Http\Resources\Api\Transactions\OrderResource;
use App\Http\Resources\Api\Transactions\PurchaseResource;
use App\Models\Order;
use App\Models\Purchase;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;

class ReturnController extends BaseApiController
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Store a sales return (retur penjualan).
     */
    public function storeOrderReturn(StoreOrderReturnRequest $request)
    {
        try {
            $order = Order::where('tenant_id', $request->user()->tenant_id)
                ->with('items')
                ->findOrFail($request->order_id);

            $this->inventoryService->processOrderReturn(
                $order,
                $request->items,
                $request->user()->id,
                $request->account_id
            );

            return $this->successResponse(
                new OrderResource($order->load(['items.product', 'user', 'returnUser'])),
                'Retur penjualan berhasil diproses.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Store a purchase return (retur pembelian).
     */
    public function storePurchaseReturn(StorePurchaseReturnRequest $request)
    {
        try {
            $purchase = Purchase::where('tenant_id', $request->user()->tenant_id)
                ->with('items')
                ->findOrFail($request->purchase_id);

            $this->inventoryService->processPurchaseReturn(
                $purchase,
                $request->items,
                $request->user()->id,
                $request->account_id
            );

            return $this->successResponse(
                new PurchaseResource($purchase->load(['items.product', 'user', 'supplier', 'returnUser'])),
                'Retur pembelian berhasil diproses.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Search for a transaction by number (Order or Purchase).
     */
    public function search(Request $request)
    {
        $request->validate([
            'number' => 'required|string',
            'type' => 'required|in:order,purchase',
        ]);

        $tenantId = $request->user()->tenant_id;
        $number = $request->number;

        if ($request->type === 'order') {
            $data = Order::where('tenant_id', $tenantId)
                ->where('order_number', $number)
                ->with(['items.product', 'user', 'returnUser'])
                ->first();

            if (! $data) {
                return $this->errorResponse('Transaksi tidak ditemukan.', 404);
            }

            return $this->successResponse(new OrderResource($data));
        } else {
            $data = Purchase::where('tenant_id', $tenantId)
                ->where('purchase_number', $number)
                ->with(['items.product', 'user', 'supplier', 'returnUser'])
                ->first();

            if (! $data) {
                return $this->errorResponse('Transaksi tidak ditemukan.', 404);
            }

            return $this->successResponse(new PurchaseResource($data));
        }
    }
}
