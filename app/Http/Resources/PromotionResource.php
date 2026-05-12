<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'discount_value' => (float) $this->discount_value,
            'min_purchase' => (float) $this->min_purchase,
            'max_discount' => (float) $this->max_discount,
            'applicable_type' => $this->applicable_type,
            'requirement_data' => $this->requirement_data,
            'product_ids' => $this->whenLoaded('products', function () {
                return $this->products->pluck('id');
            }),
            'category_ids' => $this->whenLoaded('categories', function () {
                return $this->categories->pluck('id');
            }),
            'is_stackable' => (bool) $this->is_stackable,
            'is_multiple' => (bool) $this->is_multiple,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'priority' => $this->priority,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
