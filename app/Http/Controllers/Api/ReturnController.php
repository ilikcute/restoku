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
    }

    /**
     * Store a purchase return (retur pembelian).
     */
    public function storePurchaseReturn(StorePurchaseReturnRequest $request)
    {
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
    }

    /**
     * Search for a transaction by number (Order or Purchase).
     */
    public function search(\App\Http\Requests\Api\Finance\Return\SearchTransactionRequest $request)
    {
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
