<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\Department;
use App\Models\DisagregationItem;
use App\Models\IndicatorReport;
use App\Models\IndicatorReportProof;
use App\Models\ReportingPeriod;
use App\Services\IndicatorReportService;
use App\Support\ResultChainIndicators;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class IndicatorReportingController extends Controller
{
    public function __construct(private readonly IndicatorReportService $reports) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $viewAll = $user->is_admin || $user->hasPermission('view-all-indicator-reports');

        $query = IndicatorReport::with(['period:id,name', 'department:id,name', 'currentStage:id,name'])
            ->latest();

        if (! $viewAll) {
            $query->where('created_by', $user->id);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return Inertia::render('IndicatorReporting/Reports/Index', [
            'reports' => $query->paginate(15)->withQueryString(),
            'filters' => ['status' => $request->get('status')],
            'can' => [
                'report' => $user->is_admin || $user->hasPermission('report-indicator-data'),
                'viewAll' => $viewAll,
            ],
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->is_admin || $request->user()->hasPermission('report-indicator-data'), 403);

        return Inertia::render('IndicatorReporting/Reports/Form', $this->formProps(null));
    }

    public function edit(Request $request, IndicatorReport $report)
    {
        abort_unless($this->canEdit($request, $report), 403);

        return Inertia::render('IndicatorReporting/Reports/Form',
            $this->formProps($report->load('values')));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->is_admin || $request->user()->hasPermission('report-indicator-data'), 403);
        $data = $this->validateReport($request);

        try {
            $indicator = ($data['indicator_type'])::findOrFail($data['indicator_id']);
            if (! $this->reports->canReport($request->user(), $indicator)) {
                return back()->with('error', 'You may not report on this indicator.');
            }
            $report = $this->reports->create($request->user(), $data);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('indicator-reporting.reports.show', $report->uuid)
            ->with('success', 'Draft saved.');
    }

    public function show(Request $request, IndicatorReport $report)
    {
        $user = $request->user();
        $allowed = $user->is_admin
            || $user->hasPermission('view-all-indicator-reports')
            || $report->created_by === $user->id
            || $user->hasAnyPermission(['review-indicator-reports', 'approve-indicator-reports']);
        abort_unless($allowed, 403);

        $report->load(['values.disagregationItem.category', 'proofs', 'approvals.actor:id,full_name',
            'approvals.stage:id,name', 'currentStage:id,name', 'period:id,name', 'department:id,name']);

        return Inertia::render('IndicatorReporting/Reports/Show', [
            'report' => $report,
            'can' => [
                'edit' => $this->canEdit($request, $report),
                'submit' => $this->canEdit($request, $report),
            ],
        ]);
    }

    public function update(Request $request, IndicatorReport $report)
    {
        abort_unless($this->canEdit($request, $report), 403);
        $data = $request->validate([
            'target_value' => 'nullable|numeric',
            'actual_value' => 'nullable|numeric',
            'narrative' => 'nullable|string',
            'reporting_period_id' => 'sometimes|integer|exists:reporting_periods,id',
            'values' => 'array',
            'values.*.disagregation_item_id' => 'nullable|integer|exists:disagregation_items,id',
            'values.*.value' => 'nullable|numeric',
        ]);
        $this->reports->update($report, $data);

        return back()->with('success', 'Report updated.');
    }

    public function submit(Request $request, IndicatorReport $report)
    {
        abort_unless($this->canEdit($request, $report), 403);
        try {
            $this->reports->submit($request->user(), $report);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Report submitted for review.');
    }

    public function uploadProof(Request $request, IndicatorReport $report)
    {
        abort_unless($this->canEdit($request, $report), 403);
        $request->validate(['file' => 'required|file|max:10240']);
        $this->reports->addProof($request->user(), $report, $request->file('file'));

        return back()->with('success', 'Proof uploaded.');
    }

    public function deleteProof(Request $request, IndicatorReport $report, IndicatorReportProof $proof)
    {
        abort_unless($this->canEdit($request, $report), 403);
        abort_unless($proof->report_id === $report->id, 404);
        $proof->delete();

        return back()->with('success', 'Proof removed.');
    }

    private function validateReport(Request $request): array
    {
        return $request->validate([
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
    }

    private function formProps(?IndicatorReport $report): array
    {
        return [
            'report' => $report,
            'indicators' => collect(ResultChainIndicators::options())->map(fn ($i) => [
                'type' => $i['type'], 'type_label' => $i['type_label'], 'id' => $i['id'],
                'code' => $i['code'], 'title' => $i['title'],
                'department_id' => optional(($i['type'])::find($i['id']))->department_id,
            ])->values(),
            'periods' => ReportingPeriod::open()->orderByDesc('year')->orderByDesc('period_number')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'disaggregationItems' => DisagregationItem::with('category:id,name')->get(['id', 'name', 'disagregation_category_id']),
        ];
    }

    private function canEdit(Request $request, IndicatorReport $report): bool
    {
        $user = $request->user();

        return ($user->is_admin || $report->created_by === $user->id)
            && in_array($report->status, [ReportStatus::Draft, ReportStatus::Returned], true);
    }
}
