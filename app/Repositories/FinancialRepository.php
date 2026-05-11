<?php

namespace App\Repositories;

use App\Interfaces\FinancialRepositoryInterface;
use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\Transaction;
use App\Services\InventoryService;

class FinancialRepository implements FinancialRepositoryInterface
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function getAccounts(int $tenantId)
    {
        return Account::where('tenant_id', $tenantId)->get();
    }

    public function getTransactions(int $tenantId, int $perPage = 20)
    {
        return Transaction::where('tenant_id', $tenantId)
            ->with(['account', 'user', 'expenseCategory', 'incomeCategory', 'reference'])
            ->latest()
            ->paginate($perPage);
    }

    public function storeTransaction(int $tenantId, int $userId, array $validated)
    {
        return $this->inventoryService->recordTransaction(
            $tenantId,
            $validated['account_id'],
            $userId,
            $validated['type'],
            $validated['amount'],
            $validated['description'],
            null,
            null,
            $validated['expense_category_id'] ?? null,
            $validated['income_category_id'] ?? null
        );
    }

    public function getExpenseCategories(int $tenantId)
    {
        return ExpenseCategory::where('tenant_id', $tenantId)->get();
    }

    public function getIncomeCategories(int $tenantId)
    {
        return IncomeCategory::where('tenant_id', $tenantId)->get();
    }

    public function storeAccount(int $tenantId, array $validated)
    {
        $validated['tenant_id'] = $tenantId;

        return Account::create($validated);
    }

    public function updateAccount(Account $account, array $validated)
    {
        $account->update($validated);

        return $account;
    }

    public function destroyAccount(Account $account)
    {
        if ($account->transactions()->count() > 0) {
            throw new \Exception('Gagal menghapus. Rekening ini memiliki riwayat transaksi.');
        }

        $account->delete();

        return true;
    }
}
