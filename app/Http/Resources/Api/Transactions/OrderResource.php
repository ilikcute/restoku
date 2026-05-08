<?php

namespace App\Http\Resources\Api\Transactions;

use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'order_date' => $this->order_date,
            'customer_name' => $this->customer_name,
            'subtotal' => (float) $this->subtotal,
            'total_amount' => (float) $this->total_amount,
            'total_return' => (float) $this->total_return,
            'return_date' => $this->return_date,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'service_charge' => (float) $this->service_charge,
            'rounding' => (float) $this->rounding,
            'grand_total' => (float) $this->total_amount,
            'payment_method' => $this->payment_method,
            'paid_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
            'return_user' => new UserResource($this->whenLoaded('returnUser')),
            'shift' => new ShiftResource($this->whenLoaded('shift')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
