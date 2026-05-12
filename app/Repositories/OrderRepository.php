<?php

namespace App\Repositories;

use App\Events\OrderCreated;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shift;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\PrinterService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected PrinterService $printerService,
        protected OrderService $orderService,
    ) {}

    public function getAllByTenant(int $tenantId, int $perPage = 20)
    {
        return Order::where('tenant_id', $tenantId)
            ->with(['items.product', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function processOrder(int $tenantId, int $userId, Shift $shift, array $validated, string $idempotencyKey)
    {
        $lockKey = 'order:'.$tenantId.':'.$idempotencyKey;

        $existingOrder = Order::where('tenant_id', $tenantId)
            ->where('idempotency_key', $idempotencyKey)
            ->with(['items.product', 'user', 'shift'])
            ->first();

        if ($existingOrder) {
            return ['status' => 'existing', 'order' => $existingOrder];
        }

        $lock = Cache::lock($lockKey, 60);

        if (! $lock->get()) {
            $existingOrder = Order::where('tenant_id', $tenantId)
                ->where('idempotency_key', $idempotencyKey)
                ->with(['items.product', 'user', 'shift'])
                ->first();

            if ($existingOrder) {
                return ['status' => 'existing', 'order' => $existingOrder];
            }

            throw new \Exception('Transaksi ini sedang diproses. Mohon tunggu sebentar atau refresh halaman jika order tidak muncul.', 409);
        }

        try {
            return DB::transaction(function () use ($validated, $tenantId, $userId, $shift, $idempotencyKey) {
                $existingOrder = Order::where('tenant_id', $tenantId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($existingOrder) {
                    return ['status' => 'existing', 'order' => $existingOrder->load(['items.product', 'user', 'shift'])];
                }

                $orderType = $validated['order_type'] ?? 'regular';
                $totals = $this->orderService->calculateOrderTotals($validated['items'], $orderType, $tenantId);

                $paidAmount = $validated['paid_amount'] ?? 0;
                $changeAmount = $paidAmount - $totals['grand_total'];

                if ($changeAmount < 0 && $validated['payment_method'] === 'cash') {
                    throw new \Exception('Uang pembayaran tidak boleh kurang dari total tagihan (Rp '.number_format($totals['grand_total'], 0, ',', '.').').', 422);
                }

                if ($validated['payment_method'] !== 'cash') {
                    $paidAmount = $totals['grand_total'];
                    $changeAmount = 0;
                }

                $orderNumber = $this->orderService->generateOrderNumber($tenantId);

                $order = Order::create([
                    'tenant_id' => $tenantId,
                    'shift_id' => $shift->id,
                    'user_id' => $userId,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'order_number' => $orderNumber,
                    'idempotency_key' => $idempotencyKey,
                    'customer_name' => $validated['customer_name'] ?? (
                        $validated['customer_id']
                            ? Customer::find($validated['customer_id'])?->name
                            : ($validated['order_type'] ?? 'Regular')
                    ),
                    'table_number' => $validated['table_number'] ?? null,
                    'subtotal' => $totals['subtotal'] + $totals['discount_total'],
                    'discount_amount' => $totals['discount_total'],
                    'service_charge' => $totals['service_total'],
                    'tax_amount' => $totals['tax_total'],
                    'rounding' => $totals['rounding'],
                    'total_amount' => $totals['grand_total'],
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Simpan items dan promosi masing-masing item
                foreach ($totals['items'] as $itemData) {
                    $appliedPromos = $itemData['applied_promotions'] ?? [];
                    unset($itemData['applied_promotions']);

                    $orderItem = $order->items()->create($itemData);

                    // Simpan ke pivot order_item_promotions
                    foreach ($appliedPromos as $promo) {
                        $orderItem->promotions()->attach($promo['promotion_id'], [
                            'discount_amount' => $promo['discount_amount'],
                        ]);
                    }
                }

                $this->inventoryService->adjustStockFromOrder($order, $userId, $validated['account_id']);

                $shift->increment('total_sales', $order->total_amount);

                try {
                    $this->printerService->printOrder($order);
                    $this->printerService->printKitchenReceipt($order);
                } catch (\Exception $e) {
                    \Log::warning('Printing failed on checkout: '.$e->getMessage());
                }

                $order->load(['items.product', 'user', 'shift']);
                event(new OrderCreated($order));

                return ['status' => 'created', 'order' => $order];
            });
        } catch (QueryException $e) {
            $existingOrder = Order::where('tenant_id', $tenantId)
                ->where('idempotency_key', $idempotencyKey)
                ->with(['items.product', 'user', 'shift'])
                ->first();

            if ($existingOrder) {
                return ['status' => 'existing', 'order' => $existingOrder];
            }

            throw $e;
        } finally {
            $lock->release();
        }
    }
}
