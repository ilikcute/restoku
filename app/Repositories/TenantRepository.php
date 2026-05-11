<?php

namespace App\Repositories;

use App\Interfaces\TenantRepositoryInterface;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;

class TenantRepository implements TenantRepositoryInterface
{
    public function find(int $id): ?Tenant
    {
        return Tenant::find($id);
    }

    public function updateProfile(Tenant $tenant, array $data): Tenant
    {
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old logo
            if ($tenant->logo) {
                Storage::disk('public')->delete($tenant->logo);
            }
            $data['logo'] = $data['logo']->store('tenants/logos', 'public');
        }

        $tenant->update($data);

        return $tenant;
    }

    public function updatePrinterSettings(Tenant $tenant, array $data): Tenant
    {
        $updateData = [
            'printer_use_default' => $data['use_default'],
        ];

        if ($data['use_default']) {
            $updateData['printer_connection_type'] = null;
            $updateData['printer_address'] = null;
            $updateData['printer_port'] = null;
        } else {
            $updateData['printer_connection_type'] = $data['connection_type'];
            $updateData['printer_address'] = $data['address'];
            $updateData['printer_port'] = ($data['connection_type'] === 'network') ? $data['port'] : null;
        }

        $tenant->update($updateData);

        return $tenant->refresh();
    }

    public function updateKitchenPrinterSettings(Tenant $tenant, array $data): Tenant
    {
        $tenant->update([
            'kitchen_printer_connection_type' => $data['kitchen_connection_type'] ?? null,
            'kitchen_printer_address' => $data['kitchen_address'] ?? null,
            'kitchen_printer_port' => $data['kitchen_port'] ?? null,
        ]);

        return $tenant->refresh();
    }
}
