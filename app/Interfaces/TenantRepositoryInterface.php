<?php

namespace App\Interfaces;

use App\Models\Tenant;

interface TenantRepositoryInterface
{
    /**
     * Get tenant by ID.
     */
    public function find(int $id): ?Tenant;

    /**
     * Update tenant business profile.
     */
    public function updateProfile(Tenant $tenant, array $data): Tenant;

    /**
     * Update printer settings.
     */
    public function updatePrinterSettings(Tenant $tenant, array $data): Tenant;

    /**
     * Update kitchen printer settings.
     */
    public function updateKitchenPrinterSettings(Tenant $tenant, array $data): Tenant;
}
