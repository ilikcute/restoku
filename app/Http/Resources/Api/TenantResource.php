<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'domain' => $this->domain,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' => $this->logo,
            'logo_url' => $this->logo_url,
            'footer_text' => $this->footer_text,
            'printer_use_default' => (bool) ($this->printer_use_default ?? true),
            'printer_connection_type' => $this->printer_connection_type,
            'printer_address' => $this->printer_address,
            'printer_port' => $this->printer_port,
            'is_active' => $this->is_active,
        ];
    }
}
