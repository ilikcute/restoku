<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Public\StorePublicOrderRequest;
use App\Http\Resources\Api\Master\ProductResource;
use App\Interfaces\PublicMenuRepositoryInterface;

class PublicMenuController extends BaseApiController
{
    protected PublicMenuRepositoryInterface $publicMenuRepository;

    public function __construct(PublicMenuRepositoryInterface $publicMenuRepository)
    {
        $this->publicMenuRepository = $publicMenuRepository;
    }

    /**
     * Get list of products for public menu
     */
    public function products()
    {
        try {
            $products = $this->publicMenuRepository->getActiveProducts();

            return $this->successResponse(ProductResource::collection($products));
        } catch (\Exception $e) {
            \Log::error('Public Menu Error: '.$e->getMessage());

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a self-order draft from customer
     */
    public function storeOrder(StorePublicOrderRequest $request)
    {
        $result = $this->publicMenuRepository->createPendingOrder($request->validated());

        return $this->successResponse($result, 'Pesanan berhasil dikirim.');
    }

    /**
     * Fetch pending order data (to be used by POS)
     */
    public function fetchOrder($token)
    {
        $data = $this->publicMenuRepository->getPendingOrderByToken($token);

        return $this->successResponse($data);
    }
}
