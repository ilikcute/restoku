<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Transactions\Shift\CloseShiftRequest;
use App\Http\Requests\Api\Transactions\Shift\OpenShiftRequest;
use App\Http\Resources\Api\Transactions\ShiftResource;
use App\Interfaces\ShiftRepositoryInterface;
use App\Models\Shift;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ShiftController extends BaseApiController
{
    protected ShiftRepositoryInterface $shiftRepository;

    public function __construct(ShiftRepositoryInterface $shiftRepository)
    {
        $this->shiftRepository = $shiftRepository;
    }

    public function index(Request $request)
    {
        $shifts = $this->shiftRepository->getAllByTenant($request->user()->tenant_id, 20);

        return $this->successResponse(ShiftResource::collection($shifts)->response()->getData(true));
    }

    public function show(Shift $shift)
    {
        $this->authorizeTenant($shift);

        return $this->successResponse(new ShiftResource($shift->load('user')));
    }

    public function current(Request $request)
    {
        $shift = $this->shiftRepository->getCurrentShift($request->user()->tenant_id, $request->user()->id);

        if ($shift) {
            return $this->successResponse(new ShiftResource($shift));
        }

        return $this->successResponse(null);
    }

    public function open(OpenShiftRequest $request)
    {
        try {
            $shift = $this->shiftRepository->openShift(
                $request->user()->tenant_id,
                $request->user()->id,
                $request->validated()
            );

            return $this->successResponse(new ShiftResource($shift), 'Shift berhasil dibuka.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function close(CloseShiftRequest $request)
    {
        try {
            $shift = $this->shiftRepository->closeShift(
                $request->user()->tenant_id,
                $request->user()->id,
                $request->validated()
            );

            return $this->successResponse(new ShiftResource($shift), 'Shift berhasil ditutup.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function downloadReport($id)
    {
        $shift = $this->shiftRepository->getById($id);

        $this->authorizeTenant($shift);

        $shift->load(['user', 'tenant']);

        $pdf = Pdf::loadView('reports.shift_report_pdf', [
            'shift' => $shift,
            'tenant' => $shift->tenant,
        ]);

        return $pdf->download('Laporan_Shift_'.$shift->id.'_'.$shift->start_time->format('Ymd').'.pdf');
    }
}
