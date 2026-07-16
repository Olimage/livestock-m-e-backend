<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndicatorReport;
use App\Services\ReportApprovalService;
use Illuminate\Http\Request;

class ReportApprovalController extends Controller
{
    public function __construct(private readonly ReportApprovalService $approvals) {}

    public function approve(Request $request, IndicatorReport $report)
    {
        $user = $request->user();
        abort_unless(
            $user->is_admin || $user->hasAnyPermission(['review-indicator-reports', 'approve-indicator-reports']),
            403,
            'Forbidden.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Report approved.',
            'data' => $this->approvals->approve($user, $report),
        ]);
    }

    public function decline(Request $request, IndicatorReport $report)
    {
        $user = $request->user();
        abort_unless(
            $user->is_admin || $user->hasAnyPermission(['review-indicator-reports', 'approve-indicator-reports']),
            403,
            'Forbidden.'
        );
        $data = $request->validate(['reason' => 'required|string|min:3']);

        return response()->json([
            'success' => true,
            'message' => 'Report returned to reporter.',
            'data' => $this->approvals->decline($user, $report, $data['reason']),
        ]);
    }
}
