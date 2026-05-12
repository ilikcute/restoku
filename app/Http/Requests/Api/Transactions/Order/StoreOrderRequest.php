<?php

namespace App\Http\Requests\Api\Transactions\Order;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'idempotency_key' => 'required|string|uuid',
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where('tenant_id', $tenantId),
            ],
            'customer_name' => 'nullable|string|max:255',
            'table_number' => 'nullable|string',
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('tenant_id', $tenantId),
            ],
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where('tenant_id', $tenantId),
            ],
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'nullable|numeric',
            'items.*.discount_amount' => 'nullable|numeric',
            'items.*.notes' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'order_type' => 'nullable|in:regular,ojol,wholesale',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
