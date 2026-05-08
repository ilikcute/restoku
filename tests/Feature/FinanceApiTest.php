<?php

use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    Permission::findOrCreate('view-transactions');
    $this->user->givePermissionTo('view-transactions');
    $this->account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->expenseCategory = ExpenseCategory::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->incomeCategory = IncomeCategory::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('cannot create financial transaction using account from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherAccount = Account::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/finance/transactions', [
            'account_id' => $otherAccount->id,
            'expense_category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 50000,
            'description' => 'Biaya operasional',
            'transaction_date' => now()->toDateString(),
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['account_id']);
});

test('cannot create financial transaction using categories from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherExpenseCategory = ExpenseCategory::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherIncomeCategory = IncomeCategory::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/finance/transactions', [
            'account_id' => $this->account->id,
            'expense_category_id' => $otherExpenseCategory->id,
            'income_category_id' => $otherIncomeCategory->id,
            'type' => 'expense',
            'amount' => 50000,
            'description' => 'Biaya operasional',
            'transaction_date' => now()->toDateString(),
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['expense_category_id', 'income_category_id']);
});
