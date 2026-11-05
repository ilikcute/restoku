<?php

namespace App\Repositories;

use App\Interfaces\DashboardRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getStats(int $tenantId): array
    {
        return Cache::remember("dashboard:stats:{$tenantId}", 60, fn () => $this->buildStats($tenantId));
    }

    private function buildStats(int $tenantId): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // 1. Sales hari ini + kemarin dalam 1 query
        $salesRows = Order::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $yesterday)
            ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total, COUNT(*) as cnt')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $todayKey = $today->format('Y-m-d');
        $yesterdayKey = $yesterday->format('Y-m-d');

        $salesToday = (float) ($salesRows[$todayKey]->total ?? 0);
        $salesCountToday = (int) ($salesRows[$todayKey]->cnt ?? 0);
        $salesYesterday = (float) ($salesRows[$yesterdayKey]->total ?? 0);
        $salesCountYesterday = (int) ($salesRows[$yesterdayKey]->cnt ?? 0);

        // 2. Inventory Stats
        $lowStockCount = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->where('stocks.tenant_id', $tenantId)
            ->where('products.stock_type', 'trackable')
            ->whereColumn('stocks.current_stock', '<=', 'products.minimum_stock')
            ->count();

        $totalProducts = Product::where('tenant_id', $tenantId)->count();

        // 3. Financial Stats
        $expensesToday = Transaction::where('tenant_id', $tenantId)
            ->whereDate('transaction_date', $today)
            ->where('type', 'expense')
            ->sum('amount');

        // 4. Customer Stats (3 query digabung jadi 2)
        $customerRows = Customer::where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total, SUM(DATE(created_at) = ?) as today_cnt, SUM(DATE(created_at) = ?) as yesterday_cnt', [
                $todayKey, $yesterdayKey,
            ])
            ->first();

        $totalCustomers = (int) ($customerRows->total ?? 0);
        $customersToday = (int) ($customerRows->today_cnt ?? 0);
        $customersYesterday = (int) ($customerRows->yesterday_cnt ?? 0);

        // 5. Last 7 Days Chart — 1 query, bukan 7
        $sevenDaysAgo = Carbon::today()->subDays(6)->format('Y-m-d');
        $chartRaw = Order::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $key = $date->format('Y-m-d');
            $chartData[] = [
                'date' => $key,
                'label' => $date->format('D'),
                'total' => (float) ($chartRaw[$key]->total ?? 0),
            ];
        }

        // 6. Trending Products
        $trendingProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.tenant_id', $tenantId)
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                'categories.name as category_name',
                'products.price',
                'products.image',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name', 'categories.name', 'products.price', 'products.image')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->product_name,
                'category' => $item->category_name ?? 'Uncategorized',
                'price' => (float) $item->price,
                'orders' => (float) $item->total_sold,
                'image' => $item->image ? asset('storage/'.$item->image) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80',
                'rating' => '4.5',
            ]);

        // 7. Recent Orders
        $recentOrders = Order::where('tenant_id', $tenantId)
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();

        return [
            'sales_today' => $salesToday,
            'sales_yesterday' => $salesYesterday,
            'sales_count_today' => $salesCountToday,
            'sales_count_yesterday' => $salesCountYesterday,
            'low_stock_count' => $lowStockCount,
            'total_products' => $totalProducts,
            'total_customers' => $totalCustomers,
            'customers_today' => $customersToday,
            'customers_yesterday' => $customersYesterday,
            'expenses_today' => (float) $expensesToday,
            'chart_data' => $chartData,
            'trending_products' => $trendingProducts,
            'recent_orders' => $recentOrders,
        ];
    }
}
