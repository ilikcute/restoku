<?php

namespace App\Http\Resources\Api\Transactions;

use App\Http\Resources\Api\Master\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'quantity' => (float) $this->quantity,
            'return_quantity' => (float) $this->return_quantity,
            'return_amount' => (float) $this->return_amount,
            'price' => (float) $this->price,
            'product_name' => $this->product_name,
            'subtotal' => (float) $this->subtotal,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
