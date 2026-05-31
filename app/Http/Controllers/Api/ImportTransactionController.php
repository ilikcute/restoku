<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ImportTransactionRequest;
use App\Models\Shift;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportTransactionController extends BaseApiController
{
    protected ExcelImportService $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }

    public function import(ImportTransactionRequest $request)
    {
        try {
            $file = $request->file('file');
            $user = $request->user();

            $importedCount = $this->importService->importToTemporaryTable($file->getPathname(), $user);
            $summary = $this->importService->getImportSummary($user);

            return $this->successResponse([
                'imported_count' => $importedCount,
                'summary' => $summary,
            ], "Berhasil mengimpor $importedCount transaksi ke penyimpanan sementara.");

        } catch (\Exception $e) {
            Log::error('Import Controller Error: '.$e->getMessage());

            return $this->errorResponse('Gagal mengimpor file: '.$e->getMessage(), 500);
        }
    }

    public function getSummary(Request $request)
    {
        try {
            $user = $request->user();
            $summary = $this->importService->getImportSummary($user);

            return $this->successResponse($summary, 'Berhasil memuat ringkasan import.');
        } catch (\Exception $e) {
            Log::error('Get Summary Error: '.$e->getMessage());

            return $this->errorResponse('Gagal memuat ringkasan: '.$e->getMessage(), 500);
        }
    }

    public function confirmImport(Request $request)
    {
        try {
            $user = $request->user();

            // Get current active shift
            $shift = Shift::where('tenant_id', $user->tenant_id)
                ->where('status', 'open')
                ->latest()
                ->first();

            if (! $shift) {
                return $this->errorResponse('Tidak ada shift kasir yang aktif. Harap buka shift terlebih dahulu untuk melakukan import transaksi.', 400);
            }

            $dates = $request->input('dates', []);
            $orderNumbers = $request->input('order_numbers', []);

            $committedCount = $this->importService->commitImport($user, $shift->id, $dates, $orderNumbers);

            return $this->successResponse([
                'committed_count' => $committedCount,
            ], "Berhasil memproses $committedCount transaksi ke pesanan utama.");

        } catch (\Exception $e) {
            Log::error('Confirm Import Error: '.$e->getMessage());

            return $this->errorResponse('Gagal memindahkan transaksi: '.$e->getMessage(), 500);
        }
    }
}
