<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Finance\Transaction\StoreFinancialTransactionRequest;
use App\Http\Resources\Api\Finance\AccountResource;
use App\Http\Resources\Api\Finance\CategoryResource;
use App\Http\Resources\Api\Finance\TransactionResource;
use App\Interfaces\FinancialRepositoryInterface;
use App\Models\Account;
use Illuminate\Http\Request;

class FinancialController extends BaseApiController
{
    protected FinancialRepositoryInterface $financialRepository;

    public function __construct(FinancialRepositoryInterface $financialRepository)
    {
        $this->financialRepository = $financialRepository;
    }

    public function accounts(Request $request)
    {
        $accounts = $this->financialRepository->getAccounts($request->user()->tenant_id);

        return $this->successResponse(AccountResource::collection($accounts));
    }

    public function transactions(Request $request)
    {
        $transactions = $this->financialRepository->getTransactions($request->user()->tenant_id, 20);

        return $this->successResponse(TransactionResource::collection($transactions)->response()->getData(true));
    }

    public function storeTransaction(StoreFinancialTransactionRequest $request)
    {
        $transaction = $this->financialRepository->storeTransaction(
            $request->user()->tenant_id,
            $request->user()->id,
            $request->validated()
        );

        return $this->successResponse(new TransactionResource($transaction->load(['account', 'user', 'expenseCategory', 'incomeCategory'])), 'Transaksi berhasil dicatat.', 201);
    }

    public function expenseCategories(Request $request)
    {
        $categories = $this->financialRepository->getExpenseCategories($request->user()->tenant_id);

        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function incomeCategories(Request $request)
    {
        $categories = $this->financialRepository->getIncomeCategories($request->user()->tenant_id);

        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function storeAccount(\App\Http\Requests\Api\Finance\Account\StoreAccountRequest $request)
    {
        $account = $this->financialRepository->storeAccount($request->user()->tenant_id, $request->validated());

        return $this->successResponse(new AccountResource($account), 'Akun berhasil dibuat.', 201);
    }

    public function updateAccount(\App\Http\Requests\Api\Finance\Account\UpdateAccountRequest $request, Account $account)
    {
        $this->authorizeTenant($account);

        $account = $this->financialRepository->updateAccount($account, $request->validated());

        return $this->successResponse(new AccountResource($account), 'Akun berhasil diperbarui.');
    }

    public function destroyAccount(Account $account)
    {
        $this->authorizeTenant($account);

        try {
            $this->financialRepository->destroyAccount($account);

            return $this->successResponse(null, 'Akun berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
