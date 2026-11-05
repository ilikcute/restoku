<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Shift;
use App\Models\TempOrder;
use App\Models\TempOrderItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->cashier = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-orders');
    $this->cashier->givePermissionTo('view-orders');
    $this->shift = Shift::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'status' => 'open',
    ]);
});

test('can retrieve empty staging summary when no data is imported', function () {
    $response = $this->actingAs($this->cashier, 'sanctum')
        ->getJson('/api/v1/orders/import/summary');

    $response->assertSuccessful();
    $response->assertJson([
        'status' => 'success',
        'data' => [
            'total_orders' => 0,
            'total_amount' => 0,
            'dates' => [],
            'orders' => [],
        ],
    ]);
});

test('can retrieve staging summary with temp orders', function () {
    // Create staging data
    $tempOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'TEST-001',
        'table_number' => 'Table 5',
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
        'total_amount' => 11500,
        'date' => '2026-05-28',
    ]);

    TempOrderItem::create([
        'temp_order_id' => $tempOrder->id,
        'product_name' => 'Nasi Goreng',
        'price' => 10000,
        'quantity' => 1,
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->getJson('/api/v1/orders/import/summary');

    $response->assertSuccessful();
    $response->assertJsonPath('data.total_orders', 1);
    $response->assertJsonPath('data.total_amount', '11500.00');
    $response->assertJsonPath('data.dates.0.date', '2026-05-28');
});

test('can commit staging orders to main orders table', function () {
    // Create product
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Nasi Goreng',
        'price' => 10000,
    ]);

    // Create staging data
    $tempOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'TEST-001',
        'table_number' => 'Table 5',
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
        'total_amount' => 11500,
        'date' => '2026-05-28',
    ]);

    TempOrderItem::create([
        'temp_order_id' => $tempOrder->id,
        'product_name' => 'Nasi Goreng',
        'price' => 10000,
        'quantity' => 1,
        'subtotal' => 10000,
        'tax_amount' => 1000,
        'service_charge' => 500,
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import/confirm', [
            'dates' => ['2026-05-28'],
        ]);

    $response->assertSuccessful();
    $response->assertJson([
        'status' => 'success',
        'data' => [
            'committed_count' => 1,
        ],
    ]);

    // Check database
    $this->assertDatabaseHas('orders', [
        'tenant_id' => $this->tenant->id,
        'order_number' => 'TEST-001',
        'total_amount' => 11500,
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_name' => 'Nasi Goreng',
        'quantity' => 1,
    ]);

    // Check temp orders are cleared
    expect(TempOrder::count())->toBe(0)
        ->and(TempOrderItem::count())->toBe(0);
});

test('cannot commit staging orders without open shift', function () {
    $this->shift->update(['status' => 'closed']);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import/confirm', [
            'dates' => ['2026-05-28'],
        ]);

    $response->assertStatus(400);
});

test('staging summary automatically purges empty or 0-amount orders', function () {
    // Order 1: valid
    $validOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'VALID-001',
        'table_number' => '1',
        'subtotal' => 20000,
        'total_amount' => 20000,
        'date' => '2026-05-28',
    ]);
    TempOrderItem::create([
        'temp_order_id' => $validOrder->id,
        'product_name' => 'Kopi',
        'price' => 20000,
        'quantity' => 1,
        'subtotal' => 20000,
    ]);

    // Order 2: 0 amount & without items
    TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'EMPTY-001',
        'table_number' => '1',
        'subtotal' => 0,
        'total_amount' => 0,
        'date' => null,
    ]);

    // Order 3: has items but 0 amount
    $zeroOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'ZERO-001',
        'table_number' => '1',
        'subtotal' => 0,
        'total_amount' => 0,
        'date' => '2026-05-28',
    ]);
    TempOrderItem::create([
        'temp_order_id' => $zeroOrder->id,
        'product_name' => 'Air',
        'price' => 0,
        'quantity' => 1,
        'subtotal' => 0,
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->getJson('/api/v1/orders/import/summary');

    $response->assertSuccessful();
    // Only 1 valid order should remain
    $response->assertJsonPath('data.total_orders', 1);
    $response->assertJsonPath('data.total_amount', '20000.00');

    expect(TempOrder::where('tenant_id', $this->tenant->id)->count())->toBe(1)
        ->and(TempOrder::where('order_number', 'EMPTY-001')->exists())->toBeFalse()
        ->and(TempOrder::where('order_number', 'ZERO-001')->exists())->toBeFalse();
});

test('cannot commit empty or 0-amount orders to main database', function () {
    // Create zero amount temp order
    $tempOrder = TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'ZERO-COMMIT',
        'table_number' => '1',
        'subtotal' => 0,
        'total_amount' => 0,
        'date' => '2026-05-28',
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson('/api/v1/orders/import/confirm', [
            'dates' => ['2026-05-28'],
        ]);

    $response->assertSuccessful();
    $response->assertJsonPath('data.committed_count', 0);

    // Assert main orders table has no 0-amount orders
    expect(Order::where('order_number', 'ZERO-COMMIT')->exists())->toBeFalse()
        ->and(TempOrder::where('order_number', 'ZERO-COMMIT')->exists())->toBeFalse();
});

test('can clear staging records via endpoint', function () {
    TempOrder::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->cashier->id,
        'order_number' => 'TO-DELETE',
        'table_number' => '1',
        'subtotal' => 10000,
        'total_amount' => 10000,
        'date' => '2026-05-28',
    ]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->deleteJson('/api/v1/orders/import/clear');

    $response->assertSuccessful();
    expect(TempOrder::where('tenant_id', $this->tenant->id)->count())->toBe(0);
});
