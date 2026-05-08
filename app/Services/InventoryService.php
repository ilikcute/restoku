<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Transaction;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Generate unique purchase number format: PUR-YYMMDD-XXXX.
     */
    public function generatePurchaseNumber(int $tenantId): string
    {
        $datePrefix = 'PUR-'.date('ymd').'-';

        $lastPurchase = Purchase::where('purchase_number', 'LIKE', $datePrefix.'%')
            ->where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;
        if ($lastPurchase) {
            $lastNumber = (int) str_replace($datePrefix, '', $lastPurchase->purchase_number);
            $nextNumber = $lastNumber + 1;
        }

        return $datePrefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update stock for a product and record movement.
     */
    public function updateStock(
        int $tenantId,
        int $productId,
        float $quantity,
        string $type,
        int $userId,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): ?Stock {
        return DB::transaction(function () use (
            $tenantId, $productId, $quantity, $type, $userId, $referenceType, $referenceId, $notes
        ) {
            $product = Product::where('tenant_id', $tenantId)->findOrFail($productId);

            // 1. Lock stock row so concurrent sales/returns cannot read stale balances.
            $stock = Stock::where('tenant_id', $tenantId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $createdStock = null;

                try {
                    $createdStock = Stock::create([
                        'tenant_id' => $tenantId,
                        'product_id' => $productId,
                        'current_stock' => 0,
                    ]);
                } catch (QueryException) {
                    // Another transaction may have created the unique tenant/product row first.
                }

                $stock = Stock::query()
                    ->when(
                        $createdStock,
                        fn ($query) => $query->whereKey($createdStock->id),
                        fn ($query) => $query
                            ->where('tenant_id', $tenantId)
                            ->where('product_id', $productId)
                    )
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $previousStock = $stock->current_stock;
            $newStock = $previousStock;

            // 2. Only update balance if product is tracked
            if ($product->isTracked()) {
                $newStock = $previousStock + $quantity;

                if ($newStock < 0) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok {$product->name} tidak mencukupi.",
                    ]);
                }

                $stock->update([
                    'current_stock' => $newStock,
                ]);
            }

            // 3. Record movement (always record for audit trail as requested)
            StockMovement::create([
                'tenant_id' => $tenantId,
                'product_id' => $productId,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => abs($quantity),
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes.($product->isTracked() ? '' : ' (Non-Tracked Product)'),
            ]);

            return $stock;
        });
    }

    /**
     * Record financial transaction related to inventory/sales.
     */
    public function recordTransaction(
        int $tenantId,
        int $accountId,
        int $userId,
        string $type,
        float $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $expenseCategoryId = null,
        ?int $incomeCategoryId = null,
        ?int $shiftId = null
    ): Transaction {
        return DB::transaction(function () use (
            $tenantId, $accountId, $userId, $type, $amount, $description, $referenceType, $referenceId, $expenseCategoryId, $incomeCategoryId, $shiftId
        ) {
            // 1. Update Account Balance
            $account = Account::where('tenant_id', $tenantId)
                ->where('id', $accountId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($type === 'income') {
                $account->update([
                    'balance' => $account->balance + $amount,
                ]);
            } elseif ($type === 'expense') {
                $account->update([
                    'balance' => $account->balance - $amount,
                ]);
            }

            // If shiftId is null, try to find active shift for the user
            if (! $shiftId) {
                $shiftId = Shift::where('tenant_id', $tenantId)
                    ->where('user_id', $userId)
                    ->where('status', 'open')
                    ->value('id');
            }

            // 2. Create Transaction Record
            return Transaction::create([
                'tenant_id' => $tenantId,
                'account_id' => $accountId,
                'user_id' => $userId,
                'shift_id' => $shiftId,
                'expense_category_id' => $expenseCategoryId,
                'income_category_id' => $incomeCategoryId,
                'transaction_number' => 'TRX-'.strtoupper(uniqid()),
                'transaction_date' => now(),
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    /**
     * Handle stock reduction from Order (Sales).
     */
    public function adjustStockFromOrder($order, int $userId, int $accountId)
    {
        return DB::transaction(function () use ($order, $userId, $accountId) {
            foreach ($order->items as $item) {
                $this->updateStock(
                    $order->tenant_id,
                    $item->product_id,
                    -($item->quantity),
                    'out',
                    $userId,
                    get_class($order),
                    $order->id,
                    "Sales Order: {$order->order_number}"
                );
            }

            // Record Income Transaction
            $this->recordTransaction(
                $order->tenant_id,
                $accountId,
                $userId,
                'income',
                $order->total_amount,
                "Sales Order Payment: {$order->order_number}",
                get_class($order),
                $order->id
            );
        });
    }

    /**
     * Handle stock addition from Purchase.
     */
    public function adjustStockFromPurchase($purchase, int $userId, ?int $accountId = null)
    {
        return DB::transaction(function () use ($purchase, $userId, $accountId) {
            foreach ($purchase->items as $item) {
                $this->updateStock(
                    $purchase->tenant_id,
                    $item->product_id,
                    $item->quantity,
                    'in',
                    $userId,
                    get_class($purchase),
                    $purchase->id,
                    "Purchase Order: {$purchase->purchase_number}"
                );
            }

            // Record Expense Transaction only if account is provided
            if ($accountId) {
                $this->recordTransaction(
                    $purchase->tenant_id,
                    $accountId,
                    $userId,
                    'expense',
                    $purchase->total_amount,
                    "Purchase Payment to Supplier: {$purchase->purchase_number}",
                    get_class($purchase),
                    $purchase->id
                );
            }
        });
    }

    /**
     * Handle stock addition from Order Return (Sales Return).
     */
    public function processOrderReturn($order, array $returnItems, int $userId, int $accountId)
    {
        return DB::transaction(function () use ($order, $returnItems, $userId, $accountId) {
            $totalReturnAmount = 0;

            foreach ($returnItems as $itemData) {
                $orderItem = $order->items()
                    ->where('id', $itemData['order_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
                $qtyToReturn = $itemData['quantity'];

                if ($qtyToReturn <= 0) {
                    continue;
                }
                if (($orderItem->return_quantity + $qtyToReturn) > $orderItem->quantity) {
                    throw new Exception("Kuantitas retur melebihi kuantitas pesanan untuk produk: {$orderItem->product_name}");
                }

                $returnAmount = ($orderItem->price - ($orderItem->discount_amount / $orderItem->quantity)) * $qtyToReturn;

                $orderItem->increment('return_quantity', $qtyToReturn);
                $orderItem->increment('return_amount', $returnAmount);
                $totalReturnAmount += $returnAmount;

                // Update Stock (Return Sales = Stock IN)
                $this->updateStock(
                    $order->tenant_id,
                    $orderItem->product_id,
                    $qtyToReturn,
                    'in',
                    $userId,
                    get_class($order),
                    $order->id,
                    "Retur Penjualan: {$order->order_number}"
                );
            }

            if ($totalReturnAmount > 0) {
                $order->increment('total_return', $totalReturnAmount);
                $order->update([
                    'return_date' => now(),
                    'return_user_id' => $userId,
                ]);

                // Record Refund Transaction (Expense)
                $this->recordTransaction(
                    $order->tenant_id,
                    $accountId,
                    $userId,
                    'expense',
                    $totalReturnAmount,
                    "Refund Penjualan (Retur): {$order->order_number}",
                    get_class($order),
                    $order->id
                );
            }

            return $order;
        });
    }

    /**
     * Handle stock reduction from Purchase Return.
     */
    public function processPurchaseReturn($purchase, array $returnItems, int $userId, int $accountId)
    {
        return DB::transaction(function () use ($purchase, $returnItems, $userId, $accountId) {
            $totalReturnAmount = 0;

            foreach ($returnItems as $itemData) {
                $purchaseItem = $purchase->items()
                    ->where('id', $itemData['purchase_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
                $qtyToReturn = $itemData['quantity'];

                if ($qtyToReturn <= 0) {
                    continue;
                }
                if (($purchaseItem->return_quantity + $qtyToReturn) > $purchaseItem->quantity) {
                    throw new Exception("Kuantitas retur melebihi kuantitas pembelian untuk produk: {$purchaseItem->product_name}");
                }

                $returnAmount = ($purchaseItem->cost_price - ($purchaseItem->discount_amount / $purchaseItem->quantity)) * $qtyToReturn;

                $purchaseItem->increment('return_quantity', $qtyToReturn);
                $purchaseItem->increment('return_amount', $returnAmount);
                $totalReturnAmount += $returnAmount;

                // Update Stock (Return Purchase = Stock OUT)
                $this->updateStock(
                    $purchase->tenant_id,
                    $purchaseItem->product_id,
                    -($qtyToReturn),
                    'out',
                    $userId,
                    get_class($purchase),
                    $purchase->id,
                    "Retur Pembelian: {$purchase->purchase_number}"
                );
            }

            if ($totalReturnAmount > 0) {
                $purchase->increment('total_return', $totalReturnAmount);
                $purchase->update([
                    'return_date' => now(),
                    'return_user_id' => $userId,
                ]);

                // Record Refund Transaction (Income)
                $this->recordTransaction(
                    $purchase->tenant_id,
                    $accountId,
                    $userId,
                    'income',
                    $totalReturnAmount,
                    "Refund Pembelian (Retur): {$purchase->purchase_number}",
                    get_class($purchase),
                    $purchase->id
                );
            }

            return $purchase;
        });
    }
}
