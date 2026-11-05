<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * @property Tenant $tenant
 * @property User $admin
 * @property Shift $shift
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->admin = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-reports');
    Permission::findOrCreate('view-report-tax');
    $this->admin->givePermissionTo(['view-reports', 'view-report-tax']);

    $this->shift = Shift::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
        'status' => 'open',
    ]);
});

test('can retrieve reconciliation data successfully', function () {
    // Create completed order in Restoku
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'shift_id' => $this->shift->id,
        'user_id' => $this->admin->id,
        'order_number' => 'RECON-TEST-001',
        'customer_name' => 'John Doe',
        'subtotal' => 100000,
        'tax_amount' => 10000,
        'service_charge' => 5000,
        'total_amount' => 115000,
        'paid_amount' => 115000,
        'payment_method' => 'cash',
        'status' => 'completed',
        'created_at' => Carbon::now(),
    ]);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Steak',
        'price' => 100000,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Steak',
        'cost_price' => 50000,
        'price' => 100000,
        'quantity' => 1,
        'subtotal' => 100000,
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/reports/reconciliation?start_date='.now()->format('Y-m-d').'&end_date='.now()->format('Y-m-d'));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'status',
        'data' => [
            'summary' => [
                'is_balanced',
                'orders' => [
                    'restoku_count',
                    'dpkad_count',
                    'diff_count',
                    'matched_count',
                    'mismatched_count',
                    'not_in_dpkad_count',
                    'restoku_total',
                    'dpkad_total',
                    'diff_total',
                ],
                'order_items' => [
                    'restoku_count',
                    'dpkad_count',
                    'diff_count',
                    'restoku_qty',
                    'dpkad_qty',
                ],
            ],
            'orders',
        ],
    ]);

    // Order is in Restoku but not yet in DPKAD -> not_in_dpkad_count is 1
    $response->assertJsonPath('data.summary.orders.not_in_dpkad_count', 1);
    $response->assertJsonPath('data.summary.is_balanced', false);
});

test('can sync un-synced orders from reconciliation endpoint', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'shift_id' => $this->shift->id,
        'user_id' => $this->admin->id,
        'order_number' => 'RECON-SYNC-001',
        'customer_name' => 'Jane Doe',
        'subtotal' => 50000,
        'tax_amount' => 5000,
        'service_charge' => 2500,
        'total_amount' => 57500,
        'paid_amount' => 57500,
        'payment_method' => 'cash',
        'status' => 'completed',
        'created_at' => Carbon::now(),
    ]);

    $product2 = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Burger',
        'price' => 50000,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product2->id,
        'product_name' => 'Burger',
        'cost_price' => 25000,
        'price' => 50000,
        'quantity' => 1,
        'subtotal' => 50000,
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/reports/reconciliation/sync', [
            'order_ids' => [$order->id],
        ]);

    $response->assertSuccessful();
    $response->assertJsonPath('data.synced_count', 1);

    // Verify in DPKAD database
    $dpkadOrder = DB::connection('dpkad')->table('orders')->where('external_order_id', $order->id)->first();
    expect($dpkadOrder)->not->toBeNull()
        ->and((float) $dpkadOrder->total_amount)->toBe(57500.0);

    // Clean up DPKAD test record
    DB::connection('dpkad')->table('order_items')->where('order_id', $dpkadOrder->id)->delete();
    DB::connection('dpkad')->table('orders')->where('id', $dpkadOrder->id)->delete();
});
