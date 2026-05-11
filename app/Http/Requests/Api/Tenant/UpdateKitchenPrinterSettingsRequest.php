<?php

namespace App\Http\Requests\Api\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKitchenPrinterSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kitchen_connection_type' => 'nullable|string|in:windows,network,file',
            'kitchen_address' => 'nullable|string|max:255',
            'kitchen_port' => 'nullable|integer|min:1|max:65535',
        ];
    }
}
