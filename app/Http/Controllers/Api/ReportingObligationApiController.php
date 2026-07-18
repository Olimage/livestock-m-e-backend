<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\BondOutputIndicator;
use App\Models\ImpactIndicator;
use App\Models\IndicatorReport;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;

/**
 * GET /api/v1/reporting-obligations — indicator reports shaped for the
 * "Reporting Obligation" table (Due tab). Built from IndicatorReport.
 *
 * NOTE: the mock uses extra workflow states (Validated / Suppressed /
 * Not Submitted / Overdue) that don't map 1:1 to the report status enum
 * (draft/pending/returned/approved). The mapping below is a documented
 * default — confirm the intended semantics before wiring the other tabs.
 *
 * @see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md §7
 */
class ReportingObligationApiController extends Controller
{
    private const TYPE_LABEL = [
        OutputIndicator::class => 'Output Indicator (OPT)',
        OutcomeIndicator::class => 'Outcome Indicator (OCI)',
        ImpactIndicator::class => 'Impact Indicator (IMP)',
        BondOutputIndicator::class => 'Bond Output Indicator (BOI)',
    ];

    public function index()
    {
        try {
            $reports = IndicatorReport::with(['indicator', 'department', 'period'])
                ->orderByDesc('reporting_period_id')
                ->orderBy('id')
                ->get();

            $today = now()->startOfDay();

            $data = $reports->map(function (IndicatorReport $r) use ($today) {
                $submitted = $r->submitted_at !== null;
                $periodEnded = ($r->period?->end_date && $r->period->end_date < $today)
                    || ($r->period && ! $r->period->is_open);

                return [
                    'id' => $r->id,
                    'indicator' => $r->indicator?->title ?? $r->indicator_code ?? "Indicator {$r->indicator_id}",
                    'reportingUnit' => $r->department?->name ?? '—',
                    'type' => self::TYPE_LABEL[$r->indicator_type] ?? 'Indicator',
                    'period' => $r->period?->name ?? '—',
                    'reportingCompletion' => $this->completion($r->status),
                    'status' => $this->statusLabel($r->status),
                    // Category flags (see controller docblock for definitions).
                    'submitted' => $submitted,
                    'overdue' => $periodEnded && ! $submitted && $r->status !== ReportStatus::Approved,
                ];
            });

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve reporting obligations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/suppressed-indicators — returned/rejected reports, shaped for
     * the Suppressed tab (indicators withheld from the published dataset).
     */
    public function suppressed()
    {
        try {
            $reports = IndicatorReport::with(['indicator', 'department', 'period'])
                ->where('status', ReportStatus::Returned)
                ->orderByDesc('updated_at')
                ->get();

            $data = $reports->map(fn (IndicatorReport $r) => [
                'id' => $r->id,
                'indicator' => $r->indicator?->title ?? $r->indicator_code ?? "Indicator {$r->indicator_id}",
                'owner' => $r->department?->name ?? '—',
                'period' => $r->period?->name ?? '—',
                'reason' => $r->narrative ?: 'Returned for revision during validation.',
                'suppressedDate' => $r->updated_at?->toDateString(),
                'lastAction' => 'Rejected',
            ]);

            return response()->json(['status' => true, 'message' => 'Success', 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve suppressed indicators',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function statusLabel(ReportStatus $status): string
    {
        return match ($status) {
            ReportStatus::Draft => 'Draft',
            ReportStatus::Pending => 'Pending Validation',
            ReportStatus::Returned => 'Rejected',
            ReportStatus::Approved => 'Approved',
        };
    }

    private function completion(ReportStatus $status): int
    {
        return match ($status) {
            ReportStatus::Approved => 100,
            ReportStatus::Pending => 75,
            ReportStatus::Returned => 50,
            ReportStatus::Draft => 40,
        };
    }
}
