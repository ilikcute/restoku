<?php

namespace App\Http\Resources\Api\Finance;

use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'transaction_number' => $this->transaction_number,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'account' => new AccountResource($this->whenLoaded('account')),
            'user' => new UserResource($this->whenLoaded('user')),
            'expense_category' => $this->whenLoaded('expenseCategory'),
            'income_category' => $this->whenLoaded('incomeCategory'),
            'created_at' => $this->created_at,
        ];
    }
}
