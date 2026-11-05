<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DpkadSyncService
{
    /**
     * Sync multiple orders to DPKAD database.
     *
     * @param  array<int>  $orderIds
     * @return array<string, int>
     */
    public function syncOrders(array $orderIds, bool $force = false): array
    {
        // Pastikan database dpkad siap
        $this->ensureDpkadTablesExist();

        /** @var Collection<int, Order> $orders */
        $orders = Order::with('items')->whereIn('id', $orderIds)->get();
        $syncedCount = 0;

        foreach ($orders as $order) {
            /** @var Order $order */
            // Lewati jika sudah pernah disinkronkan di DB lokal kecuali jika $force = true
            if ($order->is_synced_to_dpkad && ! $force) {
                continue;
            }

            DB::transaction(function () use ($order, &$syncedCount) {
                /** @var Order $order */
                // Cek apakah sudah ada di database dpkad (Idempotency)
                $exists = DB::connection('dpkad')->table('orders')
                    ->where('external_order_id', $order->id)
                    ->first();

                if (! $exists) {
                    // Simpan ke database dpkad
                    $dpkadOrderId = DB::connection('dpkad')->table('orders')->insertGetId([
                        'external_order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer_name,
                        'subtotal' => $order->subtotal,
                        'tax_amount' => $order->tax_amount,
                        'service_charge' => $order->service_charge,
                        'total_amount' => $order->total_amount,
                        'payment_method' => $order->payment_method,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ]);

                    foreach ($order->items as $item) {
                        DB::connection('dpkad')->table('order_items')->insert([
                            'order_id' => $dpkadOrderId,
                            'product_name' => $item->product_name,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                        ]);
                    }
                } else {
                    // Update order di database dpkad jika ada perubahan / force sync
                    DB::connection('dpkad')->table('orders')
                        ->where('id', $exists->id)
                        ->update([
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer_name,
                            'subtotal' => $order->subtotal,
                            'tax_amount' => $order->tax_amount,
                            'service_charge' => $order->service_charge,
                            'total_amount' => $order->total_amount,
                            'payment_method' => $order->payment_method,
                            'updated_at' => now(),
                        ]);

                    // Hapus item lama dan ganti dengan items terbaru
                    DB::connection('dpkad')->table('order_items')
                        ->where('order_id', $exists->id)
                        ->delete();

                    foreach ($order->items as $item) {
                        DB::connection('dpkad')->table('order_items')->insert([
                            'order_id' => $exists->id,
                            'product_name' => $item->product_name,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                            'created_at' => $item->created_at,
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Update status di database utama
                $order->update([
                    'is_synced_to_dpkad' => true,
                    'synced_to_dpkad_at' => now(),
                ]);

                $syncedCount++;
            });
        }

        return [
            'synced_count' => $syncedCount,
        ];
    }

    /**
     * Ensure DPKAD tables exist on the remote connection.
     */
    public function ensureDpkadTablesExist(): void
    {
        if (! Schema::connection('dpkad')->hasTable('orders')) {
            Schema::connection('dpkad')->create('orders', function ($table) {
                $table->id();
                $table->unsignedBigInteger('external_order_id')->unique();
                $table->string('order_number');
                $table->string('customer_name')->nullable();
                $table->decimal('subtotal', 15, 2);
                $table->decimal('tax_amount', 15, 2);
                $table->decimal('service_charge', 15, 2);
                $table->decimal('total_amount', 15, 2);
                $table->string('payment_method');
                $table->timestamps();
            });
        }

        if (! Schema::connection('dpkad')->hasTable('order_items')) {
            Schema::connection('dpkad')->create('order_items', function ($table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('product_name');
                $table->decimal('price', 15, 2);
                $table->integer('quantity');
                $table->decimal('subtotal', 15, 2);
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }
    }
}
