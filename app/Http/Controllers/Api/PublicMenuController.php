<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Public\StorePublicOrderRequest;
use App\Http\Resources\Api\Master\ProductResource;
use App\Models\PendingOrder;
use App\Models\Product;
use Illuminate\Support\Str;

class PublicMenuController extends BaseApiController
{
    /**
     * Get list of products for public menu
     */
    public function products()
    {
        try {
            $products = Product::with(['category', 'unit'])
                ->where('is_active', true)
                ->get();

            return $this->successResponse(ProductResource::collection($products));
        } catch (\Exception $e) {
            \Log::error('Public Menu Error: '.$e->getMessage());

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a self-order draft from customer
     */
    public function storeOrder(StorePublicOrderRequest $request)
    {
        $validated = $request->validated();
        $token = 'PO-'.strtoupper(Str::random(6));

        $pendingOrder = PendingOrder::create([
            'token' => $token,
            'tenant_id' => $validated['tenant_id'],
            'customer_name' => $validated['customer_name'] ?? null,
            'table_number' => $validated['table_number'] ?? null,
            'items' => $validated['items'],
            'status' => 'pending',
        ]);

        return $this->successResponse([
            'token' => $token,
            'id' => $pendingOrder->id,
        ], 'Pesanan berhasil dikirim.');
    }

    /**
     * Fetch pending order data (to be used by POS)
     */
    public function fetchOrder($token)
    {
        $order = PendingOrder::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        // Enrich items with product details
        $items = collect($order->items)->map(function ($item) {
            $product = Product::with(['category', 'unit'])
                ->find($item['product_id']);

            if ($product) {
                $productData = (new ProductResource($product))->resolve();

                return array_merge($productData, [
                    'qty' => $item['quantity'],
                    'notes' => $item['notes'] ?? '',
                ]);
            }

            return $item;
        });

        $data = $order->toArray();
        $data['items'] = $items;

        return $this->successResponse($data);
    }
}
