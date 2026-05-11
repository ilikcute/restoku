<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Supplier\StoreSupplierRequest;
use App\Http\Requests\Api\Master\Supplier\UpdateSupplierRequest;
use App\Http\Resources\Api\Master\SupplierResource;
use App\Interfaces\SupplierRepositoryInterface;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends BaseApiController
{
    public function __construct(
        protected SupplierRepositoryInterface $supplierRepository
    ) {}

    public function index(Request $request)
    {
        $suppliers = $this->supplierRepository->getAllByTenant(
            $request->user()->tenant_id,
            $request->search,
            $request->integer('per_page') ?: null
        );

        return $this->successResponse(SupplierResource::collection($suppliers));
    }

    public function store(StoreSupplierRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $supplier = $this->supplierRepository->create($validated);

        return $this->successResponse(new SupplierResource($supplier), 'Supplier created successfully', 201);
    }

    public function show(Supplier $supplier)
    {
        $this->authorizeTenant($supplier);

        $supplier->loadCount('products');

        return $this->successResponse(new SupplierResource($supplier));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->authorizeTenant($supplier);

        $supplier = $this->supplierRepository->update($supplier->id, $request->validated());

        return $this->successResponse(new SupplierResource($supplier), 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorizeTenant($supplier);

        $deleted = $this->supplierRepository->delete($supplier->id);

        if (!$deleted) {
            return $this->errorResponse('Cannot delete supplier with existing products.', 422);
        }

        return $this->successResponse(null, 'Supplier deleted successfully');
    }
}
