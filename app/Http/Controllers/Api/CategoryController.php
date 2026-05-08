<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Category\StoreCategoryRequest;
use App\Http\Requests\Api\Master\Category\UpdateCategoryRequest;
use App\Http\Resources\Api\Master\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    public function index(Request $request)
    {
        $categories = Category::where('tenant_id', $request->user()->tenant_id)->get();

        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;
        $category = Category::create($validated);

        return $this->successResponse(new CategoryResource($category), 'Category created successfully', 201);
    }

    public function show(Category $category)
    {
        $this->authorizeTenant($category);

        return $this->successResponse(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorizeTenant($category);
        $category->update($request->validated());

        return $this->successResponse(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        $this->authorizeTenant($category);
        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
