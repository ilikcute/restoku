<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function stats(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $today = Carbon::today();

        // 1. Sales Stats
        $salesToday = Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $salesCountToday = Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', $today)
            ->count();

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

        // 4. Comparison Stats (Yesterday)
        $yesterday = Carbon::yesterday();
        $salesYesterday = Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', $yesterday)
            ->sum('total_amount');

        $salesCountYesterday = Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', $yesterday)
            ->count();

        // 5. Customer Stats (Real from Customers Table)
        $totalCustomers = Customer::where('tenant_id', $tenantId)->count();

        $customersYesterday = Customer::where('tenant_id', $tenantId)
            ->whereDate('created_at', $yesterday)
            ->count();

        $customersToday = Customer::where('tenant_id', $tenantId)
            ->whereDate('created_at', $today)
            ->count();

        // 6. Last 7 Days Sales Chart Data
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $total = Order::where('tenant_id', $tenantId)
                ->whereDate('created_at', $date)
                ->sum('total_amount');

            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'total' => (float) $total,
            ];
        }

        // 5. Top Selling Products (Trending)
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
                \DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name', 'categories.name', 'products.price', 'products.image')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'category' => $item->category_name ?? 'Uncategorized',
                    'price' => (float) $item->price,
                    'orders' => (float) $item->total_sold,
                    'image' => $item->image ? asset('storage/'.$item->image) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80',
                    'rating' => '4.5', // Placeholder as we don't have rating system yet
                ];
            });

        // 6. Recent Orders
        $recentOrders = Order::where('tenant_id', $tenantId)
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();

        return $this->successResponse([
            'sales_today' => (float) $salesToday,
            'sales_yesterday' => (float) $salesYesterday,
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
        ]);
    }
}
