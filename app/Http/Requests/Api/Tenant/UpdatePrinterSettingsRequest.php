<?php

namespace App\Http\Requests\Api\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrinterSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'use_default' => 'required|boolean',
            'connection_type' => 'nullable|string|in:windows,network,file',
            'address' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
        ];

        if (! $this->input('use_default')) {
            $rules['connection_type'] = 'required|string|in:windows,network,file';
            $rules['address'] = 'required|string|max:255';
            $rules['port'] = 'required_if:connection_type,network|nullable|integer|min:1|max:65535';
        }

        return $rules;
    }
}
