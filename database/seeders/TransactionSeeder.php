<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(InventoryService $inventoryService): void
    {
        $tenant = Tenant::where('slug', 'restoku-pos-demo')->first();
        $admin = User::where('email', 'admin@restoku.id')->first();
        $accounts = Account::where('tenant_id', $tenant->id)->get();
        $expenseCategories = ExpenseCategory::where('tenant_id', $tenant->id)->get();
        $incomeCategories = IncomeCategory::where('tenant_id', $tenant->id)->get();

        for ($i = 0; $i < 55; $i++) {
            $isIncome = rand(0, 1);
            $inventoryService->recordTransaction(
                $tenant->id,
                $accounts->random()->id,
                $admin->id,
                $isIncome ? 'income' : 'expense',
                rand(10000, 500000),
                'Generic transaction '.($i + 1),
                null,
                null,
                ! $isIncome ? $expenseCategories->random()->id : null,
                $isIncome ? $incomeCategories->random()->id : null
            );
        }
    }
}
