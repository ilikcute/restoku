<?php

namespace App\Http\Resources\Api\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category ? $this->category->name : 'Uncategorized',
            'unit' => $this->unit ? $this->unit->name : '-',
            'stock_type' => $this->stock_type,
            'is_tracked' => $this->isTracked(),
            'cost_price' => (float) $this->cost_price,
            'price' => (float) $this->price,
            'current_stock' => $this->stock ? (float) $this->stock->current_stock : 0,
            'minimum_stock' => (float) $this->minimum_stock,
            'maximum_stock' => (float) $this->maximum_stock,
            'reorder_quantity' => (float) $this->reorder_quantity,
            'safety_stock' => (float) $this->safety_stock,
            'lead_time' => (int) $this->lead_time,
            'is_low_stock' => $this->isTracked() && ($this->stock ? $this->stock->current_stock : 0) <= $this->minimum_stock,
            'is_over_stock' => $this->isTracked() && $this->maximum_stock > 0 && ($this->stock ? $this->stock->current_stock : 0) >= $this->maximum_stock,
            'last_updated' => $this->stock ? $this->stock->updated_at : $this->created_at,
        ];
    }
}
