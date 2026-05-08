<?php

namespace App\Http\Resources\Api\Inventory;

use App\Http\Resources\Api\Master\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockAdjustmentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'recorded_stock' => (float) $this->recorded_stock,
            'actual_stock' => (float) $this->actual_stock,
            'adjustment_quantity' => (float) $this->adjustment_quantity,
            'cost_price' => (float) $this->cost_price,
            'loss_value' => $this->adjustment_quantity < 0 ? abs($this->adjustment_quantity) * $this->cost_price : 0,
        ];
    }
}
