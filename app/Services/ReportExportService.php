<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Collection;

class ReportExportService
{
    /**
     * Generate Excel for Financial Recap.
     */
    public function exportFinancialRecapExcel(array $data, string $tenantName, string $startDate, string $endDate)
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapitulasi Keuangan');

        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI KEUANGAN - ' . strtoupper($tenantName));
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

        return new Xlsx($spreadsheet);
    }

    /**
     * Generate PDF for Financial Recap.
     */
    public function exportFinancialRecapPdf(array $data)
    {
        return Pdf::loadView('reports.recap_pdf', $data);
    }

    /**
     * Generate Excel for Sales Report.
     */
    public function exportSalesExcel(Collection $orders, string $startDate, string $endDate)
    {
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

        return new Xlsx($spreadsheet);
    }

    /**
     * Generate Excel for Sales Detail.
     */
    public function exportSalesDetailExcel(Collection $items, string $startDate, string $endDate)
    {
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

        return new Xlsx($spreadsheet);
    }

    /**
     * Generate Excel for Shift Sales.
     */
    public function exportShiftSalesExcel(Collection $shifts, Collection $shiftSalesTotals, string $startDate, string $endDate)
    {
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
            ], null, 'A' . $row);
            $row++;
        }

        return new Xlsx($spreadsheet);
    }

    /**
     * Generate Excel for Tax Report.
     */
    public function exportTaxExcel(array $grouped, Collection $allCategories, string $startDate, string $endDate)
    {
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

        return new Xlsx($spreadsheet);
    }

    /**
     * Generate PDF for Tax Report.
     */
    public function exportTaxPdf(array $data)
    {
        return Pdf::loadView('reports.tax_report_pdf', $data)->setPaper('a4', 'landscape');
    }
}
