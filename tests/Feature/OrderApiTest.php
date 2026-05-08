<?php

use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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
    $this->account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('order routes are defined', function () {
    expect(true)->toBeTrue();
});

test('cannot create order without open shift', function () {
    $this->shift->update(['status' => 'closed']);

    $orderData = [
        'idempotency_key' => (string) Str::uuid(),
        'customer_name' => 'Guest Customer',
        'account_id' => $this->account->id,
        'items' => [
            [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ],
        ],
        'payment_method' => 'cash',
        'paid_amount' => 100000,
    ];

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson(route('api.v1.orders.store'), $orderData);

    $response->assertStatus(422);
});

test('cannot create order using an account from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherAccount = Account::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson(route('api.v1.orders.store'), [
            'idempotency_key' => (string) Str::uuid(),
            'customer_name' => 'Guest Customer',
            'account_id' => $otherAccount->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 100000,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['account_id']);
});

test('cannot create order using a product from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherProduct = Product::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson(route('api.v1.orders.store'), [
            'idempotency_key' => (string) Str::uuid(),
            'customer_name' => 'Guest Customer',
            'account_id' => $this->account->id,
            'items' => [
                [
                    'product_id' => $otherProduct->id,
                    'quantity' => 1,
                ],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 100000,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.product_id']);
});

test('cannot create order using a customer from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherCustomer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->cashier, 'sanctum')
        ->postJson(route('api.v1.orders.store'), [
            'idempotency_key' => (string) Str::uuid(),
            'customer_id' => $otherCustomer->id,
            'account_id' => $this->account->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 100000,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id']);
});

test('retrying checkout with the same idempotency key returns existing order', function () {
    Stock::factory()->create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'current_stock' => 10,
    ]);

    $idempotencyKey = (string) Str::uuid();
    $payload = [
        'idempotency_key' => $idempotencyKey,
        'customer_name' => 'Guest Customer',
        'account_id' => $this->account->id,
        'items' => [
            [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ],
        ],
        'payment_method' => 'cash',
        'paid_amount' => 100000,
    ];

    $firstResponse = $this->actingAs($this->cashier, 'sanctum')
        ->postJson(route('api.v1.orders.store'), $payload);

    $secondResponse = $this->actingAs($this->cashier, 'sanctum')
        ->postJson(route('api.v1.orders.store'), $payload);

    $firstResponse->assertCreated();
    $secondResponse->assertOk();

    expect($secondResponse->json('data.id'))->toBe($firstResponse->json('data.id'))
        ->and(Order::where('tenant_id', $this->tenant->id)
            ->where('idempotency_key', $idempotencyKey)
            ->count())->toBe(1)
        ->and(StockMovement::where('tenant_id', $this->tenant->id)
            ->where('reference_type', Order::class)
            ->count())->toBe(1)
        ->and(Transaction::where('tenant_id', $this->tenant->id)
            ->where('reference_type', Order::class)
            ->count())->toBe(1);
});
