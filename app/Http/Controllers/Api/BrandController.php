<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Brand\StoreBrandRequest;
use App\Http\Requests\Api\Master\Brand\UpdateBrandRequest;
use App\Http\Resources\Api\Master\BrandResource;
use App\Interfaces\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends BaseApiController
{
    public function __construct(
        protected BrandRepositoryInterface $brandRepository
    ) {}

    public function index(Request $request)
    {
        $brands = $this->brandRepository->getAllByTenant(
            $request->user()->tenant_id,
            $request->search,
            $request->integer('per_page') ?: null
        );

        return $this->successResponse(BrandResource::collection($brands));
    }

    public function store(StoreBrandRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $brand = $this->brandRepository->create($validated);

        return $this->successResponse(new BrandResource($brand), 'Brand created successfully', 201);
    }

    public function show(Brand $brand)
    {
        $this->authorizeTenant($brand);

        return $this->successResponse(new BrandResource($brand));
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $this->authorizeTenant($brand);

        $brand = $this->brandRepository->update($brand->id, $request->validated());

        return $this->successResponse(new BrandResource($brand), 'Brand updated successfully');
    }

    public function destroy(Brand $brand)
    {
        $this->authorizeTenant($brand);

        $this->brandRepository->delete($brand->id);

        return $this->successResponse(null, 'Brand deleted successfully');
    }
}
