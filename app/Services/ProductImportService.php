<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportService
{
    public function import(UploadedFile $file, int $tenantId): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Assume first row is header
        $header = array_shift($rows);
        
        // Basic mapping index (case insensitive)
        $mapping = $this->getMapping($header);

        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty(array_filter($row))) continue;

            try {
                DB::transaction(function () use ($row, $mapping, $tenantId, &$imported) {
                    $name = $this->getValue($row, $mapping, 'name');
                    $codeRaw = $this->getValue($row, $mapping, 'code');
                    $code = $codeRaw ? (string) intval($codeRaw) : null;
                    if ($code === '0' && $codeRaw !== '0') $code = trim((string)$codeRaw);
                    
                    if (!$name) throw new \Exception("Nama produk wajib diisi");

                    // Handle Category
                    $categoryName = trim($this->getValue($row, $mapping, 'category_id') ?: ($this->getValue($row, $mapping, 'category') ?: 'Uncategorized'));
                    $categorySlug = Str::slug($categoryName);
                    $category = Category::firstOrCreate(
                        ['tenant_id' => $tenantId, 'slug' => $categorySlug],
                        ['name' => $categoryName]
                    );

                    // Handle Unit
                    $unitName = trim($this->getValue($row, $mapping, 'unit_id') ?: ($this->getValue($row, $mapping, 'unit') ?: 'Pcs'));
                    $unit = Unit::firstOrCreate(
                        ['tenant_id' => $tenantId, 'name' => $unitName],
                        ['short_name' => strtolower(substr($unitName, 0, 5)), 'is_active' => true]
                    );

                    // Prepare update data dynamically
                    $updateData = [
                        'category_id' => $category->id,
                        'unit_id' => $unit->id,
                        'name' => $name,
                        'slug' => Str::slug($name) . '-' . rand(1000, 9999),
                        'is_active' => true,
                    ];

                    // Map all other columns found in the file
                    $skipColumns = ['id', 'tenant_id', 'code', 'category_id', 'unit_id', 'name', 'slug', 'created_at', 'updated_at', 'deleted_at'];
                    $productColumns = \Illuminate\Support\Facades\Schema::getColumnListing('products');
                    
                    foreach ($productColumns as $col) {
                        if (in_array($col, $skipColumns)) continue;
                        
                        $val = $this->getValue($row, $mapping, $col);
                        if ($val !== null) {
                            // Basic cleaning for numeric/price fields
                            if (str_contains($col, 'price') || str_contains($col, 'cost') || str_contains($col, 'rate') || str_contains($col, 'stock') || str_contains($col, 'discount')) {
                                $val = (float) str_replace(['.', ','], '', $val);
                            }
                            // Boolean handling
                            if ($col === 'is_active') {
                                $val = (bool) $val;
                            }
                            $updateData[$col] = $val;
                        }
                    }

                    Product::updateOrCreate(
                        ['tenant_id' => $tenantId, 'code' => $code],
                        $updateData
                    );

                    $imported++;
                });
            } catch (\Exception $e) {
                $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return [
            'success_count' => $imported,
            'errors' => $errors
        ];
    }

    private function getMapping(array $header): array
    {
        $map = [];
        $productColumns = \Illuminate\Support\Facades\Schema::getColumnListing('products');
        
        // Define aliases for main columns
        $aliases = [
            'code' => ['kode', 'code', 'sku'],
            'name' => ['nama', 'name', 'produk'],
            'category_id' => ['kategori', 'category'],
            'unit_id' => ['satuan', 'unit'],
        ];

        foreach ($header as $index => $label) {
            $label = strtolower(trim($label));
            
            // Check aliases first
            foreach ($aliases as $key => $aliasList) {
                foreach ($aliasList as $alias) {
                    if ($label === strtolower($alias)) {
                        $map[$key] = $index;
                        continue 2;
                    }
                }
            }

            // Then check direct column names
            foreach ($productColumns as $col) {
                if ($label === strtolower(str_replace('_', ' ', $col)) || $label === strtolower($col)) {
                    $map[$col] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    private function getValue(array $row, array $mapping, string $key)
    {
        return isset($mapping[$key]) ? $row[$mapping[$key]] : null;
    }
}
