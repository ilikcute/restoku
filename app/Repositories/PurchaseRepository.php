<?php

namespace App\Repositories;

use App\Interfaces\PurchaseRepositoryInterface;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function getAllByTenant(int $tenantId, int $perPage = 20)
    {
        return Purchase::where('tenant_id', $tenantId)
            ->with(['supplier', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function createPurchase(int $tenantId, int $userId, array $validated)
    {
        return DB::transaction(function () use ($tenantId, $userId, $validated) {
            $subtotal = 0;
            $taxAmountTotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::where('tenant_id', $tenantId)->findOrFail($item['product_id']);
                $itemSubtotal = $item['cost_price'] * $item['quantity'];

                $taxRate = $validated['tax_rate'] ?? 0;
                $itemTax = $itemSubtotal * ($taxRate / 100);

                $subtotal += $itemSubtotal;
                $taxAmountTotal += $itemTax;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'cost_price' => $item['cost_price'],
                    'quantity' => $item['quantity'],
                    'tax_amount' => $itemTax,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $totalAmount = $subtotal + $taxAmountTotal;
            $paymentMethod = $validated['payment_method'] ?? 'cash';
            $paymentStatus = ($paymentMethod === 'credit') ? 'unpaid' : 'paid';

            $purchase = Purchase::create([
                'tenant_id' => $tenantId,
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $userId,
                'purchase_number' => $this->inventoryService->generatePurchaseNumber($tenantId),
                'purchase_date' => $validated['purchase_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmountTotal,
                'total_amount' => $totalAmount,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $purchase->items()->create($itemData);

                Product::where('tenant_id', $tenantId)
                    ->where('id', $itemData['product_id'])
                    ->update([
                        'cost_price' => $itemData['cost_price'],
                    ]);
            }

            $this->inventoryService->adjustStockFromPurchase($purchase, $userId, $validated['account_id'] ?? null);

            return $purchase->load('items.product');
        });
    }
}
