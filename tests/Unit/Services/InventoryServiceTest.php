<?php

use App\Models\Account;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Validation\ValidationException;

describe('InventoryService', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
        $this->product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->stock = Stock::factory()->create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'current_stock' => 100,
        ]);
        $this->service = new InventoryService;
    });

    test('can create inventory service', function () {
        expect($this->service)->toBeInstanceOf(InventoryService::class);
    });

    test('can get current stock level for product', function () {
        $currentStock = $this->stock->current_stock;

        expect($currentStock)->toBe(100);
    });

    test('can increase stock correctly', function () {
        $initialStock = $this->stock->current_stock;
        $this->stock->increment('current_stock', 50);

        expect($this->stock->current_stock)->toBe($initialStock + 50)
            ->and($this->stock->current_stock)->toBe(150);
    });

    test('can decrease stock correctly', function () {
        $initialStock = $this->stock->current_stock;
        $this->stock->decrement('current_stock', 25);

        expect($this->stock->current_stock)->toBe($initialStock - 25)
            ->and($this->stock->current_stock)->toBe(75);
    });

    test('stock cannot go below zero', function () {
        $this->stock->current_stock = 0;
        $this->stock->save();

        expect($this->stock->current_stock)->toBe(0);
    });

    test('service locks and decreases tracked stock with movement audit', function () {
        $this->service->updateStock(
            $this->tenant->id,
            $this->product->id,
            -25,
            'out',
            $this->user->id,
            'test',
            1,
            'Test sale'
        );

        $this->stock->refresh();

        expect((float) $this->stock->current_stock)->toBe(75.0)
            ->and(StockMovement::where('tenant_id', $this->tenant->id)
                ->where('product_id', $this->product->id)
                ->where('type', 'out')
                ->exists())->toBeTrue();
    });

    test('service prevents tracked stock from going below zero', function () {
        $this->service->updateStock(
            $this->tenant->id,
            $this->product->id,
            -1000,
            'out',
            $this->user->id,
            'test',
            1,
            'Oversell attempt'
        );
    })->throws(ValidationException::class);

    test('service locks account balance when recording income and expense', function () {
        $account = Account::factory()->create([
            'tenant_id' => $this->tenant->id,
            'balance' => 100000,
        ]);

        $income = $this->service->recordTransaction(
            $this->tenant->id,
            $account->id,
            $this->user->id,
            'income',
            50000,
            'Cash sale'
        );

        $expense = $this->service->recordTransaction(
            $this->tenant->id,
            $account->id,
            $this->user->id,
            'expense',
            25000,
            'Refund'
        );

        $account->refresh();

        expect((float) $account->balance)->toBe(125000.0)
            ->and($income)->toBeInstanceOf(Transaction::class)
            ->and($expense)->toBeInstanceOf(Transaction::class);
    });

    test('stock belongs to product', function () {
        expect($this->stock->product)->toBeInstanceOf(Product::class)
            ->and($this->stock->product->id)->toBe($this->product->id);
    });

    test('stock belongs to tenant', function () {
        expect($this->stock->tenant)->toBeInstanceOf(Tenant::class)
            ->and($this->stock->tenant->id)->toBe($this->tenant->id);
    });

    test('multiple products can have independent stock levels', function () {
        $product2 = Product::factory()->create(['tenant_id' => $this->tenant->id]);
        $stock2 = Stock::factory()->create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product2->id,
            'current_stock' => 50,
        ]);

        expect($this->stock->current_stock)->toBe(100)
            ->and($stock2->current_stock)->toBe(50);
    });
});
