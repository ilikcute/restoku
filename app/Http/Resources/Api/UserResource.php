<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'users',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                'avatar_url' => $this->avatar_url,
                'phone' => $this->phone,
                'role' => $this->role->value,
                'is_active' => $this->is_active,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'roles' => $this->roles->pluck('name'),
                'permissions' => $this->getAllPermissions()->pluck('name'),
            ],
            'relationships' => [
                'tenant' => [
                    'data' => $this->whenLoaded('tenant', function () {
                        return [
                            'type' => 'tenants',
                            'id' => (string) $this->tenant_id,
                            'attributes' => [
                                'name' => $this->tenant->name,
                                'slug' => $this->tenant->slug,
                                'logo_url' => $this->tenant->logo_url,
                            ],
                        ];
                    }),
                ],
            ],
            'links' => [
                'self' => route('api.v1.users.show', $this),
            ],
        ];
    }
}
