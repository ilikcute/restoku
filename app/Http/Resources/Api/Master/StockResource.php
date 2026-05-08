<?php

namespace App\Http\Resources\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'current_stock' => (float) $this->current_stock,
            'minimum_stock' => (float) $this->minimum_stock,
            'is_low_stock' => $this->current_stock <= $this->minimum_stock,
        ];
    }
}
