<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Finance\Transaction\StoreFinancialTransactionRequest;
use App\Http\Resources\Api\Finance\AccountResource;
use App\Http\Resources\Api\Finance\CategoryResource;
use App\Http\Resources\Api\Finance\TransactionResource;
use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\Transaction;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class FinancialController extends BaseApiController
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function accounts(Request $request)
    {
        $accounts = Account::where('tenant_id', $request->user()->tenant_id)->get();

        return $this->successResponse(AccountResource::collection($accounts));
    }

    public function transactions(Request $request)
    {
        $transactions = Transaction::where('tenant_id', $request->user()->tenant_id)
            ->with(['account', 'user', 'expenseCategory', 'incomeCategory', 'reference'])
            ->latest()
            ->paginate(20);

        return $this->successResponse(TransactionResource::collection($transactions)->response()->getData(true));
    }

    public function storeTransaction(StoreFinancialTransactionRequest $request)
    {
        $validated = $request->validated();

        $transaction = $this->inventoryService->recordTransaction(
            $request->user()->tenant_id,
            $validated['account_id'],
            $request->user()->id,
            $validated['type'],
            $validated['amount'],
            $validated['description'],
            null,
            null,
            $validated['expense_category_id'] ?? null,
            $validated['income_category_id'] ?? null
        );

        return $this->successResponse(new TransactionResource($transaction->load(['account', 'user', 'expenseCategory', 'incomeCategory'])), 'Transaksi berhasil dicatat.', 201);
    }

    public function expenseCategories(Request $request)
    {
        $categories = ExpenseCategory::where('tenant_id', $request->user()->tenant_id)->get();

        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function incomeCategories(Request $request)
    {
        $categories = IncomeCategory::where('tenant_id', $request->user()->tenant_id)->get();

        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'balance' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $account = Account::create($validated);

        return $this->successResponse(new AccountResource($account), 'Akun berhasil dibuat.', 201);
    }

    public function updateAccount(Request $request, Account $account)
    {
        $this->authorizeTenant($account);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'balance' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $account->update($validated);

        return $this->successResponse(new AccountResource($account), 'Akun berhasil diperbarui.');
    }

    public function destroyAccount(Account $account)
    {
        $this->authorizeTenant($account);

        // Prevent deletion if account has transactions
        if ($account->transactions()->count() > 0) {
            return $this->errorResponse('Gagal menghapus. Rekening ini memiliki riwayat transaksi.', 422);
        }

        $account->delete();

        return $this->successResponse(null, 'Akun berhasil dihapus.');
    }
}
