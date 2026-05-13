<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Inventory\StoreStockAdjustmentRequest;
use App\Http\Resources\Api\Inventory\ProductStockResource;
use App\Http\Resources\Api\Inventory\StockAdjustmentResource;
use App\Http\Resources\Api\Inventory\StockMovementResource;
use App\Interfaces\InventoryRepositoryInterface;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;

class InventoryController extends BaseApiController
{
    protected InventoryRepositoryInterface $inventoryRepository;

    public function __construct(InventoryRepositoryInterface $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * Display real-time stock levels.
     */
    public function stockLevels(Request $request)
    {
        \Log::info('Fetching stock levels', [
            'tenant_id' => $request->user()->tenant_id,
            'category_id' => $request->category_id
        ]);

        try {
            $products = $this->inventoryRepository->getStockLevels(
                $request->user()->tenant_id,
                $request->category_id,
                $request->q
            );

            \Log::info('Products fetched', ['count' => $products->count()]);

            $totalStock = $products->sum(function ($p) {
                return $p->stock ? $p->stock->current_stock : 0;
            });

            return $this->successResponse([
                'data' => ProductStockResource::collection($products),
                'total_stock' => $totalStock,
            ]);
        } catch (\Exception $e) {
            \Log::error('Stock levels error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Display comprehensive stock movement audit report.
     */
    public function movements(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));

        $results = $this->inventoryRepository->getMovements(
            $tenantId,
            $startDate,
            $endDate,
            $request->category_id,
            $request->q
        );

        return $this->successResponse([
            'period' => ['start' => $startDate, 'end' => $endDate],
            'items' => $results,
        ]);
    }

    /**
     * Display detailed movement log for a specific product.
     */
    public function movementDetail(Request $request, Product $product)
    {
        $this->authorizeTenant($product);

        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfDay()->format('Y-m-d'));

        $movements = $this->inventoryRepository->getMovementDetail(
            $request->user()->tenant_id,
            $product->id,
            $startDate,
            $endDate
        );

        return $this->successResponse([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ],
            'movements' => StockMovementResource::collection($movements),
        ]);
    }

    /**
     * Multi-stage Stock Adjustment (Opname)
     * Statuses: I (Investigation/Draft 1), D (Done/Verify), A (Adjusted/Final)
     */
    public function adjust(StoreStockAdjustmentRequest $request)
    {
        $tenantId = $request->user()->tenant_id;
        $userId = $request->user()->id;

        try {
            $adjustment = $this->inventoryRepository->processAdjustment(
                $tenantId,
                $userId,
                $request->validated()
            );

            return $this->successResponse(
                new StockAdjustmentResource($adjustment),
                $request->status === 'A' ? 'Stock opname finalized successfully' : 'Draft saved successfully',
                isset($request->id) ? 200 : 201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Display adjustment history.
     */
    public function adjustmentHistory(Request $request)
    {
        $adjustments = $this->inventoryRepository->getAdjustmentHistory(
            $request->user()->tenant_id,
            20
        );

        return $this->successResponse(StockAdjustmentResource::collection($adjustments)->response()->getData(true));
    }

    public function showAdjustment(StockAdjustment $adjustment)
    {
        $this->authorizeTenant($adjustment);

        return $this->successResponse(new StockAdjustmentResource($adjustment->load(['user', 'items.product'])));
    }
}
