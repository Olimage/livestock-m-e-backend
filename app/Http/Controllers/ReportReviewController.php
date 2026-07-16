<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\IndicatorReport;
use App\Services\ReportApprovalService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportReviewController extends Controller
{
    public function __construct(private readonly ReportApprovalService $approvals) {}

    public function queue(Request $request)
    {
        $user = $request->user();
        abort_unless($user->is_admin || $user->hasAnyPermission(['review-indicator-reports', 'approve-indicator-reports']), 403);

        // currentStage is loaded in full (not column-constrained) because eligibility
        // resolution needs role_id/assignment_type, not just the display name.
        $pending = IndicatorReport::with(['currentStage', 'period:id,name', 'department:id,name'])
            ->where('status', ReportStatus::Pending->value)
            ->latest('submitted_at')
            ->get()
            ->filter(fn (IndicatorReport $r) => $this->approvals->isEligibleApprover($user, $r))
            ->values();

        return Inertia::render('IndicatorReporting/Review/Queue', [
            'reports' => $pending,
        ]);
    }

    public function approve(Request $request, IndicatorReport $report)
    {
        $user = $request->user();
        abort_unless($user->is_admin || $user->hasAnyPermission(['review-indicator-reports', 'approve-indicator-reports']), 403);

        try {
            $this->approvals->approve($user, $report);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Report approved.');
    }

    public function decline(Request $request, IndicatorReport $report)
    {
        $user = $request->user();
        abort_unless($user->is_admin || $user->hasAnyPermission(['review-indicator-reports', 'approve-indicator-reports']), 403);
        $data = $request->validate(['reason' => 'required|string|min:3']);

        try {
            $this->approvals->decline($user, $report, $data['reason']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Report returned to reporter.');
    }
}
