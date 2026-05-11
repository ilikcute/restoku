<?php

namespace App\Repositories;

use App\Interfaces\ReturnRepositoryInterface;
use App\Models\Order;
use App\Models\Purchase;
use App\Services\InventoryService;

class ReturnRepository implements ReturnRepositoryInterface
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function processOrderReturn(int $tenantId, int $orderId, array $items, int $userId, ?int $accountId)
    {
        $order = Order::where('tenant_id', $tenantId)
            ->with('items')
            ->findOrFail($orderId);

        $this->inventoryService->processOrderReturn(
            $order,
            $items,
            $userId,
            $accountId
        );

        return $order->load(['items.product', 'user', 'returnUser']);
    }

    public function processPurchaseReturn(int $tenantId, int $purchaseId, array $items, int $userId, ?int $accountId)
    {
        $purchase = Purchase::where('tenant_id', $tenantId)
            ->with('items')
            ->findOrFail($purchaseId);

        $this->inventoryService->processPurchaseReturn(
            $purchase,
            $items,
            $userId,
            $accountId
        );

        return $purchase->load(['items.product', 'user', 'supplier', 'returnUser']);
    }

    public function searchTransaction(int $tenantId, string $number, string $type)
    {
        if ($type === 'order') {
            return Order::where('tenant_id', $tenantId)
                ->where('order_number', $number)
                ->with(['items.product', 'user', 'returnUser'])
                ->first();
        } else {
            return Purchase::where('tenant_id', $tenantId)
                ->where('purchase_number', $number)
                ->with(['items.product', 'user', 'supplier', 'returnUser'])
                ->first();
        }
    }
}
