<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
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

        $restokuOrders = $this->getRestokuOrders($tenantId, $startDateTime, $endDateTime);
        $orderIds = $restokuOrders->pluck('id')->all();
        $orderNumbers = $restokuOrders->pluck('order_number')->all();

        $dpkadOrders = $this->getDpkadOrders($startDateTime, $endDateTime, $orderIds, $orderNumbers);
        $dpkadOrderIds = $dpkadOrders->pluck('id')->all();

        $dpkadItemsGrouped = $this->getDpkadItemsGrouped($dpkadOrderIds);

        [$comparisonList, $counts] = $this->compareOrders($restokuOrders, $dpkadOrders, $dpkadItemsGrouped);
        $summary = $this->buildSummary($restokuOrders, $dpkadOrders, $dpkadItemsGrouped, $counts);

        return [
            'summary' => $summary,
            'orders' => $comparisonList,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    /**
     * Get completed Restoku orders with their items.
     *
     * @return EloquentCollection<int, Order>
     */
    protected function getRestokuOrders(int $tenantId, string $startDateTime, string $endDateTime): EloquentCollection
    {
        return Order::with('items')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get orders from resto_dpkad matching date range or order ids.
     *
     * @param  array<int, int>  $orderIds
     * @param  array<int, string>  $orderNumbers
     */
    protected function getDpkadOrders(string $startDateTime, string $endDateTime, array $orderIds, array $orderNumbers): Collection
    {
        return DB::connection('dpkad')->table('orders')
            ->where(function ($q) use ($startDateTime, $endDateTime, $orderIds, $orderNumbers) {
                $q->whereBetween('created_at', [$startDateTime, $endDateTime]);
                if (! empty($orderIds)) {
                    $q->orWhereIn('external_order_id', $orderIds);
                }
                if (! empty($orderNumbers)) {
                    $q->orWhereIn('order_number', $orderNumbers);
                }
            })
            ->get();
    }

    /**
     * Get order_items from resto_dpkad grouped by order_id.
     *
     * @param  array<int, int>  $dpkadOrderIds
     */
    protected function getDpkadItemsGrouped(array $dpkadOrderIds): Collection
    {
        if (empty($dpkadOrderIds)) {
            return collect();
        }

        return DB::connection('dpkad')->table('order_items')
            ->whereIn('order_id', $dpkadOrderIds)
            ->get()
            ->groupBy('order_id');
    }

    /**
     * Compare orders and order_items between Restoku and DPKAD.
     *
     * @param  EloquentCollection<int, Order>  $restokuOrders
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, int>}
     */
    protected function compareOrders(
        EloquentCollection $restokuOrders,
        Collection $dpkadOrders,
        Collection $dpkadItemsGrouped
    ): array {
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
                /** @var Collection $items */
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

            /** @var Collection $items */
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

        $counts = [
            'matched' => $matchedCount,
            'mismatched' => $mismatchedCount,
            'not_in_dpkad' => $notInDpkadCount,
            'not_in_restoku' => $notInRestokuCount,
        ];

        return [$comparisonList, $counts];
    }

    /**
     * Build aggregated summary comparison.
     *
     * @param  EloquentCollection<int, Order>  $restokuOrders
     * @param  array<string, int>  $counts
     * @return array<string, mixed>
     */
    protected function buildSummary(
        EloquentCollection $restokuOrders,
        Collection $dpkadOrders,
        Collection $dpkadItemsGrouped,
        array $counts
    ): array {
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
            $counts['mismatched'] === 0 &&
            $counts['not_in_dpkad'] === 0 &&
            $counts['not_in_restoku'] === 0
        );

        return [
            'is_balanced' => $isBalanced,
            'orders' => [
                'restoku_count' => $restokuTotalOrders,
                'dpkad_count' => $dpkadTotalOrders,
                'diff_count' => $restokuTotalOrders - $dpkadTotalOrders,
                'matched_count' => $counts['matched'],
                'mismatched_count' => $counts['mismatched'],
                'not_in_dpkad_count' => $counts['not_in_dpkad'],
                'not_in_restoku_count' => $counts['not_in_restoku'],
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
