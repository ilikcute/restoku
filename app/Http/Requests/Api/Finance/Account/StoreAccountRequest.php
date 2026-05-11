<?php

namespace App\Http\Requests\Api\Finance\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'balance' => 'numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
