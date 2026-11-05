<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shift;
use App\Models\TempOrder;
use App\Models\TempOrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelImportService
{
    /**
     * Import raw excel file into temporary staging table.
     */
    public function importToTemporaryTable(string $filePath, User $user): int
    {
        $tenantId = $user->tenant_id;

        // 1. Verifikasi harus ada shift yang berstatus 'open'
        $activeShift = Shift::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (! $activeShift) {
            throw new \Exception('Tidak ada shift kasir yang aktif (status open). Harap buka shift terlebih dahulu sebelum mengimpor transaksi.');
        }

        // 2. Verifikasi tanggal shift aktif harus sama dengan tanggal hari ini
        $today = Carbon::today()->format('Y-m-d');
        $shiftDate = $activeShift->start_time ? $activeShift->start_time->format('Y-m-d') : $today;

        if ($shiftDate !== $today) {
            throw new \Exception("Shift aktif saat ini ($shiftDate) tidak sesuai dengan tanggal hari ini ($today). Harap tutup shift lama dan buka shift baru untuk hari ini.");
        }

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
            $parsedRowDate = $this->parseDate($dateStr);

            // 3. Jagaan tanggal: hanya transaksi yang sesuai dengan tanggal shift aktif yang diimpor
            // Transaksi dengan tanggal lain otomatis dilewati / diabaikan
            if (! empty($parsedRowDate) && $parsedRowDate !== $shiftDate) {
                continue;
            }

            if (empty($parsedRowDate) && ! isset($ordersData[$billNumber])) {
                continue;
            }

            $room = trim((string) $sheet->getCell('L'.$row)->getFormattedValue());

            if (empty($room) || $room === '-' || stripos($room, 'Bar') !== false) {
                $room = '1';
            }

            if (! isset($ordersData[$billNumber])) {
                $ordersData[$billNumber] = [
                    'order_number' => str_replace("'", '', $billNumber),
                    'date' => $shiftDate,
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
        }

        // Filter out orders that have no items (empty/0 rows)
        $ordersData = array_filter($ordersData, function ($order) {
            return ! empty($order['items']);
        });

        if (empty($ordersData)) {
            throw new \Exception("Tidak ditemukan transaksi untuk tanggal shift aktif ($shiftDate) pada file Excel. Transaksi dengan tanggal lain tidak diimpor.");
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
                if (empty($data['items'])) {
                    continue;
                }

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

                // Abaikan dan jangan simpan transaksi yang bernilai 0 atau tidak ada item
                if ($grandTotal <= 0 || empty($orderItemsToInsert)) {
                    continue;
                }

                $tempOrder = TempOrder::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id,
                    'order_number' => $data['order_number'],
                    'table_number' => (empty($data['table_number']) || $data['table_number'] === '-' || stripos($data['table_number'], 'Bar') !== false) ? '1' : $data['table_number'],
                    'subtotal' => $orderTotal,
                    'tax_amount' => $orderTax,
                    'service_charge' => $orderService,
                    'total_amount' => $grandTotal,
                    'date' => $shiftDate,
                ]);

                foreach ($orderItemsToInsert as $oItem) {
                    $oItem['temp_order_id'] = $tempOrder->id;
                    TempOrderItem::create($oItem);
                }

                $importedCount++;
            }

            // Verifikasi ulang: pastikan shift masih berstatus open saat proses import selesai
            $reverifiedShift = Shift::where('tenant_id', $tenantId)
                ->where('id', $activeShift->id)
                ->where('status', 'open')
                ->first();

            if (! $reverifiedShift) {
                throw new \Exception('Verifikasi gagal: Shift kasir tidak aktif saat proses import disimpan.');
            }

            // Verifikasi ulang: pastikan semua record temp_orders untuk tenant ini memiliki date = shiftDate
            $mismatchedDateCount = TempOrder::where('tenant_id', $tenantId)
                ->where('date', '!=', $shiftDate)
                ->count();

            if ($mismatchedDateCount > 0) {
                throw new \Exception('Verifikasi gagal: Terdapat data transaksi yang tanggalnya tidak sesuai dengan shift aktif.');
            }

            DB::commit();

            return $importedCount;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel Staging Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get summary of current staging data for the user's tenant.
     *
     * @return array<string, mixed>
     */
    public function getImportSummary(User $user): array
    {
        $tenantId = $user->tenant_id;

        // Otomatis hapus data staging yang record-nya 0 atau kosong (tanpa item)
        TempOrder::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('total_amount', '<=', 0)
                    ->orWhereNull('total_amount')
                    ->orWhereDoesntHave('items');
            })
            ->delete();

        $activeShift = Shift::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->with('user:id,name')
            ->latest()
            ->first();

        $shiftDate = $activeShift?->start_time ? $activeShift->start_time->format('Y-m-d') : null;
        $activeShiftData = null;
        if ($activeShift) {
            $activeShiftData = [
                'id' => $activeShift->id,
                'date' => $shiftDate,
                'is_today' => $shiftDate === Carbon::today()->format('Y-m-d'),
                'user_name' => $activeShift->user?->name,
                'status' => $activeShift->status,
            ];
        }

        $totalOrders = TempOrder::where('tenant_id', $tenantId)->count();
        $totalAmount = TempOrder::where('tenant_id', $tenantId)->sum('total_amount') ?: 0;

        // Distinct dates with their count & total amount
        $datesSummary = TempOrder::where('tenant_id', $tenantId)
            ->where('total_amount', '>', 0)
            ->whereNotNull('date')
            ->selectRaw('date, count(*) as count, sum(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) use ($shiftDate) {
                $item->is_shift_date = ($shiftDate !== null && $item->date === $shiftDate);

                return $item;
            });

        // All temp orders with already_imported and is_shift_date checks
        $orders = TempOrder::where('tenant_id', $tenantId)
            ->where('total_amount', '>', 0)
            ->orderBy('order_number', 'asc')
            ->get()
            ->map(function ($order) use ($tenantId, $shiftDate) {
                $order->already_imported = Order::where('tenant_id', $tenantId)
                    ->where('order_number', $order->order_number)
                    ->exists();

                $orderDate = $order->date ? Carbon::parse($order->date)->format('Y-m-d') : null;
                $order->is_shift_date = ($shiftDate !== null && $orderDate === $shiftDate);

                return $order;
            });

        $mismatchedCount = 0;
        if ($shiftDate) {
            $mismatchedCount = TempOrder::where('tenant_id', $tenantId)
                ->where('date', '!=', $shiftDate)
                ->count();
        }

        return [
            'total_orders' => $totalOrders,
            'total_amount' => $totalAmount,
            'dates' => $datesSummary,
            'orders' => $orders,
            'active_shift' => $activeShiftData,
            'mismatched_dates_count' => $mismatchedCount,
        ];
    }

    /**
     * Commit staging records to final orders and order_items tables.
     *
     * @param  array<string>  $dates
     * @param  array<string>  $orderNumbers
     */
    public function commitImport(User $user, int $shiftId, array $dates = [], array $orderNumbers = []): int
    {
        $tenantId = $user->tenant_id;

        // Verifikasi shift yang dituju
        $shift = Shift::where('tenant_id', $tenantId)->find($shiftId);
        if (! $shift || $shift->status !== 'open') {
            throw new \Exception('Shift tidak ditemukan atau statusnya tidak aktif (open). Harap buka shift terlebih dahulu.');
        }

        $shiftDate = $shift->start_time ? $shift->start_time->format('Y-m-d') : null;
        if (! $shiftDate) {
            throw new \Exception('Tanggal shift kasir tidak valid.');
        }

        // Bersihkan data temporary yang kosong atau bernilai 0
        TempOrder::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('total_amount', '<=', 0)
                    ->orWhereNull('total_amount')
                    ->orWhereDoesntHave('items');
            })
            ->delete();

        $query = TempOrder::where('tenant_id', $tenantId)
            ->where('total_amount', '>', 0)
            ->has('items')
            ->with('items');

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

        // JEGATAN KRITIS: Tolak jika ada transaksi staging yang tanggalnya tidak sama dengan shift aktif!
        foreach ($tempOrders as $tempOrder) {
            $orderDate = $tempOrder->date ? Carbon::parse($tempOrder->date)->format('Y-m-d') : null;
            if ($orderDate !== $shiftDate) {
                throw new \Exception("Transaksi (#{$tempOrder->order_number}) dengan tanggal ($orderDate) tidak sama dengan tanggal shift aktif ($shiftDate). Proses impor dibatalkan untuk mencegah selisih tanggal laporan kasir.");
            }
        }

        $importedCount = 0;
        $totalBatchAmount = 0.0;

        DB::beginTransaction();
        try {
            foreach ($tempOrders as $tempOrder) {
                // Lewati jika total 0 atau tanpa item
                if ($tempOrder->total_amount <= 0 || $tempOrder->items->isEmpty()) {
                    $tempOrder->delete();

                    continue;
                }
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
                    'customer_name' => '-',
                    'table_number' => $tempOrder->table_number,
                    'subtotal' => $tempOrder->subtotal,
                    'tax_amount' => $tempOrder->tax_amount,
                    'service_charge' => $tempOrder->service_charge,
                    'total_amount' => $tempOrder->total_amount,
                    'paid_amount' => $tempOrder->total_amount,
                    'change_amount' => 0,
                    'payment_method' => 'cash',
                    'status' => 'completed',
                    'created_at' => Carbon::parse($shiftDate.' '.now()->format('H:i:s')),
                ]);

                foreach ($tempOrder->items as $tempItem) {
                    $product = Product::where('tenant_id', $tenantId)
                        ->where('name', $tempItem->product_name)
                        ->first();

                    if (! $product) {
                        $product = Product::create([
                            'tenant_id' => $tenantId,
                            'name' => $tempItem->product_name,
                            'price' => $tempItem->price,
                            'cost_price' => 0,
                            'is_active' => true,
                        ]);
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
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
                $totalBatchAmount += (float) $order->total_amount;
            }

            if ($totalBatchAmount > 0) {
                $shift->increment('total_sales', $totalBatchAmount);
                $shift->increment('cash_sales', $totalBatchAmount);
            }

            DB::commit();

            return $importedCount;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Commit Import Error: '.$e->getMessage());
            throw $e;
        }
    }

    private function parseDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }
        try {
            // Check if it is a serial number from excel
            if (is_numeric($dateStr)) {
                return Date::excelToDateTimeObject((int) $dateStr)->format('Y-m-d');
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

    public function clearStaging(User $user): int
    {
        return TempOrder::where('tenant_id', $user->tenant_id)->delete();
    }

    public function deleteEmptyOrders(User $user): int
    {
        return TempOrder::where('tenant_id', $user->tenant_id)
            ->where(function ($q) {
                $q->where('total_amount', '<=', 0)
                    ->orWhereNull('total_amount')
                    ->orWhereDoesntHave('items');
            })
            ->delete();
    }

    public function deleteMismatchedOrders(User $user): int
    {
        $tenantId = $user->tenant_id;
        $activeShift = Shift::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (! $activeShift) {
            return 0;
        }

        $shiftDate = $activeShift->start_time ? $activeShift->start_time->format('Y-m-d') : null;
        if (! $shiftDate) {
            return 0;
        }

        return TempOrder::where('tenant_id', $tenantId)
            ->where('date', '!=', $shiftDate)
            ->delete();
    }
}
