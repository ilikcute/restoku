<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Finance\Closing\StoreDailyClosingRequest;
use App\Http\Resources\Api\Finance\DailyClosingResource;
use App\Models\DailyClosing;
use App\Models\Shift;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyClosingController extends BaseApiController
{
    public function downloadReport($id)
    {
        $closing = DailyClosing::with(['user', 'tenant'])->findOrFail($id);
        $this->authorizeTenant($closing);

        $pdf = Pdf::loadView('reports.daily_closing_pdf', [
            'closing' => $closing,
            'tenant' => $closing->tenant,
        ]);

        return $pdf->download('Laporan_Closing_Harian_'.$closing->closing_date.'.pdf');
    }

    public function index(Request $request)
    {
        $closings = DailyClosing::where('tenant_id', $request->user()->tenant_id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return $this->successResponse(DailyClosingResource::collection($closings)->response()->getData(true));
    }

    public function store(StoreDailyClosingRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            $tenantId = $request->user()->tenant_id;
            $closingDate = $validated['closing_date'];

            // Validate: All shifts for this tenant on this date must be closed
            $openShifts = Shift::where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->count();

            if ($openShifts > 0) {
                return $this->errorResponse("Masih ada $openShifts Shift yang belum ditutup. Tutup semua Shift sebelum melakukan Closing Harian.", 422);
            }

            // Calculate totals for the day from closed Shifts
            $shiftQuery = Shift::where('tenant_id', $tenantId)
                ->whereDate('start_time', $closingDate)
                ->where('status', 'closed');

            $totalRevenue = $shiftQuery->sum('total_sales');
            $totalTax = $shiftQuery->sum('total_tax');
            $totalDiscounts = 0; // If shift doesn't store discounts, we might need a join or keep as 0
            $totalTransactions = $shiftQuery->sum(DB::raw('(SELECT COUNT(*) FROM orders WHERE orders.shift_id = shifts.id AND status = "completed")'));

            $totalIncome = $shiftQuery->sum('total_income');
            $totalExpense = $shiftQuery->sum('total_expense');

            // Net Revenue = Sales + Additional Income - Expenses
            $netRevenue = $totalRevenue + $totalIncome - $totalExpense;

            $closing = DailyClosing::create([
                'tenant_id' => $tenantId,
                'user_id' => $request->user()->id,
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

            return $this->successResponse(new DailyClosingResource($closing), 'Closing harian berhasil diselesaikan.', 201);
        });
    }

    public function show(DailyClosing $dailyClosing)
    {
        $this->authorizeTenant($dailyClosing);

        return $this->successResponse(new DailyClosingResource($dailyClosing->load('user')));
    }
}
