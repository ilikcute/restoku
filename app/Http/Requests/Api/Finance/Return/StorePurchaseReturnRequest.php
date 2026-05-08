<?php

namespace App\Http\Requests\Api\Finance\Return;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'purchase_id' => [
                'required',
                Rule::exists('purchases', 'id')->where('tenant_id', $tenantId),
            ],
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('tenant_id', $tenantId),
            ],
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => [
                'required',
                Rule::exists('purchase_items', 'id')->where(function ($query) use ($tenantId) {
                    $query->whereIn('purchase_id', DB::table('purchases')
                        ->select('id')
                        ->where('tenant_id', $tenantId));
                }),
            ],
            'items.*.quantity' => 'required|numeric|min:0',
        ];
    }
}
