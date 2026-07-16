<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\IndicatorReport;
use App\Models\IndicatorReportProof;
use App\Services\IndicatorReportService;
use App\Support\ResultChainIndicators;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IndicatorReportController extends Controller
{
    public function __construct(private readonly IndicatorReportService $reports) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = IndicatorReport::with(['period', 'department', 'currentStage'])->latest();

        if (! $user->is_admin && ! $user->hasPermission('view-all-indicator-reports')) {
            $query->where('created_by', $user->id);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return response()->json(['success' => true, 'data' => $query->paginate($request->get('per_page', 15))]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->is_admin || $user->hasPermission('report-indicator-data'), 403, 'Forbidden.');

        $data = $request->validate([
            'indicator_type' => ['required', Rule::in(array_keys(ResultChainIndicators::TYPES))],
            'indicator_id' => 'required|integer',
            'department_id' => 'required|integer|exists:departments,id',
            'reporting_period_id' => 'required|integer|exists:reporting_periods,id',
            'target_value' => 'nullable|numeric',
            'actual_value' => 'nullable|numeric',
            'narrative' => 'nullable|string',
            'values' => 'array',
            'values.*.disagregation_item_id' => 'nullable|integer|exists:disagregation_items,id',
            'values.*.value' => 'nullable|numeric',
        ]);

        $indicator = ($data['indicator_type'])::findOrFail($data['indicator_id']);
        abort_unless($this->reports->canReport($user, $indicator), 403, 'You may not report on this indicator.');

        $report = $this->reports->create($user, $data);

        return response()->json(['success' => true, 'message' => 'Draft created.', 'data' => $report], 201);
    }

    public function show(Request $request, IndicatorReport $report)
    {
        $this->authorizeView($request, $report);

        return response()->json(['success' => true, 'data' => $report->load(['values', 'proofs', 'approvals.actor', 'approvals.stage', 'currentStage', 'period', 'department'])]);
    }

    public function update(Request $request, IndicatorReport $report)
    {
        abort_unless($this->canEdit($request, $report), 403, 'Report is not editable.');

        $data = $request->validate([
            'target_value' => 'nullable|numeric',
            'actual_value' => 'nullable|numeric',
            'narrative' => 'nullable|string',
            'reporting_period_id' => 'sometimes|integer|exists:reporting_periods,id',
            'values' => 'array',
            'values.*.disagregation_item_id' => 'nullable|integer|exists:disagregation_items,id',
            'values.*.value' => 'nullable|numeric',
        ]);

        return response()->json(['success' => true, 'message' => 'Report updated.', 'data' => $this->reports->update($report, $data)]);
    }

    public function submit(Request $request, IndicatorReport $report)
    {
        abort_unless($this->canEdit($request, $report), 403, 'Report cannot be submitted.');

        return response()->json(['success' => true, 'message' => 'Report submitted.', 'data' => $this->reports->submit($request->user(), $report)]);
    }

    public function uploadProof(Request $request, IndicatorReport $report)
    {
        abort_unless($this->canEdit($request, $report), 403, 'Cannot attach proof.');
        $request->validate(['file' => 'required|file|max:10240']);

        $proof = $this->reports->addProof($request->user(), $report, $request->file('file'));

        return response()->json(['success' => true, 'message' => 'Proof uploaded.', 'data' => $proof], 201);
    }

    public function deleteProof(Request $request, IndicatorReport $report, IndicatorReportProof $proof)
    {
        abort_unless($this->canEdit($request, $report), 403, 'Cannot remove proof.');
        abort_unless($proof->report_id === $report->id, 404);
        $proof->delete();

        return response()->json(['success' => true, 'message' => 'Proof removed.']);
    }

    private function authorizeView(Request $request, IndicatorReport $report): void
    {
        $user = $request->user();
        $allowed = $user->is_admin
            || $user->hasPermission('view-all-indicator-reports')
            || $report->created_by === $user->id
            || $user->hasAnyPermission(['review-indicator-reports', 'approve-indicator-reports']);
        abort_unless($allowed, 403, 'Forbidden.');
    }

    private function canEdit(Request $request, IndicatorReport $report): bool
    {
        $user = $request->user();

        return ($user->is_admin || $report->created_by === $user->id)
            && in_array($report->status, [ReportStatus::Draft, ReportStatus::Returned], true);
    }
}
