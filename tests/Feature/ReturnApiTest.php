<?php

use App\Models\Account;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-sales-returns');
    Permission::findOrCreate('view-purchase-returns');
    $this->user->givePermissionTo(['view-sales-returns', 'view-purchase-returns']);
    $this->account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->shift = Shift::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'status' => 'closed',
    ]);
    $this->order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'shift_id' => $this->shift->id,
        'user_id' => $this->user->id,
    ]);
    $this->orderItem = OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
    ]);
    $this->supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->purchase = Purchase::create([
        'tenant_id' => $this->tenant->id,
        'supplier_id' => $this->supplier->id,
        'user_id' => $this->user->id,
        'purchase_number' => 'PUR-TEST-001',
        'purchase_date' => now()->toDateString(),
        'subtotal' => 10000,
        'total_amount' => 10000,
        'payment_status' => 'paid',
        'status' => 'completed',
    ]);
    $this->purchaseItem = PurchaseItem::factory()->create([
        'purchase_id' => $this->purchase->id,
        'product_id' => $this->product->id,
    ]);
});

test('cannot create order return using order from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherOrder = Order::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/returns/orders', [
            'order_id' => $otherOrder->id,
            'account_id' => $this->account->id,
            'items' => [
                [
                    'order_item_id' => $this->orderItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['order_id']);
});

test('cannot create order return using account or item from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherAccount = Account::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherOrder = Order::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherOrderItem = OrderItem::factory()->create(['order_id' => $otherOrder->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/returns/orders', [
            'order_id' => $this->order->id,
            'account_id' => $otherAccount->id,
            'items' => [
                [
                    'order_item_id' => $otherOrderItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['account_id', 'items.0.order_item_id']);
});

test('cannot create purchase return using purchase from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherSupplier = Supplier::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherUser = User::factory()->admin()->create(['tenant_id' => $otherTenant->id]);
    $otherPurchase = Purchase::create([
        'tenant_id' => $otherTenant->id,
        'supplier_id' => $otherSupplier->id,
        'user_id' => $otherUser->id,
        'purchase_number' => 'PUR-OTHER-001',
        'purchase_date' => now()->toDateString(),
        'subtotal' => 10000,
        'total_amount' => 10000,
        'payment_status' => 'paid',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/returns/purchases', [
            'purchase_id' => $otherPurchase->id,
            'account_id' => $this->account->id,
            'items' => [
                [
                    'purchase_item_id' => $this->purchaseItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['purchase_id']);
});

test('cannot create purchase return using account or item from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherAccount = Account::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherSupplier = Supplier::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherUser = User::factory()->admin()->create(['tenant_id' => $otherTenant->id]);
    $otherPurchase = Purchase::create([
        'tenant_id' => $otherTenant->id,
        'supplier_id' => $otherSupplier->id,
        'user_id' => $otherUser->id,
        'purchase_number' => 'PUR-OTHER-002',
        'purchase_date' => now()->toDateString(),
        'subtotal' => 10000,
        'total_amount' => 10000,
        'payment_status' => 'paid',
        'status' => 'completed',
    ]);
    $otherPurchaseItem = PurchaseItem::factory()->create(['purchase_id' => $otherPurchase->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/returns/purchases', [
            'purchase_id' => $this->purchase->id,
            'account_id' => $otherAccount->id,
            'items' => [
                [
                    'purchase_item_id' => $otherPurchaseItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['account_id', 'items.0.purchase_item_id']);
});
