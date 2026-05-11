<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Unit\StoreUnitRequest;
use App\Http\Requests\Api\Master\Unit\UpdateUnitRequest;
use App\Http\Resources\Api\Master\UnitResource;
use App\Interfaces\UnitRepositoryInterface;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends BaseApiController
{
    public function __construct(
        protected UnitRepositoryInterface $unitRepository
    ) {}
    public function index(Request $request)
    {
        $units = $this->unitRepository->getAllByTenant(
            $request->user()->tenant_id,
            $request->search,
            $request->integer('per_page') ?: null
        );

        return $this->successResponse(UnitResource::collection($units));
    }

    public function store(StoreUnitRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $unit = $this->unitRepository->create($validated);

        return $this->successResponse(new UnitResource($unit), 'Unit created successfully', 201);
    }

    public function show(Unit $unit)
    {
        $this->authorizeTenant($unit);

        $unit->loadCount('products');

        return $this->successResponse(new UnitResource($unit));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->authorizeTenant($unit);

        $unit = $this->unitRepository->update($unit->id, $request->validated());

        return $this->successResponse(new UnitResource($unit), 'Unit updated successfully');
    }

    public function destroy(Unit $unit)
    {
        $this->authorizeTenant($unit);

        $deleted = $this->unitRepository->delete($unit->id);

        if (! $deleted) {
            return $this->errorResponse('Cannot delete unit with existing products.', 422);
        }

        return $this->successResponse(null, 'Unit deleted successfully');
    }
}
