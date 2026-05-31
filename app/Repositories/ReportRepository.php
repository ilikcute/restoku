<?php

namespace App\Repositories;

use App\Interfaces\ReportRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Shift;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportRepository implements ReportRepositoryInterface
{
    public function getReportData(int $tenantId, string $startDate, string $endDate): array
    {
        $sales = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as gross_sales,
                SUM(subtotal) as net_sales,
                SUM(tax_amount) as total_tax,
                SUM(service_charge) as total_service,
                SUM(discount_amount) as total_discount,
                SUM(total_return) as total_returns
            ')->first();

        $cogs = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('orders.status', 'completed')
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at')
            ->sum(DB::raw('order_items.cost_price * (order_items.quantity - order_items.return_quantity)'));

        $expenses = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $otherIncome = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->whereNull('reference_type')
            ->sum('amount');

        $grossProfit = $sales->net_sales - $cogs;
        $netProfit = $grossProfit - $expenses + $otherIncome;

        return [
            'sales' => $sales,
            'cogs' => (float) $cogs,
            'gross_profit' => (float) $grossProfit,
            'expenses' => (float) $expenses,
            'other_income' => (float) $otherIncome,
            'net_profit' => (float) $netProfit,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    public function aggregateTaxReportData(Collection $orders): Collection
    {
        return $orders->groupBy(fn ($order) => $order->created_at->format('Y-m-d'))
            ->map(function ($dayOrders, $date) {
                $categories = [];
                $subtotal = 0.0;
                $tax = 0.0;
                $service = 0.0;

                foreach ($dayOrders as $order) {
                    foreach ($order->items as $item) {
                        $catName = $item->product->category->name ?? 'Lain-lain';
                        $categories[$catName] = ($categories[$catName] ?? 0) + $item->subtotal;
                    }
                    $subtotal += $order->subtotal;
                    $tax += $order->tax_amount;
                    $service += $order->service_charge;
                }

                return [
                    'date' => $date,
                    'day' => Carbon::parse($date)->translatedFormat('l'),
                    'categories' => $categories,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'service' => $service,
                    'grand_total' => $subtotal + $tax + $service,
                ];
            })
            ->values();
    }

    public function getDpkadOrders(int $tenantId, string $startDate, string $endDate): Collection
    {
        return Order::where('tenant_id', $tenantId)
            ->where('is_synced_to_dpkad', true)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->with(['items.product.category'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getFixedDpkadOrders(int $tenantId, string $startDate, string $endDate): Collection
    {
        $tenantOrderIds = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->pluck('id');

        $dpkadOrders = DB::connection('dpkad')
            ->table('orders')
            ->whereIn('external_order_id', $tenantOrderIds)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($dpkadOrders->isEmpty()) {
            return collect();
        }

        $dpkadOrderIds = $dpkadOrders->pluck('id');
        $dpkadItems = DB::connection('dpkad')
            ->table('order_items')
            ->whereIn('order_id', $dpkadOrderIds)
            ->get()
            ->groupBy('order_id');

        return $dpkadOrders->map(function ($order) use ($dpkadItems) {
            $order->created_at = Carbon::parse($order->created_at);

            $items = collect($dpkadItems->get($order->id, []))->map(function ($item) {
                $localProduct = Product::with('category')->where('name', $item->product_name)->first();
                $categoryName = $localProduct?->category?->name ?? 'Lain-lain';

                return (object) [
                    'subtotal' => (float) $item->subtotal,
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'product_name' => $item->product_name,
                    'product' => (object) [
                        'category' => (object) [
                            'name' => $categoryName,
                        ],
                    ],
                ];
            });

            return (object) [
                'id' => $order->id,
                'external_order_id' => $order->external_order_id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'subtotal' => (float) $order->subtotal,
                'tax_amount' => (float) $order->tax_amount,
                'service_charge' => (float) $order->service_charge,
                'total_amount' => (float) $order->total_amount,
                'created_at' => $order->created_at,
                'items' => $items,
            ];
        });
    }

    public function getDailyChart(int $tenantId, string $startDate, string $endDate): Collection
    {
        return collect(Order::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get());
    }

    public function getTopProducts(int $tenantId, string $startDate, string $endDate): Collection
    {
        return collect(DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('orders.status', 'completed')
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at')
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity - order_items.return_quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get());
    }

    public function getTransactions(int $tenantId, string $startDate, string $endDate): Collection
    {
        return Order::with(['user', 'shift'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPurchases(int $tenantId, string $startDate, string $endDate): Collection
    {
        return Purchase::with(['user', 'supplier'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }

    public function getSalesReturns(int $tenantId, string $startDate, string $endDate): Collection
    {
        return Order::with(['user', 'returnUser'])
            ->where('tenant_id', $tenantId)
            ->where('total_return', '>', 0)
            ->whereBetween('return_date', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('return_date', 'desc')
            ->get();
    }

    public function getPurchaseReturns(int $tenantId, string $startDate, string $endDate): Collection
    {
        return Purchase::with(['user', 'supplier', 'returnUser'])
            ->where('tenant_id', $tenantId)
            ->where('total_return', '>', 0)
            ->whereBetween('return_date', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('return_date', 'desc')
            ->get();
    }

    public function getSalesDetailItems(int $tenantId, string $startDate, string $endDate): Collection
    {
        return OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('orders.status', 'completed')
            ->whereNull('orders.deleted_at')
            ->select('order_items.*')
            ->with(['order.user', 'product.category'])
            ->get();
    }

    public function getShiftSalesTotals(): Collection
    {
        return DB::table('orders')
            ->whereNotNull('shift_id')
            ->where('status', 'completed')
            ->whereNull('deleted_at')
            ->select('shift_id', DB::raw('SUM(total_amount) as sales_total'))
            ->groupBy('shift_id')
            ->pluck('sales_total', 'shift_id');
    }

    public function getShifts(int $tenantId, string $startDate, string $endDate): Collection
    {
        return Shift::with(['user'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->get();
    }

    public function getOrdersByStatus(int $tenantId, string $startDate, string $endDate, string $status = 'completed'): Collection
    {
        return Order::with(['user'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('status', $status)
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
