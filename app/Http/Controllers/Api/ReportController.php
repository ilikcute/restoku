<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\Transactions\OrderResource;
use App\Http\Resources\Api\Transactions\PurchaseResource;
use App\Interfaces\ReportRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends BaseApiController
{
    protected ReportRepositoryInterface $reportRepository;

    public function __construct(ReportRepositoryInterface $reportRepository)
    {
        $this->reportRepository = $reportRepository;
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

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekapitulasi Keuangan');

            $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI KEUANGAN - '.strtoupper($request->user()->tenant->name));
            $sheet->setCellValue('A2', 'Periode: '.$startDate.' s/d '.$endDate);
            $sheet->mergeCells('A1:B1');
            $sheet->mergeCells('A2:B2');

            $rows = [
                ['PENDAPATAN & PENJUALAN', ''],
                ['Total Penjualan Kotor (Gross)', (float) $data['sales']->gross_sales],
                ['- Total Pajak (PB1/PPN)', (float) -$data['sales']->total_tax],
                ['- Total Service Charge', (float) -$data['sales']->total_service],
                ['- Total Diskon', (float) -$data['sales']->total_discount],
                ['TOTAL PENJUALAN BERSIH (NET)', (float) $data['sales']->net_sales],
                ['', ''],
                ['BEBAN & PROFITABILITAS', ''],
                ['Total HPP (Modal Produk)', (float) -$data['cogs']],
                ['GROSS PROFIT', (float) $data['gross_profit']],
                ['Beban Operasional (Expense)', (float) -$data['expenses']],
                ['Pendapatan Lain-lain', (float) $data['other_income']],
                ['LABA BERSIH (ESTIMASI)', (float) $data['net_profit']],
            ];

            $sheet->fromArray($rows, null, 'A4');
            $sheet->getStyle('A4:B4')->getFont()->setBold(true);
            $sheet->getStyle('A11:B11')->getFont()->setBold(true);
            $sheet->getStyle('A9:B9')->getFont()->setBold(true);
            $sheet->getStyle('A16:B16')->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setWidth(20);

            $numericRows = [5, 6, 7, 8, 9, 12, 13, 14, 15, 16];
            foreach ($numericRows as $row) {
                $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#,##0');
            }

            $writer = new Xlsx($spreadsheet);
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

        $pdf = Pdf::loadView('reports.recap_pdf', $data);

        return $pdf->download('Laporan_Rekapitulasi_'.$startDate.'_'.$endDate.'.pdf');
    }

    public function exportExcelSales(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));

        $orders = $this->reportRepository->getOrdersByStatus($request->user()->tenant_id, $startDate, $endDate, 'completed');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Penjualan');

        $headers = ['No', 'No. Trans', 'Tanggal', 'Karyawan', 'No. Meja', 'Subtotal', 'Service', 'Tax', 'Rounding', 'Total'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($orders as $index => $order) {
            $sheet->fromArray([
                $index + 1,
                $order->order_number,
                $order->created_at->format('d-m-Y H:i'),
                $order->user->name ?? '-',
                $order->table_number ?? '-',
                (float) $order->subtotal,
                (float) $order->service_charge,
                (float) $order->tax_amount,
                (float) $order->rounding,
                (float) $order->total_amount,
            ], null, 'A'.$row);
            $row++;
        }

        $sheet->setCellValue('A'.$row, 'GRAND TOTAL');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $sheet->setCellValue('J'.$row, $orders->sum('total_amount'));
        $sheet->getStyle('A'.$row.':J'.$row)->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
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

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penjualan Detail');

        $headers = ['No', 'No. Trans', 'Tanggal', 'Kategori', 'Nama Menu', 'Harga', 'Qty', 'Total'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($items as $index => $item) {
            $sheet->fromArray([
                $index + 1,
                $item->order->order_number,
                $item->order->created_at->format('d-m-Y H:i'),
                $item->product->category->name ?? '-',
                $item->product_name,
                (float) $item->price,
                (float) $item->quantity,
                (float) $item->subtotal,
            ], null, 'A'.$row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
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

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Per Shift');

        $headers = ['No', 'Kasir', 'Buka', 'Tutup', 'Modal Awal', 'Total Penjualan', 'Total Biaya', 'Kas Akhir'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($shifts as $index => $shift) {
            $salesTotal = $shiftSalesTotals->get($shift->id, 0);

            $sheet->fromArray([
                $index + 1,
                $shift->user->name ?? '-',
                $shift->start_time->format('d-m-Y H:i'),
                $shift->end_time ? $shift->end_time->format('d-m-Y H:i') : 'AKTIF',
                (float) $shift->starting_cash,
                (float) $salesTotal,
                0,
                (float) ($shift->ending_cash ?? 0),
            ], null, 'A'.$row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
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

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pajak');

        $headers = ['Tanggal'];
        foreach ($allCategories as $cat) {
            $headers[] = $cat;
        }
        $headers = array_merge($headers, ['Subtotal', 'Service', 'Tax (PB1)', 'Grand Total']);

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);

        $row = 2;
        foreach ($grouped as $data) {
            $rowData = [$data['date']];
            foreach ($allCategories as $cat) {
                $rowData[] = (float) ($data['categories'][$cat] ?? 0);
            }
            $rowData[] = (float) $data['subtotal'];
            $rowData[] = (float) $data['service'];
            $rowData[] = (float) $data['tax'];
            $rowData[] = (float) $data['grand_total'];

            $sheet->fromArray($rowData, null, 'A'.$row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
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

        $pdf = Pdf::loadView('reports.tax_report_pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Pajak_'.$startDate.'_'.$endDate.'.pdf');
    }
}
