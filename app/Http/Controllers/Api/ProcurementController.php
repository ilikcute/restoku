<?php

namespace App\Http\Controllers\Api;

use App\Interfaces\ProcurementRepositoryInterface;
use Illuminate\Http\Request;

class ProcurementController extends BaseApiController
{
    protected ProcurementRepositoryInterface $procurementRepository;

    public function __construct(ProcurementRepositoryInterface $procurementRepository)
    {
        $this->procurementRepository = $procurementRepository;
    }

    /**
     * Get procurement recommendations based on ROP formula.
     */
    public function recommendations(Request $request)
    {
        $recommendations = $this->procurementRepository->getRecommendations(
            $request->user()->tenant_id,
            max(1, $request->get('days', 30))
        );

        return $this->successResponse($recommendations);
    }

    /**
     * Get inventory alerts (Overstock).
     */
    public function alerts(Request $request)
    {
        $overstockProducts = $this->procurementRepository->getAlerts(
            $request->user()->tenant_id
        );

        return $this->successResponse($overstockProducts);
    }
}
