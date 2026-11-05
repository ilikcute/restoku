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

            $shiftDate = $summary['active_shift']['date'] ?? date('Y-m-d');

            return $this->successResponse([
                'imported_count' => $importedCount,
                'summary' => $summary,
            ], "Berhasil mengimpor $importedCount transaksi untuk tanggal shift ($shiftDate). Transaksi tanggal lain otomatis diabaikan.");

        } catch (\Exception $e) {
            Log::error('Import Controller Error: '.$e->getMessage());

            return $this->errorResponse($e->getMessage(), 400);
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

            return $this->errorResponse('Gagal memindahkan transaksi: '.$e->getMessage(), 400);
        }
    }

    public function clearStaging(Request $request)
    {
        try {
            $user = $request->user();
            $deleted = $this->importService->clearStaging($user);

            return $this->successResponse(['deleted_count' => $deleted], 'Staging transaksi berhasil dibersihkan.');
        } catch (\Exception $e) {
            Log::error('Clear Staging Error: '.$e->getMessage());

            return $this->errorResponse('Gagal membersihkan staging: '.$e->getMessage(), 500);
        }
    }

    public function deleteEmptyRecords(Request $request)
    {
        try {
            $user = $request->user();
            $deleted = $this->importService->deleteEmptyOrders($user);
            $summary = $this->importService->getImportSummary($user);

            return $this->successResponse([
                'deleted_count' => $deleted,
                'summary' => $summary,
            ], "Berhasil menghapus $deleted data transaksi kosong/bernilai 0.");
        } catch (\Exception $e) {
            Log::error('Delete Empty Records Error: '.$e->getMessage());

            return $this->errorResponse('Gagal menghapus data kosong: '.$e->getMessage(), 500);
        }
    }

    public function deleteMismatchedRecords(Request $request)
    {
        try {
            $user = $request->user();
            $deleted = $this->importService->deleteMismatchedOrders($user);
            $summary = $this->importService->getImportSummary($user);

            return $this->successResponse([
                'deleted_count' => $deleted,
                'summary' => $summary,
            ], "Berhasil membersihkan $deleted data transaksi dengan tanggal yang tidak sesuai shift aktif.");
        } catch (\Exception $e) {
            Log::error('Delete Mismatched Records Error: '.$e->getMessage());

            return $this->errorResponse('Gagal membersihkan data tanggal berbeda: '.$e->getMessage(), 500);
        }
    }
}
