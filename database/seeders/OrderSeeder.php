<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(InventoryService $inventoryService): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        $cashier = User::where('email', 'cashier@restoku.id')->first();
        $shifts = Shift::where('tenant_id', $tenant->id)->get();
        $products = Product::where('tenant_id', $tenant->id)->get();
        $customers = Customer::where('tenant_id', $tenant->id)->get();
        $accounts = Account::where('tenant_id', $tenant->id)->get();

        for ($i = 0; $i < 55; $i++) {
            $shift = $shifts[$i % $shifts->count()];
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'shift_id' => $shift->id,
                'user_id' => $cashier->id,
                'customer_id' => $customers->random()->id,
                'order_number' => 'ORD-MASS-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'customer_name' => $customers->random()->name,
                'subtotal' => 0,
                'total_amount' => 0,
                'payment_method' => 'cash',
                'status' => 'completed',
                'created_at' => $shift->start_time,
            ]);

            $subtotal = 0;
            foreach ($products->random(3) as $p) {
                $qty = rand(1, 5);
                $sub = $p->price * $qty;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'price' => $p->price,
                    'cost_price' => $p->cost_price,
                    'quantity' => $qty,
                    'subtotal' => $sub,
                    'tax_amount' => $sub * 0.11,
                ]);
                $subtotal += $sub;
            }

            $tax = $subtotal * 0.11;
            $service = $subtotal * 0.05;
            $total = $subtotal + $tax + $service;

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'service_charge' => $service,
                'total_amount' => $total,
                'paid_amount' => $total,
            ]);

            $inventoryService->adjustStockFromOrder($order, $cashier->id, $accounts->random()->id);
        }
    }
}
