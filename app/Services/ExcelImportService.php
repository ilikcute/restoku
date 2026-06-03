<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TempOrder;
use App\Models\TempOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelImportService
{
    public function importToTemporaryTable($filePath, $user)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Input');

        if (! $sheet) {
            throw new \Exception("Sheet 'Input' tidak ditemukan dalam file Excel.");
        }

        $highestRow = $sheet->getHighestDataRow();
        $ordersData = [];

        // Parsing Data
        $emptyConsecutive = 0;
        for ($row = 5; $row <= $highestRow; $row++) {
            $billNumber = trim((string) $sheet->getCell('H'.$row)->getFormattedValue());
            if (empty($billNumber)) {
                $emptyConsecutive++;
                if ($emptyConsecutive > 20) {
                    break; // Stop parsing if 20 consecutive empty rows are found
                }

                continue;
            }
            $emptyConsecutive = 0;

            $dateStr = trim((string) $sheet->getCell('D'.$row)->getFormattedValue()); // e.g. 01/05/2026
            $room = trim((string) $sheet->getCell('L'.$row)->getFormattedValue());

            if (empty($room) || $room === '-' || stripos($room, 'Bar') !== false) {
                $room = '1';
            }

            if (! isset($ordersData[$billNumber])) {
                $ordersData[$billNumber] = [
                    'order_number' => str_replace("'", '', $billNumber),
                    'date' => $this->parseDate($dateStr),
                    'table_number' => $room,
                    'items' => [],
                ];
            }

            // Loop through horizontal item columns starting from index 24 (X) to 78 (BZ) and possibly further
            // Every 5 columns: X (24), AC (29), AH (34), etc.
            for ($colIdx = 24; $colIdx <= 200; $colIdx += 5) {
                $colName = Coordinate::stringFromColumnIndex($colIdx);
                $productName = trim((string) $sheet->getCell($colName.$row)->getFormattedValue());

                // If it's empty or '0' or '-', skip it
                if (empty($productName) || $productName === '0' || $productName === '-') {
                    continue; // Skip this product block
                }

                $colQty = Coordinate::stringFromColumnIndex($colIdx + 1);
                $colPrice = Coordinate::stringFromColumnIndex($colIdx + 2);

                $qtyStr = trim((string) $sheet->getCell($colQty.$row)->getFormattedValue());
                $priceStr = trim((string) $sheet->getCell($colPrice.$row)->getFormattedValue());

                $qty = (float) str_replace(',', '', $qtyStr);
                $price = (float) str_replace(',', '', $priceStr);

                if ($qty > 0) {
                    $ordersData[$billNumber]['items'][] = [
                        'product_name' => $productName,
                        'quantity' => $qty,
                        'price' => $price,
                        'subtotal' => $qty * $price,
                    ];
                }
            }

            // If an order ends up with absolutely no items after looping, we could remove it, but it's fine.
        }

        // Process Database Insertion
        $importedCount = 0;
        $tenantId = $user->tenant_id;

        // Find default category for new products
        $defaultCategory = Category::where('tenant_id', $tenantId)->first();

        DB::beginTransaction();
        try {
            // Delete previous temporary imports for this tenant
            TempOrder::where('tenant_id', $tenantId)->delete();

            foreach ($ordersData as $bill => $data) {
                $orderTotal = 0;
                $orderTax = 0;
                $orderService = 0;

                $orderItemsToInsert = [];

                foreach ($data['items'] as $item) {
                    $product = Product::where('tenant_id', $tenantId)
                        ->where('name', $item['product_name'])
                        ->first();

                    if (! $product) {
                        // Create product
                        $product = Product::create([
                            'tenant_id' => $tenantId,
                            'name' => $item['product_name'],
                            'price' => $item['price'],
                            'cost_price' => 0,
                            'code' => 'AUTO-'.Str::upper(Str::random(6)),
                            'category_id' => $defaultCategory ? $defaultCategory->id : null,
                            'tax_rate' => 0,
                            'service_charge_rate' => 0,
                            'is_active' => true,
                            'stock_type' => 'unlimited',
                        ]);
                    }

                    $taxRate = $product->tax_rate ?? 0;
                    $serviceRate = $product->service_charge_rate ?? 0;

                    $itemSubtotal = $item['subtotal'];
                    $itemTax = ($itemSubtotal * $taxRate) / 100;
                    $itemService = ($itemSubtotal * $serviceRate) / 100;

                    $orderTotal += $itemSubtotal;
                    $orderTax += $itemTax;
                    $orderService += $itemService;

                    $orderItemsToInsert[] = [
                        'product_name' => $product->name,
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $itemSubtotal,
                        'tax_amount' => $itemTax,
                        'service_charge' => $itemService,
                    ];
                }

                $grandTotal = $orderTotal + $orderTax + $orderService;

                $tempOrder = TempOrder::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id,
                    'order_number' => $data['order_number'],
                    'table_number' => (empty($data['table_number']) || $data['table_number'] === '-' || stripos($data['table_number'], 'Bar') !== false) ? '1' : $data['table_number'],
                    'subtotal' => $orderTotal,
                    'tax_amount' => $orderTax,
                    'service_charge' => $orderService,
                    'total_amount' => $grandTotal,
                    'date' => $data['date'],
                ]);

                foreach ($orderItemsToInsert as $oItem) {
                    $oItem['temp_order_id'] = $tempOrder->id;
                    TempOrderItem::create($oItem);
                }

                $importedCount++;
            }

            DB::commit();

            return $importedCount;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel Staging Error: '.$e->getMessage());
            throw $e;
        }
    }

    public function getImportSummary($user)
    {
        $tenantId = $user->tenant_id;

        $totalOrders = TempOrder::where('tenant_id', $tenantId)->count();
        $totalAmount = TempOrder::where('tenant_id', $tenantId)->sum('total_amount');

        // Distinct dates with their count & total amount
        $datesSummary = TempOrder::where('tenant_id', $tenantId)
            ->selectRaw('date, count(*) as count, sum(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // All temp orders with already_imported check
        $orders = TempOrder::where('tenant_id', $tenantId)
            ->orderBy('order_number', 'asc')
            ->get()
            ->map(function ($order) use ($tenantId) {
                $order->already_imported = Order::where('tenant_id', $tenantId)
                    ->where('order_number', $order->order_number)
                    ->exists();

                return $order;
            });

        return [
            'total_orders' => $totalOrders,
            'total_amount' => $totalAmount,
            'dates' => $datesSummary,
            'orders' => $orders,
        ];
    }

    public function commitImport($user, $shiftId, array $dates = [], array $orderNumbers = [])
    {
        $tenantId = $user->tenant_id;

        $query = TempOrder::where('tenant_id', $tenantId)->with('items');

        // Apply filters if provided
        if (! empty($dates) && ! empty($orderNumbers)) {
            $query->where(function ($q) use ($dates, $orderNumbers) {
                $q->whereIn('date', $dates)
                    ->orWhereIn('order_number', $orderNumbers);
            });
        } elseif (! empty($dates)) {
            $query->whereIn('date', $dates);
        } elseif (! empty($orderNumbers)) {
            $query->whereIn('order_number', $orderNumbers);
        }

        $tempOrders = $query->get();

        if ($tempOrders->isEmpty()) {
            return 0;
        }

        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($tempOrders as $tempOrder) {
                // Skip if already imported to main table
                $existingOrder = Order::where('tenant_id', $tenantId)
                    ->where('order_number', $tempOrder->order_number)
                    ->first();

                if ($existingOrder) {
                    $tempOrder->delete();

                    continue;
                }

                $order = Order::create([
                    'tenant_id' => $tenantId,
                    'shift_id' => $shiftId,
                    'user_id' => $user->id,
                    'order_number' => $tempOrder->order_number,
                    'customer_name' => 'Imported',
                    'table_number' => $tempOrder->table_number,
                    'subtotal' => $tempOrder->subtotal,
                    'tax_amount' => $tempOrder->tax_amount,
                    'service_charge' => $tempOrder->service_charge,
                    'total_amount' => $tempOrder->total_amount,
                    'paid_amount' => $tempOrder->total_amount,
                    'change_amount' => 0,
                    'payment_method' => 'cash',
                    'status' => 'completed',
                    'created_at' => $tempOrder->date ? Carbon::parse($tempOrder->date)->format('Y-m-d H:i:s') : now(),
                ]);

                foreach ($tempOrder->items as $tempItem) {
                    $product = Product::where('tenant_id', $tenantId)
                        ->where('name', $tempItem->product_name)
                        ->first();

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product ? $product->id : null,
                        'product_name' => $tempItem->product_name,
                        'price' => $tempItem->price,
                        'cost_price' => $product->cost_price ?? 0,
                        'quantity' => $tempItem->quantity,
                        'subtotal' => $tempItem->subtotal,
                        'tax_amount' => $tempItem->tax_amount,
                        'service_charge' => $tempItem->service_charge,
                    ]);
                }

                $tempOrder->delete();
                $importedCount++;
            }

            DB::commit();

            return $importedCount;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Commit Import Error: '.$e->getMessage());
            throw $e;
        }
    }

    private function parseDate($dateStr)
    {
        if (empty($dateStr)) {
            return null;
        }
        try {
            // Check if it is a serial number from excel
            if (is_numeric($dateStr)) {
                return Date::excelToDateTimeObject($dateStr)->format('Y-m-d');
            }

            // format 01/05/2026 -> 2026-05-01
            try {
                return Carbon::createFromFormat('d/m/Y', $dateStr)->format('Y-m-d');
            } catch (\Exception $e) {
                return Carbon::parse($dateStr)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
