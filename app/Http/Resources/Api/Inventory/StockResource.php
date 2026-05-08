<?php

namespace App\Http\Resources\Api\Inventory;

use App\Http\Resources\Api\Master\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'current_stock' => (float) $this->current_stock,
            'minimum_stock' => (float) $this->minimum_stock,
            'last_updated' => $this->updated_at,
            'is_low_stock' => $this->current_stock <= $this->minimum_stock,
        ];
    }
}
