<?php

namespace App\Http\Requests\Api\Finance\Return;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreOrderReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where('tenant_id', $tenantId),
            ],
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('tenant_id', $tenantId),
            ],
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => [
                'required',
                Rule::exists('order_items', 'id')->where(function ($query) use ($tenantId) {
                    $query->whereIn('order_id', DB::table('orders')
                        ->select('id')
                        ->where('tenant_id', $tenantId));
                }),
            ],
            'items.*.quantity' => 'required|numeric|min:0',
        ];
    }
}
