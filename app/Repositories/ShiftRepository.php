<?php

namespace App\Repositories;

use App\Interfaces\ShiftRepositoryInterface;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ShiftRepository implements ShiftRepositoryInterface
{
    public function getAllByTenant(int $tenantId, int $perPage = 20)
    {
        return Shift::where('tenant_id', $tenantId)
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function getById(int $id): Shift
    {
        return Shift::findOrFail($id);
    }

    public function getCurrentShift(int $tenantId, int $userId): ?Shift
    {
        $shift = Shift::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($shift) {
            // Shift dianggap expired jika sudah buka lebih dari 24 jam
            $isExpired = $shift->start_time->diffInHours(now()) > 24;
            $shift->is_expired = $isExpired;
        }

        return $shift;
    }

    public function openShift(int $tenantId, int $userId, array $data): Shift
    {
        // Check if there is already an open shift
        $existingShift = Shift::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            throw new \Exception('Anda masih memiliki Shift yang terbuka.');
        }

        return Shift::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'start_time' => now(),
            'starting_cash' => $data['starting_cash'],
            'status' => 'open',
        ]);
    }

    public function closeShift(int $tenantId, int $userId, array $data): Shift
    {
        return DB::transaction(function () use ($tenantId, $userId, $data) {
            $shift = Shift::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('status', 'open')
                ->firstOrFail();

            // 1. Calculate Sales
            $cashSales = $shift->orders()->where('payment_method', 'cash')->sum('total_amount');
            $nonCashSales = $shift->orders()->where('payment_method', '!=', 'cash')->sum('total_amount');
            $totalSales = $cashSales + $nonCashSales;

            // 2. Calculate Manual Financial Transactions (Income/Expense)
            $totalIncome = Transaction::where('shift_id', $shift->id)
                ->where('type', 'income')
                ->where(function ($q) {
                    $q->whereNull('reference_type')
                        ->orWhere('reference_type', '!=', 'App\Models\Order');
                })
                ->sum('amount');

            $totalExpense = Transaction::where('shift_id', $shift->id)
                ->where('type', 'expense')
                ->where(function ($q) {
                    $q->whereNull('reference_type')
                        ->orWhere('reference_type', '!=', 'App\Models\Order');
                })
                ->sum('amount');

            // 3. Calculate Returns, Tax, and Service from Orders
            $totalReturn = $shift->orders()->sum('total_return');
            $cashReturns = $shift->orders()->where('payment_method', 'cash')->sum('total_return');
            $totalTax = $shift->orders()->sum('tax_amount');
            $totalService = $shift->orders()->sum('service_charge');

            // 4. Calculate Expected Cash
            $totalExpected = $shift->starting_cash + $cashSales - $cashReturns + $totalIncome - $totalExpense;
            $difference = $data['ending_cash'] - $totalExpected;

            $shift->update([
                'end_time' => now(),
                'total_sales' => $totalSales,
                'cash_sales' => $cashSales,
                'non_cash_sales' => $nonCashSales,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'total_return' => $totalReturn,
                'total_tax' => $totalTax,
                'total_service' => $totalService,
                'ending_cash' => $data['ending_cash'],
                'total_expected' => $totalExpected,
                'difference' => $difference,
                'status' => 'closed',
                'notes' => $data['notes'] ?? null,
            ]);

            return $shift;
        });
    }
}
