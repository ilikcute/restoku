<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PromotionResource;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends BaseApiController
{
    /**
     * Display a listing of active promotions.
     */
    public function index(Request $request)
    {
        // For the customer display, we only need active promotions
        // But we might also want to manage them in a dashboard
        $query = Promotion::where('tenant_id', $request->user()->tenant_id);

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        $promotions = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

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
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'integer',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $promotion = Promotion::create($validated);

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
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'integer',
        ]);

        $promotion->update($validated);

        return $this->successResponse(new PromotionResource($promotion), 'Promotion updated successfully');
    }

    /**
     * Remove the specified promotion.
     */
    public function destroy(Promotion $promotion)
    {
        $this->authorizeTenant($promotion);
        $promotion->delete();

        return $this->successResponse(null, 'Promotion deleted successfully');
    }
}
