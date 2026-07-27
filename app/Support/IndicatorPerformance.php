<?php

namespace App\Support;

use App\Enums\ReportStatus;
use App\Models\IndicatorReport;
use Illuminate\Database\Eloquent\Model;

/**
 * Shapes indicator reporting data into the dashboard API contract.
 *
 * Shared by the dashboard endpoints (bond-deliverables, sector-outcomes, etc.)
 * so status derivation and the evidence/disaggregation blocks stay consistent.
 *
 * @see docs/superpowers/specs/2026-07-16-mems-dashboard-api-contract.md
 */
class IndicatorPerformance
{
    /**
     * Derive the target_status enum from actual vs target.
     * Contract §0: API returns machine values; the frontend owns display labels.
     */
    public static function status(?float $actual, ?float $target): string
    {
        if ($target === null || $target == 0.0 || $actual === null) {
            return 'unknown';
        }

        $ratio = $actual / $target;

        return match (true) {
            $ratio >= 1.05 => 'above',
            $ratio >= 0.90 => 'on',
            default => 'below',
        };
    }

    /**
     * Shape a single indicator into the dashboard contract's indicator block,
     * using its latest report for target/actual/status/evidence/disaggregation.
     *
     * @param  class-string  $indicatorType  the model FQCN (matches indicator_reports.indicator_type)
     * @return array<string, mixed>
     */
    public static function present(Model $indicator, string $indicatorType): array
    {
        $report = self::latestReport($indicatorType, $indicator->id);

        $target = $report?->target_value !== null ? (float) $report->target_value : null;
        $actual = $report?->actual_value !== null ? (float) $report->actual_value : null;

        return [
            'name' => $indicator->title,
            'code' => $indicator->code,
            'target' => $target,
            'actual' => $actual,
            'status' => self::status($actual, $target),
            'baseline' => $report?->baseline !== null ? (float) $report->baseline : null,
            'baselineYear' => $report?->baseline_year !== null ? (int) $report->baseline_year : null,
            'measurementUnit' => $indicator->getAttribute('measurement_unit'),
            'department' => $report?->department?->name,
            'supportingDepartment' => null,
            'lastUpdate' => $report?->published_at?->toISOString(),
            'narrative' => $report?->narrative,
            'evidence' => self::evidence($report),
            'disaggregation' => (object) self::disaggregation($report),
        ];
    }

    /**
     * The latest approved report for an indicator.
     *
     * Only `approved` reports count as ministry data — a draft, a report still
     * moving through the workflow, and one returned for correction must never
     * reach a dashboard. This is the single gate for every consumer of
     * present(), so it has to stay here rather than in each controller.
     *
     * Ordered by the period's own chronology (year, then period number) rather
     * than `reporting_period_id`: periods are not necessarily created in
     * calendar order, so the id says nothing about which period is later.
     */
    public static function latestReport(string $indicatorType, int $indicatorId): ?IndicatorReport
    {
        return IndicatorReport::with(['proofs', 'department', 'period'])
            ->where('indicator_type', $indicatorType)
            ->where('indicator_id', $indicatorId)
            ->where('indicator_reports.status', ReportStatus::Approved->value)
            ->join('reporting_periods as rp', 'rp.id', '=', 'indicator_reports.reporting_period_id')
            ->orderByDesc('rp.year')
            ->orderByDesc('rp.period_number')
            ->select('indicator_reports.*')
            ->first();
    }

    /**
     * Normalised evidence block: { status, label, url } (never a bare string).
     *
     * `url` points at the authorizing download endpoint, never at the file's
     * location on the private disk — that path is internal and must not leave
     * the server.
     *
     * @return array{status: string, label: string|null, url: string|null}
     */
    public static function evidence(?IndicatorReport $report): array
    {
        $proof = $report?->proofs->first();

        if (! $proof) {
            return ['status' => 'not_submitted', 'label' => null, 'url' => null];
        }

        return [
            'status' => 'submitted',
            'label' => $proof->original_name,
            'url' => route('indicator-reports.proofs.download', [
                'report' => $report->uuid,
                'proof' => $proof->id,
            ]),
        ];
    }

    /**
     * Disaggregation breakdown for a report, grouped by category.
     * Returns an object keyed by category slug: { <slug>: { label, note, data: [{label, value}] } }.
     *
     * @return array<string, array{label: string, note: string|null, data: array<int, array{label: string, value: float}>}>
     */
    public static function disaggregation(?IndicatorReport $report): array
    {
        if (! $report) {
            return [];
        }

        $rows = $report->values()
            ->join('disagregation_items as di', 'di.id', '=', 'indicator_report_values.disagregation_item_id')
            ->join('disagregation_categories as dc', 'dc.id', '=', 'di.disagregation_category_id')
            ->get([
                'dc.name as category',
                'di.name as item',
                'indicator_report_values.value as value',
            ]);

        $grouped = [];
        foreach ($rows as $row) {
            $slug = str($row->category)->slug('_')->toString();
            if (! isset($grouped[$slug])) {
                $grouped[$slug] = ['label' => $row->category, 'note' => null, 'data' => []];
            }
            $grouped[$slug]['data'][] = ['label' => $row->item, 'value' => (float) $row->value];
        }

        return $grouped;
    }
}
