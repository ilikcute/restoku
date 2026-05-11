<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Category\StoreCategoryRequest;
use App\Http\Requests\Api\Master\Category\UpdateCategoryRequest;
use App\Http\Resources\Api\Master\CategoryResource;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    public function index(Request $request)
    {
        $categories = $this->categoryRepository->getAllByTenant(
            $request->user()->tenant_id,
            $request->search,
            $request->integer('per_page') ?: null
        );

        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $category = $this->categoryRepository->create($validated);

        return $this->successResponse(new CategoryResource($category), 'Category created successfully', 201);
    }

    public function show(Category $category)
    {
        $this->authorizeTenant($category);

        $category->loadCount('products');

        return $this->successResponse(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorizeTenant($category);

        $category = $this->categoryRepository->update($category->id, $request->validated());

        return $this->successResponse(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        $this->authorizeTenant($category);

        $deleted = $this->categoryRepository->delete($category->id);

        if (! $deleted) {
            return $this->errorResponse('Cannot delete category with existing products.', 422);
        }

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
