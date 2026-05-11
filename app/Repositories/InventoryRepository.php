<?php

namespace App\Repositories;

use App\Interfaces\InventoryRepositoryInterface;
use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class InventoryRepository implements InventoryRepositoryInterface
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function getStockLevels(int $tenantId, ?int $categoryId, ?string $search)
    {
        $query = Product::where('tenant_id', $tenantId)
            ->where('stock_type', 'trackable')
            ->with(['category', 'unit', 'stock']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhere('barcode', 'like', '%'.$search.'%');
            });
        }

        return $query->orderBy('name')->get();
    }

    public function getMovements(int $tenantId, string $startDate, string $endDate, ?int $categoryId, ?string $search)
    {
        $query = Product::where('tenant_id', $tenantId)
            ->where('stock_type', 'trackable')
            ->with(['unit']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhere('barcode', 'like', '%'.$search.'%');
            });
        }

        $products = $query->orderBy('name')->get();

        return $products->map(function ($product) use ($tenantId, $startDate, $endDate) {
            // 1. Initial Balance
            $initialMovement = StockMovement::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->where('created_at', '<', $startDate.' 00:00:00')
                ->latest()
                ->first();

            $initialBalance = $initialMovement ? $initialMovement->new_stock : 0;

            // 2. Period Movements
            $movements = StockMovement::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->get();

            $summary = [
                'purchase' => 0,
                'purchase_return' => 0,
                'sale' => 0,
                'sale_return' => 0,
                'adjustment_in' => 0,
                'adjustment_out' => 0,
            ];

            foreach ($movements as $m) {
                if ($m->type === 'initial') {
                    // Initial stock is part of initial_balance, not adjustment
                } elseif ($m->reference_type === Purchase::class) {
                    if ($m->type === 'in') {
                        $summary['purchase'] += $m->quantity;
                    }
                    if ($m->type === 'out') {
                        $summary['purchase_return'] += $m->quantity;
                    }
                } elseif ($m->reference_type === Order::class) {
                    if ($m->type === 'out') {
                        $summary['sale'] += $m->quantity;
                    }
                    if ($m->type === 'in') {
                        $summary['sale_return'] += $m->quantity;
                    }
                } elseif ($m->type === 'adjustment_in' || ($m->type === 'in' && ! $m->reference_type)) {
                    $summary['adjustment_in'] += $m->quantity;
                } elseif ($m->type === 'adjustment_out' || ($m->type === 'out' && ! $m->reference_type)) {
                    $summary['adjustment_out'] += $m->quantity;
                }
            }

            $totalIn = $summary['purchase'] + $summary['sale_return'] + $summary['adjustment_in'];
            $totalOut = $summary['sale'] + $summary['purchase_return'] + $summary['adjustment_out'];
            $finalBalance = $initialBalance + $totalIn - $totalOut;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'code' => $product->code,
                'unit' => $product->unit ? $product->unit->name : '',
                'initial_balance' => (float) $initialBalance,
                'purchase' => (float) $summary['purchase'],
                'purchase_return' => (float) $summary['purchase_return'],
                'sale' => (float) $summary['sale'],
                'sale_return' => (float) $summary['sale_return'],
                'adjustment' => (float) ($summary['adjustment_in'] - $summary['adjustment_out']),
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'final_balance' => (float) $finalBalance,
            ];
        });
    }

    public function getMovementDetail(int $tenantId, int $productId, string $startDate, string $endDate)
    {
        return StockMovement::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->with(['user'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function processAdjustment(int $tenantId, int $userId, array $validated)
    {
        return DB::transaction(function () use ($validated, $tenantId, $userId) {
            // 1. Find or Create Adjustment
            if (isset($validated['id'])) {
                $adjustment = StockAdjustment::where('tenant_id', $tenantId)->findOrFail($validated['id']);

                if ($adjustment->status === 'A') {
                    throw new \Exception('Cannot update finalized adjustment');
                }

                $adjustment->update([
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? $adjustment->notes,
                ]);

                // Refresh items
                $adjustment->items()->delete();
            } else {
                $adjustment = StockAdjustment::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'adjustment_number' => 'ADJ-'.strtoupper(uniqid()),
                    'adjustment_date' => now(),
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'],
                ]);
            }

            $totalLossAmount = 0;

            // 2. Process Items
            foreach ($validated['items'] as $itemData) {
                $product = Product::where('tenant_id', $tenantId)->findOrFail($itemData['product_id']);
                $stock = Stock::where('tenant_id', $tenantId)->where('product_id', $product->id)->first();

                $recordedStock = $stock ? $stock->current_stock : 0;
                $difference = $itemData['actual_stock'] - $recordedStock;

                // Calculate loss if difference is negative
                $lossValue = 0;
                if ($difference < 0) {
                    $lossValue = abs($difference) * ($product->cost_price ?? 0);
                    $totalLossAmount += $lossValue;
                }

                $adjustment->items()->create([
                    'product_id' => $product->id,
                    'cost_price' => $product->cost_price ?? 0,
                    'recorded_stock' => $recordedStock,
                    'actual_stock' => $itemData['actual_stock'],
                    'adjustment_quantity' => $difference,
                    'reason' => $itemData['reason'] ?? null,
                ]);

                // 3. IF FINALIZED (Status 'A'), Update Real Stock
                if ($validated['status'] === 'A') {
                    if ($difference !== 0) {
                        $type = $difference > 0 ? 'adjustment_in' : 'adjustment_out';
                        $this->inventoryService->updateStock(
                            $tenantId,
                            $product->id,
                            $difference,
                            $type,
                            $userId,
                            get_class($adjustment),
                            $adjustment->id,
                            'Stock Opname: '.$adjustment->adjustment_number
                        );
                    }
                }
            }

            // Update total loss in header
            $adjustment->update(['total_loss_amount' => $totalLossAmount]);

            // 4. Record Financial Transaction if there's a loss and status is 'A'
            if ($validated['status'] === 'A' && $totalLossAmount > 0) {
                $this->recordLossTransaction($tenantId, $userId, $totalLossAmount, $adjustment);
            }

            return $adjustment->load(['user', 'items.product']);
        });
    }

    private function recordLossTransaction(int $tenantId, int $userId, float $amount, StockAdjustment $adjustment)
    {
        $category = ExpenseCategory::firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => 'Inventory Loss'],
            ['code' => 'EXP-LOSS', 'is_active' => true]
        );

        $account = Account::where('tenant_id', $tenantId)->where('is_active', true)->first();

        if ($account) {
            Transaction::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'account_id' => $account->id,
                'transaction_number' => 'TRX-'.strtoupper(uniqid()),
                'type' => 'expense',
                'amount' => $amount,
                'transaction_date' => now(),
                'description' => 'Inventory Loss from Opname #'.$adjustment->adjustment_number,
                'reference_type' => get_class($adjustment),
                'reference_id' => $adjustment->id,
            ]);
        }
    }

    public function getAdjustmentHistory(int $tenantId, int $perPage = 20)
    {
        return StockAdjustment::where('tenant_id', $tenantId)
            ->with(['user', 'items.product'])
            ->latest()
            ->paginate($perPage);
    }
}
