<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Public\StorePublicOrderRequest;
use App\Http\Resources\Api\Master\ProductResource;
use App\Interfaces\PublicMenuRepositoryInterface;
use Illuminate\Http\Request;

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
        $validated = request()->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
        ]);

        $products = $this->publicMenuRepository->getActiveProducts($validated['tenant_id']);

        return $this->successResponse(ProductResource::collection($products));
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
    public function fetchOrder(Request $request, $token)
    {
        $tenantId = $request->user()?->tenant_id;
        $data = $this->publicMenuRepository->getPendingOrderByToken($token, $tenantId);

        return $this->successResponse($data);
    }
}
