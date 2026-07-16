<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndicatorReport;
use App\Services\ApprovalTrailService;
use Illuminate\Http\Request;

class ApprovalTrailController extends Controller
{
    public function __construct(private readonly ApprovalTrailService $trail) {}

    public function forReport(Request $request, IndicatorReport $report)
    {
        $user = $request->user();
        $allowed = $user->is_admin
            || $user->hasPermission('view-all-indicator-reports')
            || $report->created_by === $user->id
            || $user->hasAnyPermission(['review-indicator-reports', 'approve-indicator-reports']);
        abort_unless($allowed, 403, 'Forbidden.');

        return response()->json(['success' => true, 'data' => $this->trail->forReport($report)]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->is_admin || $user->hasPermission('view-all-indicator-reports'), 403, 'Forbidden.');

        return response()->json([
            'success' => true,
            'data' => $this->trail->all($request->only(['report_id', 'action', 'per_page'])),
        ]);
    }
}
