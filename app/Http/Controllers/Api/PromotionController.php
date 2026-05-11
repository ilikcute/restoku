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
    public function store(\App\Http\Requests\Api\Master\Promotion\StorePromotionRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;
        $promotion = $this->promotionRepository->create($validated);

        return $this->successResponse(new PromotionResource($promotion), 'Promotion created successfully', 201);
    }

    /**
     * Update the specified promotion.
     */
    public function update(\App\Http\Requests\Api\Master\Promotion\UpdatePromotionRequest $request, Promotion $promotion)
    {
        $this->authorizeTenant($promotion);

        $promotion = $this->promotionRepository->update($promotion->id, $request->validated());

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
