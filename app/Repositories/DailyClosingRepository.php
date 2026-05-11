<?php

namespace App\Repositories;

use App\Interfaces\DailyClosingRepositoryInterface;
use App\Models\DailyClosing;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class DailyClosingRepository implements DailyClosingRepositoryInterface
{
    public function getAllByTenant(int $tenantId, int $perPage = 20)
    {
        return DailyClosing::where('tenant_id', $tenantId)
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function processClosing(int $tenantId, int $userId, array $validated)
    {
        return DB::transaction(function () use ($tenantId, $userId, $validated) {
            $closingDate = $validated['closing_date'];

            // Validate: All shifts for this tenant on this date must be closed
            $openShifts = Shift::where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->count();

            if ($openShifts > 0) {
                throw new \Exception("Masih ada $openShifts Shift yang belum ditutup. Tutup semua Shift sebelum melakukan Closing Harian.");
            }

            // Calculate totals for the day from closed Shifts
            $shiftQuery = Shift::where('tenant_id', $tenantId)
                ->whereDate('start_time', $closingDate)
                ->where('status', 'closed');

            $totalRevenue = $shiftQuery->sum('total_sales');
            $totalTax = $shiftQuery->sum('total_tax');
            $totalDiscounts = 0;
            $totalTransactions = $shiftQuery->sum(DB::raw('(SELECT COUNT(*) FROM orders WHERE orders.shift_id = shifts.id AND status = "completed")'));

            $totalIncome = $shiftQuery->sum('total_income');
            $totalExpense = $shiftQuery->sum('total_expense');

            // Net Revenue = Sales + Additional Income - Expenses
            $netRevenue = $totalRevenue + $totalIncome - $totalExpense;

            return DailyClosing::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'closing_date' => $closingDate,
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
                'total_discounts' => $totalDiscounts,
                'total_tax' => $totalTax,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_revenue' => $netRevenue,
                'notes' => $validated['notes'] ?? null,
            ]);
        });
    }

    public function getClosingById(int $id)
    {
        return DailyClosing::with(['user', 'tenant'])->findOrFail($id);
    }
}
