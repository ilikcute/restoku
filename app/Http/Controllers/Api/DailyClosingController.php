<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Finance\Closing\StoreDailyClosingRequest;
use App\Http\Resources\Api\Finance\DailyClosingResource;
use App\Interfaces\DailyClosingRepositoryInterface;
use App\Models\DailyClosing;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DailyClosingController extends BaseApiController
{
    protected DailyClosingRepositoryInterface $dailyClosingRepository;

    public function __construct(DailyClosingRepositoryInterface $dailyClosingRepository)
    {
        $this->dailyClosingRepository = $dailyClosingRepository;
    }

    public function downloadReport($id)
    {
        $closing = $this->dailyClosingRepository->getClosingById($id);
        $this->authorizeTenant($closing);

        $pdf = Pdf::loadView('reports.daily_closing_pdf', [
            'closing' => $closing,
            'tenant' => $closing->tenant,
        ]);

        return $pdf->download('Laporan_Closing_Harian_'.$closing->closing_date.'.pdf');
    }

    public function index(Request $request)
    {
        $closings = $this->dailyClosingRepository->getAllByTenant($request->user()->tenant_id, 20);

        return $this->successResponse(DailyClosingResource::collection($closings)->response()->getData(true));
    }

    public function store(StoreDailyClosingRequest $request)
    {
        try {
            $closing = $this->dailyClosingRepository->processClosing(
                $request->user()->tenant_id,
                $request->user()->id,
                $request->validated()
            );

            return $this->successResponse(new DailyClosingResource($closing), 'Closing harian berhasil diselesaikan.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function show(DailyClosing $dailyClosing)
    {
        $this->authorizeTenant($dailyClosing);

        return $this->successResponse(new DailyClosingResource($dailyClosing->load('user')));
    }
}
