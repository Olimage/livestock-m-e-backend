<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\IndicatorReport;
use App\Models\IndicatorReportProof;

/**
 * System data-health dashboard.
 *   GET /api/v1/data-health/metrics       — headline metric cards (from real counts)
 *   GET /api/v1/data-health/validators    — validator statuses (NOT modelled — empty)
 *   GET /api/v1/data-health/activity-log  — recent activity (real)
 *
 * @see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md §7
 */
class DataHealthApiController extends Controller
{
    public function metrics()
    {
        try {
            $totalReports = IndicatorReport::count();
            $withEvidence = IndicatorReportProof::distinct('report_id')->count('report_id');
            $activeDepts = IndicatorReport::distinct('department_id')->count('department_id');
            $evidencePct = $totalReports > 0 ? round(($withEvidence / $totalReports) * 100) : 0;

            $data = [
                ['id' => 1, 'title' => 'Total Indicator Reports', 'value' => (string) $totalReports, 'status' => 'Good', 'icon' => 'records', 'description' => 'Reports submitted across all indicators'],
                ['id' => 2, 'title' => 'Reports with Evidence', 'value' => $evidencePct.'%', 'status' => $evidencePct >= 50 ? 'Good' : 'Warning', 'icon' => 'streams', 'description' => 'Share of reports with uploaded proof'],
                ['id' => 3, 'title' => 'Active Departments', 'value' => (string) $activeDepts, 'status' => 'Good', 'icon' => 'farms', 'description' => 'Departments that have reported data'],
                ['id' => 4, 'title' => 'Total Departments', 'value' => (string) Department::count(), 'status' => 'Good', 'icon' => 'uptime', 'description' => 'Registered departments in the system'],
            ];

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to retrieve data-health metrics', $e);
        }
    }

    public function validators()
    {
        // NOTE: the mock's "data validators" (RUNNING/COMPLETED/WARNING etc.) have no
        // backing model in mems. Returned empty until a validation subsystem exists.
        return response()->json(['status' => true, 'message' => 'Success', 'data' => []]);
    }

    public function activityLog()
    {
        try {
            $data = ActivityLog::with('user')
                ->latest()
                ->take(50)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'timestamp' => $log->created_at?->toISOString(),
                    'user' => $log->user?->name ?? 'System',
                    'action' => $log->action,
                    'resource' => $log->callable_type ? class_basename($log->callable_type) : 'Indicator Report',
                    'details' => $log->description,
                ]);

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to retrieve activity log', $e);
        }
    }

    private function fail(string $message, \Throwable $e)
    {
        return response()->json(['status' => false, 'message' => $message, 'error' => $e->getMessage()], 500);
    }
}
