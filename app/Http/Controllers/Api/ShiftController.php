<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Transactions\Shift\CloseShiftRequest;
use App\Http\Requests\Api\Transactions\Shift\OpenShiftRequest;
use App\Http\Resources\Api\Transactions\ShiftResource;
use App\Models\Shift;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiftController extends BaseApiController
{
    public function index(Request $request)
    {
        $shifts = Shift::where('tenant_id', $request->user()->tenant_id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return $this->successResponse(ShiftResource::collection($shifts)->response()->getData(true));
    }

    public function show(Shift $shift)
    {
        $this->authorizeTenant($shift);

        return $this->successResponse(new ShiftResource($shift->load('user')));
    }

    public function current(Request $request)
    {
        $shift = Shift::where('tenant_id', $request->user()->tenant_id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if ($shift) {
            // Shift dianggap expired jika sudah buka lebih dari 24 jam
            $isExpired = $shift->start_time->diffInHours(now()) > 24;
            $shift->is_expired = $isExpired;

            return $this->successResponse(new ShiftResource($shift));
        }

        return $this->successResponse(null);
    }

    public function open(OpenShiftRequest $request)
    {
        $tenantId = $request->user()->tenant_id;
        $userId = $request->user()->id;

        // Check if there is already an open shift
        $existingShift = Shift::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return $this->errorResponse('Anda masih memiliki Shift yang terbuka.', 422);
        }

        $shift = Shift::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'start_time' => now(),
            'starting_cash' => $request->validated()['starting_cash'],
            'status' => 'open',
        ]);

        return $this->successResponse(new ShiftResource($shift), 'Shift berhasil dibuka.', 201);
    }

    public function close(CloseShiftRequest $request)
    {
        $shift = Shift::where('tenant_id', $request->user()->tenant_id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->firstOrFail();

        $validated = $request->validated();

        // 1. Calculate Sales
        $cashSales = $shift->orders()->where('payment_method', 'cash')->sum('total_amount');
        $nonCashSales = $shift->orders()->where('payment_method', '!=', 'cash')->sum('total_amount');
        $totalSales = $cashSales + $nonCashSales;

        // 2. Calculate Manual Financial Transactions (Income/Expense)
        // Note: We exclude transactions linked to Orders because they are already accounted for in sales totals.
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
        // Expected Cash = Starting + Cash Sales - Cash Returns + Manual Income - Manual Expense
        // We only account for cash-based transactions for the physical drawer.
        $totalExpected = $shift->starting_cash + $cashSales - $cashReturns + $totalIncome - $totalExpense;
        $difference = $validated['ending_cash'] - $totalExpected;

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
            'ending_cash' => $validated['ending_cash'],
            'total_expected' => $totalExpected,
            'difference' => $difference,
            'status' => 'closed',
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->successResponse(new ShiftResource($shift), 'Shift berhasil ditutup.');
    }

    public function downloadReport($id)
    {
        Log::info('Downloading report for shift ID: '.$id);
        $shift = Shift::findOrFail($id);

        $this->authorizeTenant($shift);

        $shift->load(['user', 'tenant']);
        Log::info('Shift data loaded.');

        $pdf = Pdf::loadView('reports.shift_report_pdf', [
            'shift' => $shift,
            'tenant' => $shift->tenant,
        ]);
        Log::info('PDF loaded.');

        return $pdf->download('Laporan_Shift_'.$shift->id.'_'.$shift->start_time->format('Ymd').'.pdf');
    }
}
