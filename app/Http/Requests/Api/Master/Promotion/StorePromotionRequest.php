<?php

namespace App\Http\Requests\Api\Master\Promotion;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|string|in:announcement,discount_percentage,discount_fixed,buy_x_get_y',
            'discount_value' => 'required_if:type,discount_percentage,discount_fixed|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'applicable_type' => 'required|string|in:all,products,categories',
            'product_ids' => 'required_if:applicable_type,products|array',
            'product_ids.*' => 'exists:products,id',
            'category_ids' => 'required_if:applicable_type,categories|array',
            'category_ids.*' => 'exists:categories,id',
            'requirement_data' => 'nullable|array',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'integer',
            'is_stackable' => 'boolean',
            'is_multiple' => 'boolean',
        ];
    }
}
