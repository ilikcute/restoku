<?php

namespace App\Http\Resources\Api\Transactions;

use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
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
            'user_id' => $this->user_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'starting_cash' => (float) $this->starting_cash,
            'ending_cash' => (float) $this->ending_cash,
            'total_sales' => (float) $this->total_sales,
            'cash_sales' => (float) $this->cash_sales,
            'non_cash_sales' => (float) $this->non_cash_sales,
            'total_income' => (float) $this->total_income,
            'total_expense' => (float) $this->total_expense,
            'total_return' => (float) $this->total_return,
            'total_expected' => (float) $this->total_expected,
            'difference' => (float) $this->difference,
            'status' => $this->status,
            'is_expired' => $this->is_expired ?? false,
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
