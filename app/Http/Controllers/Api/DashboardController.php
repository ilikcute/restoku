<?php

namespace App\Http\Controllers\Api;

use App\Interfaces\DashboardRepositoryInterface;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    protected DashboardRepositoryInterface $dashboardRepository;

    public function __construct(DashboardRepositoryInterface $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function stats(Request $request)
    {
        $stats = $this->dashboardRepository->getStats($request->user()->tenant_id);

        return $this->successResponse($stats);
    }
}
