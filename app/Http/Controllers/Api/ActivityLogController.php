<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends BaseApiController
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $activities = Activity::where('tenant_id', $tenantId)
            ->with('causer')
            ->latest()
            ->paginate($request->query('per_page', 20));

        return $this->successResponse($activities);
    }

    public function show(Activity $activity)
    {
        if ($activity->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        $activity->load(['causer', 'subject']);

        return $this->successResponse($activity);
    }
}
