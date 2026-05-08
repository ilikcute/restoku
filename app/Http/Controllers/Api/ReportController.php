<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\Transactions\OrderResource;
use App\Http\Resources\Api\Transactions\PurchaseResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\Shift;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends BaseApiController
{
    /**
     * Helper untuk mengumpulkan data laporan rekapitulasi keuangan.
     */
    private function getReportData(int $tenantId, string $startDate, string $endDate): array
    {
        // 1. Sales Summary — satu query agregat
        $sales = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as gross_sales,
                SUM(subtotal) as net_sales,
                SUM(tax_amount) as total_tax,
                SUM(service_charge) as total_service,
                SUM(discount_amount) as total_discount,
                SUM(total_return) as total_returns
            ')->first();

        // 2. COGS — gunakan join agar lebih efisien dari whereHas
        $cogs = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('orders.status', 'completed')
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at')
            ->sum(DB::raw('order_items.cost_price * (order_items.quantity - order_items.return_quantity)'));

        // 3. Beban operasional
        $expenses = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // 4. Pendapatan lain-lain (manual, bukan dari order)
        $otherIncome = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->whereNull('reference_type')
            ->sum('amount');

        $grossProfit = $sales->net_sales - $cogs;
        $netProfit = $grossProfit - $expenses + $otherIncome;

        return [
            'sales' => $sales,
            'cogs' => (float) $cogs,
            'gross_profit' => (float) $grossProfit,
            'expenses' => (float) $expenses,
            'other_income' => (float) $otherIncome,
            'net_profit' => (float) $netProfit,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Helper untuk menghitung agregasi pajak per hari dari koleksi orders.
     * DRY: digunakan oleh taxReport(), exportExcelTax(), dan exportPdfTax().
     *
     * @param  Collection<int, Order>  $orders
     * @return Collection<int, array<string, mixed>>
     */
    private function aggregateTaxReportData(Collection $orders): Collection
    {
        return $orders->groupBy(fn($order) => $order->created_at->format('Y-m-d'))
            ->map(function ($dayOrders, $date) {
                $categories = [];
                $subtotal = 0.0;
                $tax = 0.0;
                $service = 0.0;

                foreach ($dayOrders as $order) {
                    foreach ($order->items as $item) {
                        $catName = $item->product->category->name ?? 'Lain-lain';
                        $categories[$catName] = ($categories[$catName] ?? 0) + $item->subtotal;
                    }
                    $subtotal += $order->subtotal;
                    $tax += $order->tax_amount;
                    $service += $order->service_charge;
                }

                return [
                    'date' => $date,
                    'day' => Carbon::parse($date)->translatedFormat('l'),
                    'categories' => $categories,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'service' => $service,
                    'grand_total' => $subtotal + $tax + $service,
                ];
            })
            ->values();
    }

    /**
     * Helper untuk mengambil orders DPKAD dengan eager load yang benar.
     *
     * @return Collection<int, Order>
     */
    private function getDpkadOrders(int $tenantId, string $startDate, string $endDate): Collection
    {
        return Order::where('tenant_id', $tenantId)
            ->where('is_synced_to_dpkad', true)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['items.product.category'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // =========================================================================
    // API Endpoints
    // =========================================================================

    public function summary(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $data = $this->getReportData($request->user()->tenant_id, $startDate, $endDate);

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
        $tenantId = $request->user()->tenant_id;

        $data = Order::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->successResponse($data);
    }

    public function topProducts(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        // JOIN lebih efisien dari whereHas untuk kasus ini
        $products = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('orders.status', 'completed')
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at')
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity - order_items.return_quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return $this->successResponse($products);
    }

    public function transactions(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $orders = Order::with(['user', 'shift'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($orders);
    }

    public function purchases(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $purchases = Purchase::with(['user', 'supplier'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->orderBy('purchase_date', 'desc')
            ->get();

        return $this->successResponse($purchases);
    }

    public function salesReturns(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $returns = Order::with(['user', 'returnUser'])
            ->where('tenant_id', $tenantId)
            ->where('total_return', '>', 0)
            ->whereBetween('return_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('return_date', 'desc')
            ->get();

        return $this->successResponse(OrderResource::collection($returns));
    }

    public function purchaseReturns(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $returns = Purchase::with(['user', 'supplier', 'returnUser'])
            ->where('tenant_id', $tenantId)
            ->where('total_return', '>', 0)
            ->whereBetween('return_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('return_date', 'desc')
            ->get();

        return $this->successResponse(PurchaseResource::collection($returns));
    }

    // =========================================================================
    // Tax Report (DPKAD)
    // =========================================================================

    public function taxReport(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $orders = $this->getDpkadOrders($tenantId, $startDate, $endDate);
        $grouped = $this->aggregateTaxReportData($orders);

        return $this->successResponse($grouped);
    }

    // =========================================================================
    // Exports
    // =========================================================================

    public function exportExcel(Request $request)
    {
        try {
            $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
            $data = $this->getReportData($request->user()->tenant_id, $startDate, $endDate);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekapitulasi Keuangan');

            $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI KEUANGAN - ' . strtoupper($request->user()->tenant->name));
            $sheet->setCellValue('A2', 'Periode: ' . $startDate . ' s/d ' . $endDate);
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
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'Laporan_Rekapitulasi_' . $startDate . '_' . $endDate . '.xlsx';

            return response()->stream(function () use ($writer) {
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Exception $e) {
            Log::error('Excel Export Error: ' . $e->getMessage());

            return response()->json(['message' => 'Gagal membuat file Excel: ' . $e->getMessage()], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $data = $this->getReportData($request->user()->tenant_id, $startDate, $endDate);
        $data['tenant'] = $request->user()->tenant;

        $pdf = Pdf::loadView('reports.recap_pdf', $data);

        return $pdf->download('Laporan_Rekapitulasi_' . $startDate . '_' . $endDate . '.pdf');
    }

    public function exportExcelSales(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $orders = Order::with(['user'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'asc')
            ->get();

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
            ], null, 'A' . $row);
            $row++;
        }

        $sheet->setCellValue('A' . $row, 'GRAND TOTAL');
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->setCellValue('J' . $row, $orders->sum('total_amount'));
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Penjualan_' . $startDate . '_' . $endDate . '.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportExcelSalesDetail(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        // JOIN lebih efisien dari whereHas untuk eksport data besar
        $items = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('orders.status', 'completed')
            ->whereNull('orders.deleted_at')
            ->select('order_items.*')
            ->with(['order.user', 'product.category'])
            ->get();

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
            ], null, 'A' . $row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Penjualan_Detail_' . $startDate . '_' . $endDate . '.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportExcelSalesShift(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        // FIX N+1: Pra-hitung total penjualan per shift dengan satu query agregat,
        // bukan satu query per shift di dalam loop.
        $shiftSalesTotals = DB::table('orders')
            ->whereNotNull('shift_id')
            ->where('status', 'completed')
            ->whereNull('deleted_at')
            ->select('shift_id', DB::raw('SUM(total_amount) as sales_total'))
            ->groupBy('shift_id')
            ->pluck('sales_total', 'shift_id');

        $shifts = Shift::with(['user'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Per Shift');

        $headers = ['No', 'Kasir', 'Buka', 'Tutup', 'Modal Awal', 'Total Penjualan', 'Total Biaya', 'Kas Akhir'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($shifts as $index => $shift) {
            // Ambil dari hasil pra-kalkulasi — tidak ada query tambahan di sini
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
            ], null, 'A' . $row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Per_Shift_' . $startDate . '_' . $endDate . '.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportExcelTax(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $orders = $this->getDpkadOrders($tenantId, $startDate, $endDate);
        $grouped = $this->aggregateTaxReportData($orders);
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
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

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

            $sheet->fromArray($rowData, null, 'A' . $row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Pajak_' . $startDate . '_' . $endDate . '.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportPdfTax(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));
        $tenantId = $request->user()->tenant_id;

        $orders = $this->getDpkadOrders($tenantId, $startDate, $endDate);
        $grouped = $this->aggregateTaxReportData($orders);
        $allCategories = $orders->pluck('items.*.product.category.name')->flatten()->unique()->filter()->values()->sort();

        $data = [
            'tenant' => $request->user()->tenant,
            'reportData' => $grouped,
            'allCategories' => $allCategories,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $pdf = Pdf::loadView('reports.tax_report_pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Pajak_' . $startDate . '_' . $endDate . '.pdf');
    }
}
