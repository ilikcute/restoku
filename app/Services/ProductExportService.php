<?php

namespace App\Services;

use App\Models\Product;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Schema;

class ProductExportService
{
    public function export(int $tenantId)
    {
        $products = Product::with(['category', 'unit'])
            ->where('tenant_id', $tenantId)
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products Data');

        // Define columns to exclude
        $exclude = ['tenant_id', 'deleted_at', 'image'];
        
        // Get all columns from the table
        $columns = Schema::getColumnListing('products');
        $exportColumns = array_diff($columns, $exclude);

        // Set Headers
        $colIndex = 1;
        foreach ($exportColumns as $col) {
            $coord = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($coord . '1', strtoupper(str_replace('_', ' ', $col)));
            $colIndex++;
        }

        // Add Data
        $rowIndex = 2;
        foreach ($products as $product) {
            $colIndex = 1;
            foreach ($exportColumns as $col) {
                $coord = Coordinate::stringFromColumnIndex($colIndex);
                $value = $product->$col;
                
                // Handle special columns (like relationships)
                if ($col === 'category_id') $value = $product->category?->name;
                if ($col === 'unit_id') $value = $product->unit?->name;
                
                $sheet->setCellValue($coord . $rowIndex, $value);
                $colIndex++;
            }
            $rowIndex++;
        }

        // Style header
        $lastCol = Coordinate::stringFromColumnIndex(count($exportColumns));
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);

        return new Xlsx($spreadsheet);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Produk');

        // Define columns to exclude
        $exclude = ['id', 'tenant_id', 'deleted_at', 'image', 'created_at', 'updated_at'];
        
        // Get all columns from the table
        $columns = Schema::getColumnListing('products');
        $exportColumns = array_diff($columns, $exclude);

        // Set Headers
        $colIndex = 1;
        foreach ($exportColumns as $col) {
            $coord = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($coord . '1', strtoupper(str_replace('_', ' ', $col)));
            $colIndex++;
        }

        // Add Sample Data Row
        $colIndex = 1;
        foreach ($exportColumns as $col) {
            $coord = Coordinate::stringFromColumnIndex($colIndex);
            $value = '';
            
            // Sample values for main columns
            if ($col === 'code') $value = 'PRD-001';
            if ($col === 'name') $value = 'Contoh Produk';
            if ($col === 'category_id') $value = 'Makanan';
            if ($col === 'unit_id') $value = 'Pcs';
            if ($col === 'price') $value = '15000';
            if ($col === 'cost_price') $value = '10000';
            if ($col === 'is_active') $value = '1';
            
            $sheet->setCellValue($coord . '2', $value);
            $colIndex++;
        }

        // Style header
        $lastCol = Coordinate::stringFromColumnIndex(count($exportColumns));
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        
        return \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    }
}
