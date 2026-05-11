<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\Transactions\OrderResource;
use App\Http\Resources\Api\Transactions\PurchaseResource;
use App\Interfaces\ReportRepositoryInterface;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends BaseApiController
{
    protected ReportRepositoryInterface $reportRepository;
    protected ReportExportService $exportService;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        ReportExportService $exportService
    ) {
        $this->reportRepository = $reportRepository;
        $this->exportService = $exportService;
    }

    public function summary(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $data = $this->reportRepository->getReportData($request->user()->tenant_id, $startDate, $endDate);

        return $this->successResponse([
            'period' => ['start' => $startDate, 'end' => $endDate],
            'sales' => [
                'count' => $data['sales']->total_orders,
                'gross' => (float) $data['sales']->gross_sales,
                'net' => (float) $data['sales']->net_sales,
                'tax' => (float) $data['sales']->total_tax,
                'service' => (float) $data['sales']->total_service,
                'discount' => (float) $data['sales']->total_discount,
                'returns' => (float) $data['sales']->total_returns,
            ],
            'financials' => [
                'cogs' => $data['cogs'],
                'gross_profit' => $data['gross_profit'],
                'expenses' => $data['expenses'],
                'other_income' => $data['other_income'],
                'net_profit' => $data['net_profit'],
            ],
        ]);
    }

    public function dailyChart(Request $request)
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $data = $this->reportRepository->getDailyChart($request->user()->tenant_id, $startDate, $endDate);

        return $this->successResponse($data);
    }

    public function topProducts(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $products = $this->reportRepository->getTopProducts($request->user()->tenant_id, $startDate, $endDate);

        return $this->successResponse($products);
    }

    public function transactions(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $orders = $this->reportRepository->getTransactions($request->user()->tenant_id, $startDate, $endDate);

        return $this->successResponse($orders);
    }

    public function purchases(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $purchases = $this->reportRepository->getPurchases($request->user()->tenant_id, $startDate, $endDate);

        return $this->successResponse($purchases);
    }

    public function salesReturns(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $returns = $this->reportRepository->getSalesReturns($request->user()->tenant_id, $startDate, $endDate);

        return $this->successResponse(OrderResource::collection($returns));
    }

    public function purchaseReturns(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $returns = $this->reportRepository->getPurchaseReturns($request->user()->tenant_id, $startDate, $endDate);

        return $this->successResponse(PurchaseResource::collection($returns));
    }

    public function taxReport(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));

        $orders = $this->reportRepository->getDpkadOrders($request->user()->tenant_id, $startDate, $endDate);
        $grouped = $this->reportRepository->aggregateTaxReportData($orders);

        return $this->successResponse($grouped);
    }

    public function exportExcel(Request $request)
    {
        try {
            $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
            $data = $this->reportRepository->getReportData($request->user()->tenant_id, $startDate, $endDate);

            $writer = $this->exportService->exportFinancialRecapExcel(
                $data,
                $request->user()->tenant->name,
                $startDate,
                $endDate
            );

            $fileName = 'Laporan_Rekapitulasi_'.$startDate.'_'.$endDate.'.xlsx';

            return response()->stream(function () use ($writer) {
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Exception $e) {
            Log::error('Excel Export Error: '.$e->getMessage());

            return response()->json(['message' => 'Gagal membuat file Excel: '.$e->getMessage()], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $data = $this->reportRepository->getReportData($request->user()->tenant_id, $startDate, $endDate);
        $data['tenant'] = $request->user()->tenant;

        $pdf = $this->exportService->exportFinancialRecapPdf($data);

        return $pdf->download('Laporan_Rekapitulasi_'.$startDate.'_'.$endDate.'.pdf');
    }

    public function exportExcelSales(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));

        $orders = $this->reportRepository->getOrdersByStatus($request->user()->tenant_id, $startDate, $endDate, 'completed');

        $writer = $this->exportService->exportSalesExcel($orders, $startDate, $endDate);
        $fileName = 'Laporan_Penjualan_'.$startDate.'_'.$endDate.'.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportExcelSalesDetail(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));

        $items = $this->reportRepository->getSalesDetailItems($request->user()->tenant_id, $startDate, $endDate);

        $writer = $this->exportService->exportSalesDetailExcel($items, $startDate, $endDate);
        $fileName = 'Laporan_Penjualan_Detail_'.$startDate.'_'.$endDate.'.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportExcelSalesShift(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $shiftSalesTotals = $this->reportRepository->getShiftSalesTotals();
        $shifts = $this->reportRepository->getShifts($tenantId, $startDate, $endDate);

        $writer = $this->exportService->exportShiftSalesExcel($shifts, $shiftSalesTotals, $startDate, $endDate);
        $fileName = 'Laporan_Per_Shift_'.$startDate.'_'.$endDate.'.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportExcelTax(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $orders = $this->reportRepository->getDpkadOrders($tenantId, $startDate, $endDate);
        $grouped = $this->reportRepository->aggregateTaxReportData($orders);
        $allCategories = $orders->pluck('items.*.product.category.name')->flatten()->unique()->filter()->values()->sort();

        $writer = $this->exportService->exportTaxExcel($grouped, $allCategories, $startDate, $endDate);
        $fileName = 'Laporan_Pajak_'.$startDate.'_'.$endDate.'.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportPdfTax(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $orders = $this->reportRepository->getDpkadOrders($tenantId, $startDate, $endDate);
        $grouped = $this->reportRepository->aggregateTaxReportData($orders);
        $allCategories = $orders->pluck('items.*.product.category.name')->flatten()->unique()->filter()->values()->sort();

        $data = [
            'tenant' => $request->user()->tenant,
            'reportData' => $grouped,
            'allCategories' => $allCategories,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $pdf = $this->exportService->exportTaxPdf($data);

        return $pdf->download('Laporan_Pajak_'.$startDate.'_'.$endDate.'.pdf');
    }
}
