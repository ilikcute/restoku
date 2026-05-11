<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PromotionResource;
use App\Interfaces\PromotionRepositoryInterface;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends BaseApiController
{
    protected PromotionRepositoryInterface $promotionRepository;

    public function __construct(PromotionRepositoryInterface $promotionRepository)
    {
        $this->promotionRepository = $promotionRepository;
    }

    /**
     * Display a listing of active promotions.
     */
    public function index(Request $request)
    {
        $activeOnly = $request->boolean('active_only', true);
        $promotions = $this->promotionRepository->getAllByTenant($request->user()->tenant_id, $activeOnly);

        return $this->successResponse(PromotionResource::collection($promotions));
    }

    /**
     * Store a newly created promotion.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|string|in:announcement,discount_percentage,discount_fixed,buy_x_get_y',
            'discount_value' => 'required_if:type,discount_percentage,discount_fixed|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'applicable_type' => 'required|string|in:all,products,categories',
            'product_ids' => 'required_if:applicable_type,products|array',
            'product_ids.*' => 'exists:products,id',
            'category_ids' => 'required_if:applicable_type,categories|array',
            'category_ids.*' => 'exists:categories,id',
            'requirement_data' => 'nullable|array',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'integer',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $promotion = $this->promotionRepository->create($validated);

        return $this->successResponse(new PromotionResource($promotion), 'Promotion created successfully', 201);
    }

    /**
     * Update the specified promotion.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $this->authorizeTenant($promotion);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'sometimes|required|string|in:announcement,discount_percentage,discount_fixed,buy_x_get_y',
            'discount_value' => 'required_if:type,discount_percentage,discount_fixed|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'applicable_type' => 'sometimes|required|string|in:all,products,categories',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'requirement_data' => 'nullable|array',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'integer',
        ]);

        $promotion = $this->promotionRepository->update($promotion->id, $validated);

        return $this->successResponse(new PromotionResource($promotion), 'Promotion updated successfully');
    }

    /**
     * Remove the specified promotion.
     */
    public function destroy(Promotion $promotion)
    {
        $this->authorizeTenant($promotion);
        $this->promotionRepository->delete($promotion->id);

        return $this->successResponse(null, 'Promotion deleted successfully');
    }
}
