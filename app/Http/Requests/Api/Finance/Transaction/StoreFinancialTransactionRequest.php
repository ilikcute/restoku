<?php

namespace App\Http\Requests\Api\Finance\Transaction;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialTransactionRequest extends FormRequest
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
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('tenant_id', $tenantId),
            ],
            'expense_category_id' => [
                'nullable',
                Rule::exists('expense_categories', 'id')->where('tenant_id', $tenantId),
            ],
            'income_category_id' => [
                'nullable',
                Rule::exists('income_categories', 'id')->where('tenant_id', $tenantId),
            ],
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
        ];
    }
}
