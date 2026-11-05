<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DpkadReconciliationService
{
    public function __construct(
        protected DpkadSyncService $dpkadSyncService
    ) {}

    /**
     * Reconcile orders and order_items between Restoku and resto_dpkad.
     *
     * @return array<string, mixed>
     */
    public function getReconciliation(int $tenantId, string $startDate, string $endDate): array
    {
        // Pastikan tabel dpkad tersedia
        $this->dpkadSyncService->ensureDpkadTablesExist();

        $startDateTime = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $endDateTime = Carbon::parse($endDate)->endOfDay()->toDateTimeString();

        // 1. Ambil orders Restoku
        $restokuOrders = Order::with('items')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->orderBy('created_at', 'desc')
            ->get();

        $restokuOrderIds = $restokuOrders->pluck('id')->toArray();
        $restokuOrderNumbers = $restokuOrders->pluck('order_number')->toArray();

        // 2. Ambil orders resto_dpkad
        $dpkadOrdersQuery = DB::connection('dpkad')->table('orders')
            ->where(function ($q) use ($startDateTime, $endDateTime, $restokuOrderIds, $restokuOrderNumbers) {
                $q->whereBetween('created_at', [$startDateTime, $endDateTime]);
                if (! empty($restokuOrderIds)) {
                    $q->orWhereIn('external_order_id', $restokuOrderIds);
                }
                if (! empty($restokuOrderNumbers)) {
                    $q->orWhereIn('order_number', $restokuOrderNumbers);
                }
            });

        $dpkadOrders = $dpkadOrdersQuery->get();
        $dpkadOrderIds = $dpkadOrders->pluck('id')->toArray();

        // 3. Ambil order_items resto_dpkad
        $dpkadItemsGrouped = collect();
        if (! empty($dpkadOrderIds)) {
            $dpkadItemsGrouped = DB::connection('dpkad')->table('order_items')
                ->whereIn('order_id', $dpkadOrderIds)
                ->get()
                ->groupBy('order_id');
        }

        // Mapping DPKAD
        $dpkadByExternalId = $dpkadOrders->keyBy('external_order_id');
        $dpkadByOrderNumber = $dpkadOrders->keyBy('order_number');

        $comparisonList = [];
        $matchedCount = 0;
        $mismatchedCount = 0;
        $notInDpkadCount = 0;

        $processedDpkadIds = [];

        foreach ($restokuOrders as $order) {
            $dpkadOrder = $dpkadByExternalId->get($order->id) ?? $dpkadByOrderNumber->get($order->order_number);

            $orderRow = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'date' => $order->created_at?->format('Y-m-d H:i:s'),
                'is_synced' => (bool) $order->is_synced_to_dpkad,
                'restoku' => [
                    'subtotal' => (float) $order->subtotal,
                    'tax_amount' => (float) $order->tax_amount,
                    'service_charge' => (float) $order->service_charge,
                    'total_amount' => (float) $order->total_amount,
                    'items_count' => $order->items->count(),
                    'items_qty' => (int) $order->items->sum('quantity'),
                    'items_subtotal' => (float) $order->items->sum('subtotal'),
                ],
                'dpkad' => null,
                'difference' => [
                    'subtotal' => (float) $order->subtotal,
                    'tax_amount' => (float) $order->tax_amount,
                    'service_charge' => (float) $order->service_charge,
                    'total_amount' => (float) $order->total_amount,
                    'items_count' => $order->items->count(),
                    'items_qty' => (int) $order->items->sum('quantity'),
                    'items_subtotal' => (float) $order->items->sum('subtotal'),
                ],
                'status' => 'NOT_IN_DPKAD',
                'can_sync' => true,
            ];

            if ($dpkadOrder) {
                $processedDpkadIds[] = $dpkadOrder->id;
                $items = $dpkadItemsGrouped->get($dpkadOrder->id, collect());

                $dpkadSubtotal = (float) $dpkadOrder->subtotal;
                $dpkadTax = (float) $dpkadOrder->tax_amount;
                $dpkadService = (float) $dpkadOrder->service_charge;
                $dpkadTotal = (float) $dpkadOrder->total_amount;
                $dpkadItemsCount = $items->count();
                $dpkadItemsQty = (int) $items->sum('quantity');
                $dpkadItemsSubtotal = (float) $items->sum('subtotal');

                $diffSubtotal = round($orderRow['restoku']['subtotal'] - $dpkadSubtotal, 2);
                $diffTax = round($orderRow['restoku']['tax_amount'] - $dpkadTax, 2);
                $diffService = round($orderRow['restoku']['service_charge'] - $dpkadService, 2);
                $diffTotal = round($orderRow['restoku']['total_amount'] - $dpkadTotal, 2);
                $diffItemsCount = $orderRow['restoku']['items_count'] - $dpkadItemsCount;
                $diffItemsQty = $orderRow['restoku']['items_qty'] - $dpkadItemsQty;
                $diffItemsSubtotal = round($orderRow['restoku']['items_subtotal'] - $dpkadItemsSubtotal, 2);

                $orderRow['dpkad'] = [
                    'id' => $dpkadOrder->id,
                    'subtotal' => $dpkadSubtotal,
                    'tax_amount' => $dpkadTax,
                    'service_charge' => $dpkadService,
                    'total_amount' => $dpkadTotal,
                    'items_count' => $dpkadItemsCount,
                    'items_qty' => $dpkadItemsQty,
                    'items_subtotal' => $dpkadItemsSubtotal,
                ];

                $orderRow['difference'] = [
                    'subtotal' => $diffSubtotal,
                    'tax_amount' => $diffTax,
                    'service_charge' => $diffService,
                    'total_amount' => $diffTotal,
                    'items_count' => $diffItemsCount,
                    'items_qty' => $diffItemsQty,
                    'items_subtotal' => $diffItemsSubtotal,
                ];

                $isMatch = (
                    abs($diffTotal) < 0.01 &&
                    abs($diffSubtotal) < 0.01 &&
                    abs($diffTax) < 0.01 &&
                    abs($diffService) < 0.01 &&
                    $diffItemsCount === 0 &&
                    $diffItemsQty === 0
                );

                if ($isMatch) {
                    $orderRow['status'] = 'MATCH';
                    $orderRow['can_sync'] = false;
                    $matchedCount++;
                } else {
                    $orderRow['status'] = 'MISMATCH';
                    $orderRow['can_sync'] = true;
                    $mismatchedCount++;
                }
            } else {
                $notInDpkadCount++;
            }

            $comparisonList[] = $orderRow;
        }

        // Periksa DPKAD orders yang tidak ada di Restoku (orphan)
        $notInRestokuCount = 0;
        foreach ($dpkadOrders as $dOrder) {
            if (in_array($dOrder->id, $processedDpkadIds)) {
                continue;
            }

            $items = $dpkadItemsGrouped->get($dOrder->id, collect());
            $dpkadSubtotal = (float) $dOrder->subtotal;
            $dpkadTax = (float) $dOrder->tax_amount;
            $dpkadService = (float) $dOrder->service_charge;
            $dpkadTotal = (float) $dOrder->total_amount;
            $dpkadItemsCount = $items->count();
            $dpkadItemsQty = (int) $items->sum('quantity');
            $dpkadItemsSubtotal = (float) $items->sum('subtotal');

            $comparisonList[] = [
                'id' => null,
                'order_number' => $dOrder->order_number,
                'customer_name' => $dOrder->customer_name,
                'date' => Carbon::parse($dOrder->created_at)->format('Y-m-d H:i:s'),
                'is_synced' => false,
                'restoku' => null,
                'dpkad' => [
                    'id' => $dOrder->id,
                    'subtotal' => $dpkadSubtotal,
                    'tax_amount' => $dpkadTax,
                    'service_charge' => $dpkadService,
                    'total_amount' => $dpkadTotal,
                    'items_count' => $dpkadItemsCount,
                    'items_qty' => $dpkadItemsQty,
                    'items_subtotal' => $dpkadItemsSubtotal,
                ],
                'difference' => [
                    'subtotal' => -$dpkadSubtotal,
                    'tax_amount' => -$dpkadTax,
                    'service_charge' => -$dpkadService,
                    'total_amount' => -$dpkadTotal,
                    'items_count' => -$dpkadItemsCount,
                    'items_qty' => -$dpkadItemsQty,
                    'items_subtotal' => -$dpkadItemsSubtotal,
                ],
                'status' => 'NOT_IN_RESTOKU',
                'can_sync' => false,
            ];

            $notInRestokuCount++;
        }

        // Summary Aggregations
        $restokuTotalOrders = $restokuOrders->count();
        $dpkadTotalOrders = $dpkadOrders->count();

        $restokuTotalAmount = (float) $restokuOrders->sum('total_amount');
        $dpkadTotalAmount = (float) $dpkadOrders->sum('total_amount');

        $restokuTotalSubtotal = (float) $restokuOrders->sum('subtotal');
        $dpkadTotalSubtotal = (float) $dpkadOrders->sum('subtotal');

        $restokuTotalTax = (float) $restokuOrders->sum('tax_amount');
        $dpkadTotalTax = (float) $dpkadOrders->sum('tax_amount');

        $restokuTotalService = (float) $restokuOrders->sum('service_charge');
        $dpkadTotalService = (float) $dpkadOrders->sum('service_charge');

        $restokuTotalItemsCount = (int) $restokuOrders->sum(fn ($o) => $o->items->count());
        $dpkadTotalItemsCount = (int) $dpkadItemsGrouped->flatten(1)->count();

        $restokuTotalItemsQty = (int) $restokuOrders->sum(fn ($o) => $o->items->sum('quantity'));
        $dpkadTotalItemsQty = (int) $dpkadItemsGrouped->flatten(1)->sum('quantity');

        $restokuTotalItemsSubtotal = (float) $restokuOrders->sum(fn ($o) => $o->items->sum('subtotal'));
        $dpkadTotalItemsSubtotal = (float) $dpkadItemsGrouped->flatten(1)->sum('subtotal');

        $isBalanced = (
            $restokuTotalOrders === $dpkadTotalOrders &&
            abs($restokuTotalAmount - $dpkadTotalAmount) < 0.01 &&
            $mismatchedCount === 0 &&
            $notInDpkadCount === 0 &&
            $notInRestokuCount === 0
        );

        return [
            'summary' => [
                'is_balanced' => $isBalanced,
                'orders' => [
                    'restoku_count' => $restokuTotalOrders,
                    'dpkad_count' => $dpkadTotalOrders,
                    'diff_count' => $restokuTotalOrders - $dpkadTotalOrders,
                    'matched_count' => $matchedCount,
                    'mismatched_count' => $mismatchedCount,
                    'not_in_dpkad_count' => $notInDpkadCount,
                    'not_in_restoku_count' => $notInRestokuCount,
                    'restoku_total' => $restokuTotalAmount,
                    'dpkad_total' => $dpkadTotalAmount,
                    'diff_total' => round($restokuTotalAmount - $dpkadTotalAmount, 2),
                    'restoku_subtotal' => $restokuTotalSubtotal,
                    'dpkad_subtotal' => $dpkadTotalSubtotal,
                    'diff_subtotal' => round($restokuTotalSubtotal - $dpkadTotalSubtotal, 2),
                    'restoku_tax' => $restokuTotalTax,
                    'dpkad_tax' => $dpkadTotalTax,
                    'diff_tax' => round($restokuTotalTax - $dpkadTotalTax, 2),
                    'restoku_service' => $restokuTotalService,
                    'dpkad_service' => $dpkadTotalService,
                    'diff_service' => round($restokuTotalService - $dpkadTotalService, 2),
                ],
                'order_items' => [
                    'restoku_count' => $restokuTotalItemsCount,
                    'dpkad_count' => $dpkadTotalItemsCount,
                    'diff_count' => $restokuTotalItemsCount - $dpkadTotalItemsCount,
                    'restoku_qty' => $restokuTotalItemsQty,
                    'dpkad_qty' => $dpkadTotalItemsQty,
                    'diff_qty' => $restokuTotalItemsQty - $dpkadTotalItemsQty,
                    'restoku_subtotal' => $restokuTotalItemsSubtotal,
                    'dpkad_subtotal' => $dpkadTotalItemsSubtotal,
                    'diff_subtotal' => round($restokuTotalItemsSubtotal - $dpkadTotalItemsSubtotal, 2),
                ],
            ],
            'orders' => $comparisonList,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    /**
     * Sync orders to DPKAD database with force option to fix mismatches.
     *
     * @param  array<int>  $orderIds
     * @return array<string, mixed>
     */
    public function syncOrders(array $orderIds): array
    {
        return $this->dpkadSyncService->syncOrders($orderIds, true);
    }
}

