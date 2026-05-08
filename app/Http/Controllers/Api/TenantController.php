<?php

namespace App\Http\Controllers\Api;

use App\Services\PrinterService;
use App\Http\Resources\Api\TenantResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantController extends BaseApiController
{
    public function show(Request $request)
    {
        $tenant = $request->user()->tenant;

        return $this->successResponse(new TenantResource($tenant));
    }

    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'footer_text' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['name', 'address', 'phone', 'email', 'footer_text']);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($tenant->logo) {
                Storage::disk('public')->delete($tenant->logo);
            }
            $data['logo'] = $request->file('logo')->store('tenants/logos', 'public');
        }

        $tenant->update($data);

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
            'defaults' => [
                'connection_type' => config('printer.connection_type'),
                'address' => config('printer.address'),
                'port' => config('printer.port'),
            ],
        ]);
    }

    public function updatePrinterSettings(Request $request)
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'use_default' => 'required|boolean',
            'connection_type' => 'nullable|string|in:windows,network,file',
            'address' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
        ]);

        if (! $validated['use_default']) {
            // Validasi ulang field custom jika use_default = false
            $customValidated = $request->validate([
                'connection_type' => 'required|string|in:windows,network,file',
                'address' => 'required|string|max:255',
                'port' => 'required_if:connection_type,network|nullable|integer|min:1|max:65535',
            ]);
            $validated = array_merge($validated, $customValidated);
        }

        // Prepare update data
        $updateData = [
            'printer_use_default' => $validated['use_default'],
        ];

        if ($validated['use_default']) {
            // Ketika use_default = true, set custom fields ke null
            $updateData['printer_connection_type'] = null;
            $updateData['printer_address'] = null;
            $updateData['printer_port'] = null;
        } else {
            // Ketika use_default = false, simpan custom values
            $updateData['printer_connection_type'] = $validated['connection_type'];
            $updateData['printer_address'] = $validated['address'];
            $updateData['printer_port'] = ($validated['connection_type'] === 'network') ? $validated['port'] : null;
        }

        $tenant->update($updateData);
        $tenant->refresh();

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

    public function testPrinter(Request $request, PrinterService $printerService)
    {
        $tenant = $request->user()->tenant;
        $printed = $printerService->printTestPage($tenant, $request->user()->name);

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
