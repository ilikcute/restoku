<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Tenant\UpdateKitchenPrinterSettingsRequest;
use App\Http\Requests\Api\Tenant\UpdatePrinterSettingsRequest;
use App\Http\Requests\Api\Tenant\UpdateTenantRequest;
use App\Http\Resources\Api\TenantResource;
use App\Interfaces\TenantRepositoryInterface;
use App\Services\PrinterService;
use Illuminate\Http\Request;

class TenantController extends BaseApiController
{
    public function __construct(
        protected TenantRepositoryInterface $tenantRepository
    ) {}

    public function show(Request $request)
    {
        $tenant = $request->user()->tenant;

        return $this->successResponse(new TenantResource($tenant));
    }

    public function update(UpdateTenantRequest $request)
    {
        $tenant = $request->user()->tenant;
        
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo');
        }

        $tenant = $this->tenantRepository->updateProfile($tenant, $data);

        return $this->successResponse(new TenantResource($tenant), 'Profil bisnis berhasil diperbarui');
    }

    public function showPrinterSettings(Request $request)
    {
        $tenant = $request->user()->tenant;

        return $this->successResponse([
            'use_default' => (bool) ($tenant->printer_use_default ?? true),
            'connection_type' => $tenant->printer_connection_type ?: config('printer.connection_type'),
            'address' => $tenant->printer_address ?: config('printer.address'),
            'port' => $tenant->printer_port ?: config('printer.port'),
            // Kitchen printer
            'kitchen_connection_type' => $tenant->kitchen_printer_connection_type,
            'kitchen_address' => $tenant->kitchen_printer_address,
            'kitchen_port' => $tenant->kitchen_printer_port,
            'defaults' => [
                'connection_type' => config('printer.connection_type'),
                'address' => config('printer.address'),
                'port' => config('printer.port'),
            ],
        ]);
    }

    public function updatePrinterSettings(UpdatePrinterSettingsRequest $request)
    {
        $tenant = $request->user()->tenant;
        $tenant = $this->tenantRepository->updatePrinterSettings($tenant, $request->validated());

        return $this->successResponse([
            'use_default' => (bool) $tenant->printer_use_default,
            'connection_type' => $tenant->printer_connection_type ?: config('printer.connection_type'),
            'address' => $tenant->printer_address ?: config('printer.address'),
            'port' => $tenant->printer_port ?: config('printer.port'),
            'defaults' => [
                'connection_type' => config('printer.connection_type'),
                'address' => config('printer.address'),
                'port' => config('printer.port'),
            ],
        ], 'Pengaturan printer berhasil diperbarui.');
    }

    public function updateKitchenPrinterSettings(UpdateKitchenPrinterSettingsRequest $request)
    {
        $tenant = $request->user()->tenant;
        $this->tenantRepository->updateKitchenPrinterSettings($tenant, $request->validated());

        return $this->successResponse(null, 'Pengaturan printer dapur berhasil diperbarui.');
    }

    public function testPrinter(Request $request, PrinterService $printerService)
    {
        $tenant = $request->user()->tenant;
        $type = $request->input('type', 'cashier');

        if ($type === 'kitchen') {
            $printed = $printerService->printKitchenTestPage($tenant, $request->user()->name);
        } else {
            $printed = $printerService->printTestPage($tenant, $request->user()->name);
        }

        if (! $printed) {
            return $this->errorResponse(
                $printerService->getLastError() ?: 'Test print gagal. Periksa nama printer, tipe koneksi, atau konektivitas printer.',
                422
            );
        }

        return $this->successResponse(['sent' => true], 'Test print berhasil dikirim ke printer.');
    }

    public function scanReadyPrinters(PrinterService $printerService)
    {
        $printers = $printerService->scanWindowsReadyPrinters();

        return $this->successResponse($printers);
    }
}
