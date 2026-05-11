<?php

namespace App\Repositories;

use App\Http\Resources\Api\Master\ProductResource;
use App\Interfaces\PublicMenuRepositoryInterface;
use App\Models\PendingOrder;
use App\Models\Product;
use Illuminate\Support\Str;

class PublicMenuRepository implements PublicMenuRepositoryInterface
{
    public function getActiveProducts()
    {
        return Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->get();
    }

    public function createPendingOrder(array $validated): array
    {
        $token = 'PO-'.strtoupper(Str::random(6));

        $pendingOrder = PendingOrder::create([
            'token' => $token,
            'tenant_id' => $validated['tenant_id'],
            'customer_name' => $validated['customer_name'] ?? null,
            'table_number' => $validated['table_number'] ?? null,
            'items' => $validated['items'],
            'status' => 'pending',
        ]);

        return [
            'token' => $token,
            'id' => $pendingOrder->id,
        ];
    }

    public function getPendingOrderByToken(string $token): array
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

        return $data;
    }
}
