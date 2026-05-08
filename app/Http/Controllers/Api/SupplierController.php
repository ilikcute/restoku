<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Supplier\StoreSupplierRequest;
use App\Http\Requests\Api\Master\Supplier\UpdateSupplierRequest;
use App\Http\Resources\Api\Master\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends BaseApiController
{
    public function index(Request $request)
    {
        $suppliers = Supplier::where('tenant_id', $request->user()->tenant_id)->get();

        return $this->successResponse(SupplierResource::collection($suppliers));
    }

    public function store(StoreSupplierRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;
        $supplier = Supplier::create($validated);

        return $this->successResponse(new SupplierResource($supplier), 'Supplier created successfully', 201);
    }

    public function show(Supplier $supplier)
    {
        $this->authorizeTenant($supplier);

        return $this->successResponse(new SupplierResource($supplier));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->authorizeTenant($supplier);
        $supplier->update($request->validated());

        return $this->successResponse(new SupplierResource($supplier), 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorizeTenant($supplier);
        $supplier->delete();

        return $this->successResponse(null, 'Supplier deleted successfully');
    }
}
