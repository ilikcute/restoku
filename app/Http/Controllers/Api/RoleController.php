<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\RoleResource;
use App\Interfaces\RoleRepositoryInterface;
use App\Http\Requests\Api\Role\StoreRoleRequest;
use App\Http\Requests\Api\Role\UpdateRoleRequest;
use Spatie\Permission\Models\Role;

class RoleController extends BaseApiController
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {}

    public function index()
    {
        $roles = $this->roleRepository->getAll();
        return $this->successResponse(RoleResource::collection($roles));
    }

    public function show(int $id)
    {
        $role = $this->roleRepository->findById($id);
        return $this->successResponse(new RoleResource($role));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleRepository->create($request->validated());
        return $this->successResponse(new RoleResource($role), 'Role created successfully', 201);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role = $this->roleRepository->update($role->id, $request->validated());
        return $this->successResponse(new RoleResource($role), 'Role permissions updated successfully');
    }

    public function destroy(int $id)
    {
        try {
            $this->roleRepository->delete($id);
            return $this->successResponse(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
