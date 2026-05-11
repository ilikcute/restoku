<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Finance\Return\StoreOrderReturnRequest;
use App\Http\Requests\Api\Finance\Return\StorePurchaseReturnRequest;
use App\Http\Resources\Api\Transactions\OrderResource;
use App\Http\Resources\Api\Transactions\PurchaseResource;
use App\Interfaces\ReturnRepositoryInterface;
use Exception;
use Illuminate\Http\Request;

class ReturnController extends BaseApiController
{
    protected ReturnRepositoryInterface $returnRepository;

    public function __construct(ReturnRepositoryInterface $returnRepository)
    {
        $this->returnRepository = $returnRepository;
    }

    /**
     * Store a sales return (retur penjualan).
     */
    public function storeOrderReturn(StoreOrderReturnRequest $request)
    {
        try {
            $order = $this->returnRepository->processOrderReturn(
                $request->user()->tenant_id,
                $request->order_id,
                $request->items,
                $request->user()->id,
                $request->account_id
            );

            return $this->successResponse(
                new OrderResource($order),
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
            $purchase = $this->returnRepository->processPurchaseReturn(
                $request->user()->tenant_id,
                $request->purchase_id,
                $request->items,
                $request->user()->id,
                $request->account_id
            );

            return $this->successResponse(
                new PurchaseResource($purchase),
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

        $data = $this->returnRepository->searchTransaction(
            $request->user()->tenant_id,
            $request->number,
            $request->type
        );

        if (! $data) {
            return $this->errorResponse('Transaksi tidak ditemukan.', 404);
        }

        if ($request->type === 'order') {
            return $this->successResponse(new OrderResource($data));
        } else {
            return $this->successResponse(new PurchaseResource($data));
        }
    }
}
