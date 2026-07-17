<?php

namespace App\Support;

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
            'baseline' => null,        // baselines not yet seeded (indicator_baseline_years)
            'baselineYear' => null,
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
     * The latest approved report for an indicator (most recent reporting period).
     */
    public static function latestReport(string $indicatorType, int $indicatorId): ?IndicatorReport
    {
        return IndicatorReport::with(['proofs', 'department', 'period'])
            ->where('indicator_type', $indicatorType)
            ->where('indicator_id', $indicatorId)
            ->orderByDesc('reporting_period_id')
            ->first();
    }

    /**
     * Normalised evidence block: { status, label, url } (never a bare string).
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
            'url' => $proof->path,
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
