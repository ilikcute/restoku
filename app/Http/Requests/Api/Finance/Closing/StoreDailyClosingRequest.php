<?php

namespace App\Http\Requests\Api\Finance\Closing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDailyClosingRequest extends FormRequest
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
        return [
            'closing_date' => 'required|date|unique:daily_closings,closing_date,NULL,id,tenant_id,'.auth()->user()->tenant_id,
            'notes' => 'nullable|string',
        ];
    }
}
