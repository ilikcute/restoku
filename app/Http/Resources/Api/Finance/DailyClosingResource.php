<?php

namespace App\Http\Resources\Api\Finance;

use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyClosingResource extends JsonResource
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
            'closing_date' => $this->closing_date,
            'total_revenue' => (float) $this->total_revenue,
            'total_transactions' => (int) $this->total_transactions,
            'total_discounts' => (float) $this->total_discounts,
            'total_tax' => (float) $this->total_tax,
            'total_income' => (float) $this->total_income,
            'total_expense' => (float) $this->total_expense,
            'net_revenue' => (float) $this->net_revenue,
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
