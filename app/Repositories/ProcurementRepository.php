<?php

namespace App\Repositories;

use App\Interfaces\ProcurementRepositoryInterface;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcurementRepository implements ProcurementRepositoryInterface
{
    public function getRecommendations(int $tenantId, int $days)
    {
        $salesData = OrderItem::whereHas('order', function ($q) use ($tenantId, $days) {
            $q->where('tenant_id', $tenantId)
                ->where('created_at', '>=', Carbon::now()->subDays($days));
        })
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->get()
            ->pluck('total_qty', 'product_id');

        $products = Product::where('tenant_id', $tenantId)
            ->where('stock_type', 'trackable')
            ->with(['stock', 'category', 'unit'])
            ->get();

        return $products->map(function ($product) use ($salesData, $days) {
            $currentStock = $product->stock ? $product->stock->current_stock : 0;
            $dailyAvgSales = ($salesData[$product->id] ?? 0) / $days;

            $rop = ($dailyAvgSales * $product->lead_time) + $product->safety_stock;
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
    }

    public function getAlerts(int $tenantId)
    {
        return Product::where('tenant_id', $tenantId)
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
    }
}
