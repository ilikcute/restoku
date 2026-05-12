<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
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
    public function calculateOrderTotals(array $items, string $orderType = 'regular', ?int $tenantId = null): array
    {
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $serviceTotal = 0.0;
        $processedItems = [];

        // Ambil semua produk sekaligus — cegah N+1
        $productIds = array_column($items, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Ambil promosi aktif jika ada tenantId
        $promotions = [];
        if ($tenantId) {
            $promotions = Promotion::where('tenant_id', $tenantId)
                ->active()
                ->with(['products', 'categories'])
                ->get();
        }

        // Hitung base subtotal terlebih dahulu untuk pengecekan min_purchase
        $baseSubtotal = 0.0;
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if ($product) {
                ['price' => $price] = self::resolveProductPrice($product, $orderType);
                $baseSubtotal += $price * $item['quantity'];
            }
        }

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                continue;
            }

            ['price' => $price, 'discount' => $discount] = self::resolveProductPrice($product, $orderType);

            // Hitung Promosi (Pilih yang terbaik berdasarkan TOTAL POTONGAN BARIS)
            $nonStackablePromos = [];
            $stackablePromosApplied = [];
            $stackableDiscountTotal = 0;

            foreach ($promotions as $promo) {
                if ($promo->type === 'announcement') {
                    continue;
                }
                if ($promo->min_purchase > 0 && $baseSubtotal < $promo->min_purchase) {
                    continue;
                }

                $isApplicable = false;
                if ($promo->applicable_type === 'all') {
                    $isApplicable = true;
                } elseif ($promo->applicable_type === 'products' && $promo->products->contains('id', $product->id)) {
                    $isApplicable = true;
                } elseif ($promo->applicable_type === 'categories' && $promo->categories->contains('id', $product->category_id)) {
                    $isApplicable = true;
                }

                if ($isApplicable) {
                    $totalVal = 0;
                    if ($promo->type === 'discount_percentage') {
                        $totalVal = ($price * ($promo->discount_value / 100)) * $item['quantity'];
                    } elseif ($promo->type === 'discount_fixed') {
                        $totalVal = $promo->is_multiple
                            ? ($promo->discount_value * $item['quantity'])
                            : (float) $promo->discount_value;
                    }

                    if ($promo->is_stackable) {
                        $stackablePromosApplied[] = ['id' => $promo->id, 'amount' => $totalVal];
                        $stackableDiscountTotal += $totalVal;
                    } else {
                        $nonStackablePromos[] = ['id' => $promo->id, 'value' => $totalVal];
                    }
                }
            }

            // Cari diskon terbaik dari non-stackable (Bandingkan nilai TOTAL)
            $bestNonStackable = ['id' => null, 'value' => 0];
            foreach ($nonStackablePromos as $p) {
                if ($p['value'] > $bestNonStackable['value']) {
                    $bestNonStackable = $p;
                }
            }

            $linePromotionDiscount = $bestNonStackable['value'] + $stackableDiscountTotal;

            // Kumpulkan semua ID promo yang diaplikasikan untuk audit
            $appliedPromotions = [];
            if ($bestNonStackable['id']) {
                $appliedPromotions[] = [
                    'promotion_id' => $bestNonStackable['id'],
                    'discount_amount' => $bestNonStackable['value'],
                ];
            }
            foreach ($stackablePromosApplied as $sp) {
                $appliedPromotions[] = [
                    'promotion_id' => $sp['id'],
                    'discount_amount' => $sp['amount'],
                ];
            }

            // TOTAL DISKON UNTUK SELURUH BARIS INI
            $unitProductDiscount = $discount;
            $lineGrossTotal = $price * $item['quantity'];
            $lineTotalDiscount = ($unitProductDiscount * $item['quantity']) + $linePromotionDiscount;
            $lineTotalDiscount = min($lineGrossTotal, $lineTotalDiscount); // Cap agar tidak melebihi harga

            $itemSubtotal = $lineGrossTotal - $lineTotalDiscount;
            $itemTax = floor($itemSubtotal * ($product->tax_rate / 100));
            $itemService = floor($itemSubtotal * ($product->service_charge_rate / 100));

            $subtotal += $itemSubtotal;
            $discountTotal += $lineTotalDiscount;
            $taxTotal += (float) $itemTax;
            $serviceTotal += (float) $itemService;

            $processedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'notes' => $item['notes'] ?? null,
                'price' => $price,
                'cost_price' => (float) $product->cost_price,
                'discount_amount' => $lineTotalDiscount,
                'tax_amount' => $itemTax,
                'service_charge' => $itemService,
                'subtotal' => $itemSubtotal,
                'applied_promotions' => $appliedPromotions,
            ];
        }

        // --- GLOBAL PROMOTIONS (Cart-based) ---
        // Misal: Diskon tambahan jika total belanja > min_purchase
        // (Untuk saat ini kita fokus ke item-based dulu agar tidak tumpang tindih kompleks)

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
            'promotion_id' => $processedItems[0]['promotion_id'] ?? null, // Simpan jika ada (sementara ambil item pertama jika global)
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
