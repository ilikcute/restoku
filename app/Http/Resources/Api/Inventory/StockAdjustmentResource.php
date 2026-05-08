<?php

namespace App\Http\Resources\Api\Inventory;

use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'adjustment_number' => $this->adjustment_number,
            'adjustment_date' => $this->adjustment_date,
            'notes' => $this->notes,
            'status' => $this->status,
            'total_loss_amount' => (float) $this->total_loss_amount,
            'user' => new UserResource($this->whenLoaded('user')),
            'items' => StockAdjustmentItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
