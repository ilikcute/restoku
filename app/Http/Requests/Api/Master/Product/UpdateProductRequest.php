<?php

namespace App\Http\Requests\Api\Master\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'brand_name' => 'nullable|string|max:255',
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where('tenant_id', $tenantId),
            ],
            'category_id' => [
                'sometimes',
                'required',
                Rule::exists('categories', 'id')->where('tenant_id', $tenantId),
            ],
            'unit_id' => [
                'sometimes',
                'required',
                Rule::exists('units', 'id')->where('tenant_id', $tenantId),
            ],
            'code' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('products')->where('tenant_id', $tenantId)->ignore($this->product->id),
            ],
            'name' => 'sometimes|required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'price' => 'sometimes|required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'ojol_price' => 'nullable|numeric|min:0',
            'ojol_discount' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'wholesale_discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'service_charge_rate' => 'nullable|numeric|min:0|max:100',
            'stock_type' => 'sometimes|required|in:trackable,untrackable',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'safety_stock' => 'nullable|numeric|min:0',
            'lead_time' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
