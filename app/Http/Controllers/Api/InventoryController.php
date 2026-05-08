<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Inventory\StoreStockAdjustmentRequest;
use App\Http\Resources\Api\Inventory\ProductStockResource;
use App\Http\Resources\Api\Inventory\StockAdjustmentResource;
use App\Http\Resources\Api\Inventory\StockMovementResource;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends BaseApiController
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display real-time stock levels.
     */
    public function stockLevels(Request $request)
    {
        $query = Product::where('tenant_id', $request->user()->tenant_id)
            ->where('stock_type', 'trackable')
            ->with(['category', 'unit', 'stock']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('code', 'like', '%' . $request->q . '%')
                    ->orWhere('barcode', 'like', '%' . $request->q . '%');
            });
        }

        $products = $query->orderBy('name')->get();

        return $this->successResponse([
            'data' => ProductStockResource::collection($products),
            'total_stock' => $products->sum(function ($p) {
                return $p->stock ? $p->stock->current_stock : 0;
            }),
        ]);
    }

    /**
     * Display comprehensive stock movement audit report.
     */
    public function movements(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));

        $query = Product::where('tenant_id', $tenantId)
            ->where('stock_type', 'trackable')
            ->with(['unit']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('code', 'like', '%' . $request->q . '%')
                    ->orWhere('barcode', 'like', '%' . $request->q . '%');
            });
        }

        $products = $query->orderBy('name')->get();

        $results = $products->map(function ($product) use ($tenantId, $startDate, $endDate) {
            // 1. Initial Balance: The new_stock of the last movement BEFORE startDate
            $initialMovement = StockMovement::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->where('created_at', '<', $startDate . ' 00:00:00')
                ->latest()
                ->first();

            $initialBalance = $initialMovement ? $initialMovement->new_stock : 0;

            // 2. Period Movements
            $movements = StockMovement::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
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
                    // So we subtract it from summary to avoid double counting if needed
                    // or just don't add it to adjustments.
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
                } elseif ($m->type === 'adjustment_in' || ($m->type === 'in' && !$m->reference_type)) {
                    $summary['adjustment_in'] += $m->quantity;
                } elseif ($m->type === 'adjustment_out' || ($m->type === 'out' && !$m->reference_type)) {
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

        return $this->successResponse([
            'period' => ['start' => $startDate, 'end' => $endDate],
            'items' => $results,
        ]);
    }

    /**
     * Display detailed movement log for a specific product.
     */
    public function movementDetail(Request $request, Product $product)
    {
        $this->authorizeTenant($product);

        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));

        $movements = StockMovement::where('tenant_id', $request->user()->tenant_id)
            ->where('product_id', $product->id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['user'])
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->successResponse([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ],
            'movements' => StockMovementResource::collection($movements),
        ]);
    }

    /**
     * Multi-stage Stock Adjustment (Opname)
     * Statuses: I (Investigation/Draft 1), D (Done/Verify), A (Adjusted/Final)
     */
    public function adjust(StoreStockAdjustmentRequest $request)
    {
        $validated = $request->validated();
        $tenantId = $request->user()->tenant_id;
        $userId = $request->user()->id;

        return DB::transaction(function () use ($validated, $tenantId, $userId) {

            // 1. Find or Create Adjustment
            if (isset($validated['id'])) {
                $adjustment = StockAdjustment::where('tenant_id', $tenantId)->findOrFail($validated['id']);

                // If already Adjusted, cannot update
                if ($adjustment->status === 'A') {
                    return $this->errorResponse('Cannot update finalized adjustment', 422);
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
                    'adjustment_number' => 'ADJ-' . strtoupper(uniqid()),
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
                            $difference, // Pass actual difference (positive/negative)
                            $type,
                            $userId,
                            get_class($adjustment),
                            $adjustment->id,
                            'Stock Opname: ' . $adjustment->adjustment_number
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

            return $this->successResponse(
                new StockAdjustmentResource($adjustment->load(['user', 'items.product'])),
                $validated['status'] === 'A' ? 'Stock opname finalized successfully' : 'Draft saved successfully',
                isset($validated['id']) ? 200 : 201
            );
        });
    }

    /**
     * Record financial loss to Transactions.
     */
    private function recordLossTransaction($tenantId, $userId, $amount, $adjustment)
    {
        // Find or create "Inventory Loss" category
        $category = ExpenseCategory::firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => 'Inventory Loss'],
            ['code' => 'EXP-LOSS', 'is_active' => true]
        );

        // Find default account (usually Cash/Operating)
        $account = Account::where('tenant_id', $tenantId)->where('is_active', true)->first();

        if ($account) {
            Transaction::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'account_id' => $account->id,
                'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                'type' => 'expense',
                'amount' => $amount,
                'transaction_date' => now(),
                'description' => 'Inventory Loss from Opname #' . $adjustment->adjustment_number,
                'reference_type' => get_class($adjustment),
                'reference_id' => $adjustment->id,
            ]);

            // Note: In a real app, you might also want to update the account balance here
            // but usually that's handled in Transaction model observers or service.
        }
    }

    /**
     * Display adjustment history.
     */
    public function adjustmentHistory(Request $request)
    {
        $adjustments = StockAdjustment::where('tenant_id', $request->user()->tenant_id)
            ->with(['user', 'items.product'])
            ->latest()
            ->paginate(20);

        return $this->successResponse(StockAdjustmentResource::collection($adjustments)->response()->getData(true));
    }

    public function showAdjustment(StockAdjustment $adjustment)
    {
        $this->authorizeTenant($adjustment);

        return $this->successResponse(new StockAdjustmentResource($adjustment->load(['user', 'items.product'])));
    }
}
