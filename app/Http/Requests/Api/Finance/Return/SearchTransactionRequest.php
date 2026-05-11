<?php

namespace App\Http\Requests\Api\Finance\Return;

use Illuminate\Foundation\Http\FormRequest;

class SearchTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string',
            'type' => 'required|in:order,purchase',
        ];
    }
}
