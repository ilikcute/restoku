<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\Master\CategoryResource;
use App\Http\Resources\Api\Master\ProductResource;
use App\Http\Resources\Api\Master\SupplierResource;
use App\Http\Resources\Api\Master\UnitResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\Request;

class MasterController extends BaseApiController
{
    public function options(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        return $this->successResponse([
            'categories' => CategoryResource::collection(Category::where('tenant_id', $tenantId)->get()),
            'units' => UnitResource::collection(Unit::where('tenant_id', $tenantId)->get()),
            'suppliers' => SupplierResource::collection(Supplier::where('tenant_id', $tenantId)->get()),
        ]);
    }

    public function initProducts(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $paginatedProducts = Product::where('tenant_id', $tenantId)
            ->with(['category', 'unit', 'supplier', 'stock'])
            ->paginate(10);

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
                'categories' => CategoryResource::collection(Category::where('tenant_id', $tenantId)->get()),
                'units' => UnitResource::collection(Unit::where('tenant_id', $tenantId)->get()),
                'suppliers' => SupplierResource::collection(Supplier::where('tenant_id', $tenantId)->get()),
            ],
        ]);
    }
}
