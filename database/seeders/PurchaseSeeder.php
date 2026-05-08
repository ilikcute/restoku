<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(InventoryService $inventoryService): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        $admin = User::where('email', 'admin@restoku.id')->first();
        $products = Product::where('tenant_id', $tenant->id)->get();
        $suppliers = Supplier::where('tenant_id', $tenant->id)->get();
        $accounts = Account::where('tenant_id', $tenant->id)->get();

        for ($i = 0; $i < 55; $i++) {
            $purchaseDate = now()->subDays(60 - $i);
            $purchase = Purchase::create([
                'tenant_id' => $tenant->id,
                'supplier_id' => $suppliers->random()->id,
                'user_id' => $admin->id,
                'purchase_number' => 'PUR-MASS-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'purchase_date' => $purchaseDate,
                'subtotal' => 0,
                'total_amount' => 0,
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);

            $subtotal = 0;
            foreach ($products->random(2) as $p) {
                $qty = rand(10, 50);
                $sub = $p->cost_price * $qty;
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'cost_price' => $p->cost_price,
                    'quantity' => $qty,
                    'subtotal' => $sub,
                ]);
                $subtotal += $sub;
            }

            $purchase->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
            ]);

            $inventoryService->adjustStockFromPurchase($purchase, $admin->id, $accounts->random()->id);
        }
    }
}
