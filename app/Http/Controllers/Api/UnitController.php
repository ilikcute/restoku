<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Unit\StoreUnitRequest;
use App\Http\Requests\Api\Master\Unit\UpdateUnitRequest;
use App\Http\Resources\Api\Master\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends BaseApiController
{
    public function index(Request $request)
    {
        $units = Unit::where('tenant_id', $request->user()->tenant_id)->get();

        return $this->successResponse(UnitResource::collection($units));
    }

    public function store(StoreUnitRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;
        $unit = Unit::create($validated);

        return $this->successResponse(new UnitResource($unit), 'Unit created successfully', 201);
    }

    public function show(Unit $unit)
    {
        $this->authorizeTenant($unit);

        return $this->successResponse(new UnitResource($unit));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->authorizeTenant($unit);
        $unit->update($request->validated());

        return $this->successResponse(new UnitResource($unit), 'Unit updated successfully');
    }

    public function destroy(Unit $unit)
    {
        $this->authorizeTenant($unit);
        $unit->delete();

        return $this->successResponse(null, 'Unit deleted successfully');
    }
}
