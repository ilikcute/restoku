<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementController extends BaseApiController
{
    /**
     * Get procurement recommendations based on ROP formula.
     */
    public function recommendations(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $days = max(1, $request->get('days', 30));

        // 1. Calculate Average Daily Sales for each product in the last X days
        $salesData = OrderItem::whereHas('order', function ($q) use ($tenantId, $days) {
            $q->where('tenant_id', $tenantId)
                ->where('created_at', '>=', Carbon::now()->subDays($days));
        })
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->get()
            ->pluck('total_qty', 'product_id');

        // 2. Get all trackable products
        $products = Product::where('tenant_id', $tenantId)
            ->where('stock_type', 'trackable')
            ->with(['stock', 'category', 'unit'])
            ->get();

        $recommendations = $products->map(function ($product) use ($salesData, $days) {
            $currentStock = $product->stock ? $product->stock->current_stock : 0;
            $dailyAvgSales = ($salesData[$product->id] ?? 0) / $days;

            // ROP = (Daily Avg Sales * Lead Time) + Safety Stock
            $rop = ($dailyAvgSales * $product->lead_time) + $product->safety_stock;

            // Standard Minimum Stock fallback (if ROP is very low or 0)
            $effectiveRop = max($rop, $product->minimum_stock);

            $needsReorder = $currentStock <= $effectiveRop;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'unit' => $product->unit?->name,
                'current_stock' => (float) $currentStock,
                'daily_avg_sales' => round($dailyAvgSales, 2),
                'lead_time' => $product->lead_time,
                'safety_stock' => (float) $product->safety_stock,
                'calculated_rop' => round($effectiveRop, 2),
                'reorder_quantity' => (float) $product->reorder_quantity,
                'needs_reorder' => $needsReorder,
                'priority' => $needsReorder ? ($currentStock <= $product->safety_stock ? 'high' : 'medium') : 'low',
            ];
        })->filter(fn ($item) => $item['needs_reorder'])->values();

        return $this->successResponse($recommendations);
    }

    /**
     * Get inventory alerts (Overstock).
     */
    public function alerts(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $overstockProducts = Product::where('tenant_id', $tenantId)
            ->where('stock_type', 'trackable')
            ->where('maximum_stock', '>', 0)
            ->whereHas('stock', function ($q) {
                $q->whereColumn('current_stock', '>=', 'products.maximum_stock');
            })
            ->with(['stock', 'category', 'unit'])
            ->get()
            ->map(function ($product) {
                $currentStock = $product->stock->current_stock;
                $excess = $currentStock - $product->maximum_stock;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'unit' => $product->unit?->name,
                    'current_stock' => (float) $currentStock,
                    'maximum_stock' => (float) $product->maximum_stock,
                    'excess_quantity' => (float) $excess,
                    'excess_percentage' => round(($excess / $product->maximum_stock) * 100, 2),
                    'severity' => $excess / $product->maximum_stock > 0.5 ? 'critical' : 'warning',
                ];
            });

        return $this->successResponse($overstockProducts);
    }
}
