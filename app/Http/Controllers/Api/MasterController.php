<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\Master\CategoryResource;
use App\Http\Resources\Api\Master\ProductResource;
use App\Http\Resources\Api\Master\SupplierResource;
use App\Http\Resources\Api\Master\UnitResource;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\SupplierRepositoryInterface;
use App\Interfaces\UnitRepositoryInterface;
use Illuminate\Http\Request;

class MasterController extends BaseApiController
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository,
        protected UnitRepositoryInterface $unitRepository,
        protected SupplierRepositoryInterface $supplierRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function options(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        return $this->successResponse([
            'categories' => CategoryResource::collection($this->categoryRepository->getAllByTenant($tenantId)),
            'units' => UnitResource::collection($this->unitRepository->getAllByTenant($tenantId)),
            'suppliers' => SupplierResource::collection($this->supplierRepository->getAllByTenant($tenantId)),
        ]);
    }

    public function initProducts(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $paginatedProducts = $this->productRepository->getAllByTenant($tenantId, perPage: 10);

        return $this->successResponse([
            'products' => [
                'data' => ProductResource::collection($paginatedProducts),
                'meta' => [
                    'total' => $paginatedProducts->total(),
                    'per_page' => $paginatedProducts->perPage(),
                    'current_page' => $paginatedProducts->currentPage(),
                    'last_page' => $paginatedProducts->lastPage(),
                ],
            ],
            'options' => [
                'categories' => CategoryResource::collection($this->categoryRepository->getAllByTenant($tenantId)),
                'units' => UnitResource::collection($this->unitRepository->getAllByTenant($tenantId)),
                'suppliers' => SupplierResource::collection($this->supplierRepository->getAllByTenant($tenantId)),
            ],
        ]);
    }
}
