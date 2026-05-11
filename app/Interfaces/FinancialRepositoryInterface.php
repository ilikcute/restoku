<?php

namespace App\Interfaces;

use App\Models\Account;

interface FinancialRepositoryInterface
{
    public function getAccounts(int $tenantId);

    public function getTransactions(int $tenantId, int $perPage = 20);

    public function storeTransaction(int $tenantId, int $userId, array $validated);

    public function getExpenseCategories(int $tenantId);

    public function getIncomeCategories(int $tenantId);

    public function storeAccount(int $tenantId, array $validated);

    public function updateAccount(Account $account, array $validated);

    public function destroyAccount(Account $account);
}
