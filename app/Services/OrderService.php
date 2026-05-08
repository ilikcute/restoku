<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Tipe order yang valid.
     */
    public const ORDER_TYPES = ['regular', 'ojol', 'wholesale'];

    /**
     * Pembulatan ke atas ke kelipatan 1000 terdekat.
     * Misal: 12.500 → 13.000 (rounding = 500 tidak, harus ke 1000).
     * Misal: 12.001 → 13.000 (rounding = 999).
     */
    public static function calculateRounding(float $amount): float
    {
        $remainder = (int) $amount % 1000;

        return $remainder > 0 ? 1000 - $remainder : 0;
    }

    /**
     * Dapatkan harga jual dan diskon produk berdasarkan tipe order.
     *
     * @return array{price: float, discount: float}
     */
    public static function resolveProductPrice(Product $product, string $orderType): array
    {
        $price = $product->price;
        $discount = $product->discount_amount;

        if ($orderType === 'ojol') {
            $price = $product->ojol_price > 0 ? $product->ojol_price : $product->price;
            $discount = $product->ojol_discount;
        } elseif ($orderType === 'wholesale') {
            $price = $product->wholesale_price > 0 ? $product->wholesale_price : $product->price;
            $discount = $product->wholesale_discount;
        }

        return [
            'price' => (float) $price,
            'discount' => (float) $discount,
        ];
    }

    /**
     * Hitung total keseluruhan dari daftar item order.
     *
     * Mengembalikan breakdown lengkap yang digunakan oleh OrderController::store()
     * maupun frontend (via API response) untuk memastikan konsistensi.
     *
     * @param  array<int, array{product_id: int, quantity: float, notes?: string}>  $items
     * @return array{
     *   items: array<int, array{
     *     product_id: int, product_name: string, quantity: float,
     *     notes: string|null, price: float, cost_price: float,
     *     discount_amount: float, tax_amount: float, subtotal: float
     *   }>,
     *   subtotal: float,
     *   discount_total: float,
     *   tax_total: float,
     *   service_total: float,
     *   grand_total_before_rounding: float,
     *   rounding: float,
     *   grand_total: float,
     * }
     */
    public function calculateOrderTotals(array $items, string $orderType = 'regular'): array
    {
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $serviceTotal = 0.0;
        $processedItems = [];

        // Ambil semua produk sekaligus — cegah N+1
        $productIds = array_column($items, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                continue;
            }

            ['price' => $price, 'discount' => $discount] = self::resolveProductPrice($product, $orderType);

            $itemSubtotal = ($price - $discount) * $item['quantity'];
            $itemTax = $itemSubtotal * ($product->tax_rate / 100);
            $itemService = $itemSubtotal * ($product->service_charge_rate / 100);

            $subtotal += $itemSubtotal;
            $discountTotal += $discount * $item['quantity'];
            $taxTotal += $itemTax;
            $serviceTotal += $itemService;

            $processedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'notes' => $item['notes'] ?? null,
                'price' => $price,
                'cost_price' => (float) $product->cost_price,
                'discount_amount' => $discount,
                'tax_amount' => $itemTax,
                'subtotal' => $itemSubtotal,
            ];
        }

        $grandTotalBeforeRounding = $subtotal + $taxTotal + $serviceTotal;
        $rounding = self::calculateRounding($grandTotalBeforeRounding);
        $grandTotal = $grandTotalBeforeRounding + $rounding;

        return [
            'items' => $processedItems,
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'service_total' => $serviceTotal,
            'grand_total_before_rounding' => $grandTotalBeforeRounding,
            'rounding' => $rounding,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Generate nomor order unik format: ORD-YYMMDD-XXXX.
     * Menggunakan lockForUpdate() dalam transaksi DB yang aktif.
     */
    public function generateOrderNumber(int $tenantId): string
    {
        $datePrefix = 'ORD-'.date('ymd').'-';

        $lastOrder = Order::where('order_number', 'LIKE', $datePrefix.'%')
            ->where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;
        if ($lastOrder) {
            $lastNumber = (int) str_replace($datePrefix, '', $lastOrder->order_number);
            $nextNumber = $lastNumber + 1;
        }

        return $datePrefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
