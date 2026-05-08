<?php

namespace App\Http\Requests\Api\Transactions\Purchase;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
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
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where('tenant_id', $tenantId),
            ],
            'payment_method' => 'required|in:cash,credit',
            'account_id' => [
                'required_if:payment_method,cash',
                'nullable',
                Rule::exists('accounts', 'id')->where('tenant_id', $tenantId),
            ],
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where('tenant_id', $tenantId),
            ],
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
